#!/usr/bin/env bash
set -euo pipefail

command -v jq >/dev/null || {
  echo "jq is required but not installed" >&2
  exit 1
}

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
POST_SCRIPT="${ROOT_DIR}/bin/post-mr-statistic-comment.sh"
FIXTURES_DIR="${ROOT_DIR}/tests/fixtures/gitlab"
MOCK_DIR="$(mktemp -d)"
MOCK_LOG="${MOCK_DIR}/gitlab-control.log"
COMMENT_PATH="${MOCK_DIR}/mr-comment.md"

trap 'rm -rf "${MOCK_DIR}"' EXIT

cat > "${MOCK_DIR}/gitlab-control" <<'EOF'
#!/usr/bin/env bash
printf '%s\n' "$*" >> "${GITLAB_CONTROL_MOCK_LOG:?}"
exit 0
EOF
chmod +x "${MOCK_DIR}/gitlab-control"

export GITLAB_CONTROL="${MOCK_DIR}/gitlab-control"
export GITLAB_CONTROL_MOCK_LOG="$MOCK_LOG"
chmod +x "${POST_SCRIPT}"

run_case() {
  local report_path="$1"
  local expected_comment_path="$2"
  local case_label="$3"

  rm -f "$MOCK_LOG" "$COMMENT_PATH"
  "${POST_SCRIPT}" "$report_path" --comment-path "$COMMENT_PATH"

  diff -u "$expected_comment_path" "$COMMENT_PATH"

  grep -Fq 'upsert_mr_note TODO-REGISTRAR-STATISTIC:START '"$COMMENT_PATH" "$MOCK_LOG" || {
    echo "mock gitlab-control did not receive expected upsert_mr_note invocation for ${case_label}" >&2
    cat "$MOCK_LOG" >&2
    exit 1
  }
}

run_case \
  "${FIXTURES_DIR}/report.json" \
  "${FIXTURES_DIR}/mr-comment.expected.md" \
  "report with unregistered TODOs"

run_case \
  "${FIXTURES_DIR}/report-empty.json" \
  "${FIXTURES_DIR}/mr-comment-empty.expected.md" \
  "empty report"
