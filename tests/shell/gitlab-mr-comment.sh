#!/usr/bin/env bash
set -euo pipefail

command -v jq >/dev/null || {
  echo "jq is required but not installed" >&2
  exit 1
}

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
BUILD_SCRIPT="${ROOT_DIR}/bin/build-mr-comment.sh"
FIXTURES_DIR="${ROOT_DIR}/tests/fixtures/gitlab"
GENERATED_PATH="$(mktemp)"

trap 'rm -f "${GENERATED_PATH}"' EXIT

chmod +x "${BUILD_SCRIPT}"

"${BUILD_SCRIPT}" "${FIXTURES_DIR}/report.json" "${GENERATED_PATH}"
diff -u "${FIXTURES_DIR}/mr-comment.expected.md" "${GENERATED_PATH}"

"${BUILD_SCRIPT}" "${FIXTURES_DIR}/report-empty.json" "${GENERATED_PATH}"
diff -u "${FIXTURES_DIR}/mr-comment-empty.expected.md" "${GENERATED_PATH}"
