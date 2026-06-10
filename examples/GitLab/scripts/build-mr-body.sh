#!/usr/bin/env bash
set -euo pipefail

REPORT_PATH="${1:?report path required}"
MR_BODY_PATH="${2:?MR body output path required}"
LOGO_URL="${3:-https://cdn.jsdelivr.net/gh/Aeliot-Tm/todo-registrar@main/docs/logo.svg}"
REGISTRAR_LINK="[TODO Registrar](https://github.com/Aeliot-Tm/todo-registrar)"

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

write_alert() {
    local registered="$1"
    local new_issues="$2"
    local glued="$3"
    local analyzed="$4"

    if [[ "$registered" -eq 0 ]]; then
        echo "> [!tip]"
        if [[ -n "$analyzed" ]]; then
            echo "> No new TODOs to register. Scanned **${analyzed}** $(pluralize "$analyzed" "file" "files"), nothing changed."
        else
            echo "> No new TODOs to register."
        fi
    elif [[ "$new_issues" -gt 0 ]]; then
        echo "> [!note]"
        echo "> Automated registration of TODO comments by ${REGISTRAR_LINK}."
    elif [[ "$glued" -gt 0 ]]; then
        echo "> [!important]"
        echo "> All registered TODOs were linked to existing issues. No new tracker tickets were created."
    else
        echo "> [!note]"
        echo "> Automated registration of TODO comments by ${REGISTRAR_LINK}."
    fi
}

write_footer() {
    local server_url="${CI_SERVER_URL:-}"
    local project_path="${CI_PROJECT_PATH:-}"
    local pipeline_id="${CI_PIPELINE_ID:-}"
    local commit_sha="${COMMIT_SHA:-${CI_COMMIT_SHA:-}}"

    [[ -n "$pipeline_id" && -n "$project_path" && -n "$server_url" ]] || return 0

    local pipeline_url="${server_url}/${project_path}/-/pipelines/${pipeline_id}"
    local footer="> Run by [pipeline #${pipeline_id}](${pipeline_url})"

    if [[ -n "$commit_sha" ]]; then
        footer+=" · [view changes](${server_url}/${project_path}/-/commit/${commit_sha})"
    fi

    echo ""
    echo "$footer"
}

{
    echo '<!-- TODO-REGISTRAR:MR-BODY:START -->'
    echo ""
    echo "![TODO Registrar](${LOGO_URL})"
    echo ""
    echo '<!-- TODO-REGISTRAR:MR-BODY:END -->'
    echo ""

    if [[ -f "$REPORT_PATH" ]]; then
        read -r REGISTERED NEW_ISSUES GLUED <<< "$(jq -r '.summary.todos | "\(.registered) \(.newIssues) \(.glued)"' "$REPORT_PATH")"
        ANALYZED="$(jq -r '.summary.files.analyzed // empty' "$REPORT_PATH")"
        UPDATED_FILES="$(jq '[.files[]? | select(.summary.todos.registered > 0)] | length' "$REPORT_PATH")"

        write_alert "$REGISTERED" "$NEW_ISSUES" "$GLUED" "$ANALYZED"
        echo ""
        echo "---"
        echo ""
        echo "## Processing summary"
        echo ""

        echo "| Registered | New issues | Glued |"
        echo "| :--------: | :--------: | :---: |"
        echo "| **${REGISTERED}** | **${NEW_ISSUES}** | **${GLUED}** |"
        echo ""
        echo "- **Registered** — TODO comments that received an issue key"
        echo "- **New issues** — new issues created in the tracker"
        echo "- **Glued** — TODOs that reused an existing issue key"

        if [[ "$UPDATED_FILES" -gt 0 ]]; then
            echo ""
            echo "<details>"
            echo "<summary>Updated files (${UPDATED_FILES})</summary>"
            echo ""
            echo "| File | Registered TODOs |"
            echo "|------|-----------------:|"
            jq -r '.files | map(select(.summary.todos.registered > 0)) | sort_by(-.summary.todos.registered) | .[] | "| `\(.path)` | \(.summary.todos.registered) |"' "$REPORT_PATH"
            echo ""
            echo "</details>"
        fi

        write_footer
    else
        echo "> [!warning]"
        echo "> Processing report is not available."
        write_footer
    fi
} > "$MR_BODY_PATH"
