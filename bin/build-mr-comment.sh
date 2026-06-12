#!/usr/bin/env bash
set -euo pipefail

REPORT_PATH="${1:?report path required}"
COMMENT_PATH="${2:?comment output path required}"
LOGO_URL="${3:-https://cdn.jsdelivr.net/gh/Aeliot-Tm/todo-registrar@main/docs/logo-in-comment.svg}"
REGISTRAR_PROJECT_URL="https://github.com/Aeliot-Tm/todo-registrar"
MARKER_START="<!-- TODO-REGISTRAR-STATISTIC:START -->"
MARKER_END="<!-- TODO-REGISTRAR-STATISTIC:END -->"

pluralize() {
  local count="$1"
  local singular="$2"
  local plural="$3"

  if [[ "$count" == "1" ]]; then
    echo "$singular"
  else
    echo "$plural"
  fi
}

write_logo_linked() {
  echo "<a href=\"${REGISTRAR_PROJECT_URL}\"><img src=\"${LOGO_URL}\" alt=\"TODO Registrar\" /></a>"
}

write_empty_header() {
  local analyzed="$1"

  echo '<table>'
  echo '<tr>'
  echo '<td align="center" valign="middle" width="40%">'
  write_logo_linked
  echo '</td>'
  echo '<td valign="middle">'
  echo ""
  echo "> [!TIP]"
  if [[ -n "$analyzed" ]]; then
    echo "> No unregistered TODOs found. Scanned **${analyzed}** $(pluralize "$analyzed" "file" "files")."
  else
    echo "> No unregistered TODOs found."
  fi
  echo ""
  echo '</td>'
  echo '</tr>'
  echo '</table>'
}

write_metrics_header() {
  local registered="$1"
  local new_issues="$2"
  local glued="$3"

  echo '<table>'
  echo '<tr>'
  echo '<td rowspan="2" align="center" valign="middle" width="40%">'
  write_logo_linked
  echo '</td>'
  echo '<th align="center">Unregistered</th>'
  echo '<th align="center">New issues</th>'
  echo '<th align="center">Glued</th>'
  echo '</tr>'
  echo '<tr>'
  echo "<td align=\"center\"><strong>${registered}</strong></td>"
  echo "<td align=\"center\"><strong>${new_issues}</strong></td>"
  echo "<td align=\"center\"><strong>${glued}</strong></td>"
  echo '</tr>'
  echo '</table>'
}

write_metric_legend() {
  echo "<details>"
  echo "<summary><strong>Metric definitions</strong></summary>"
  echo ""
  echo "- **Unregistered** — TODO comments without an issue key that would be registered"
  echo "- **New issues** — new tracker tickets that would be created (\`registered - glued\`)"
  echo "- **Glued** — TODOs that would reuse an existing issue key"
  echo ""
  echo "</details>"
}

{
  echo "$MARKER_START"
  echo ""

  if [[ -f "$REPORT_PATH" ]]; then
    read -r REGISTERED NEW_ISSUES GLUED <<< "$(jq -r '.summary.todos | "\(.registered) \(.newIssues) \(.glued)"' "$REPORT_PATH")"
    ANALYZED="$(jq -r '.summary.files.analyzed // empty' "$REPORT_PATH")"
    UNREGISTERED_FILES="$(jq '[.files[]? | select(.summary.todos.registered > 0)] | length' "$REPORT_PATH")"

    if [[ "$REGISTERED" -eq 0 ]]; then
      write_empty_header "$ANALYZED"
    else
      write_metrics_header "$REGISTERED" "$NEW_ISSUES" "$GLUED"

      if [[ "$UNREGISTERED_FILES" -gt 0 ]]; then
        echo ""
        echo "<details>"
        echo "<summary><strong>Files with unregistered TODOs</strong> (${UNREGISTERED_FILES})</summary>"
        echo ""
        echo "| File | Unregistered TODOs |"
        echo "|------|-------------------:|"
        jq -r '.files | map(select(.summary.todos.registered > 0)) | sort_by(-.summary.todos.registered) | .[] | "| `\(.path)` | \(.summary.todos.registered) |"' "$REPORT_PATH"
        echo ""
        echo "</details>"
      fi

      echo ""
      write_metric_legend
    fi

    echo ""
    echo "$MARKER_END"
  else
    echo "> [!WARNING]"
    echo "> Processing report is not available."
    echo ""
    echo "$MARKER_END"
  fi
} > "$COMMENT_PATH"
