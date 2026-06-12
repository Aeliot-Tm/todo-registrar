#!/usr/bin/env bash
set -euo pipefail

command -v jq >/dev/null || {
  echo "jq is required but not installed" >&2
  exit 1
}

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
BUILD_SCRIPT="${ROOT_DIR}/bin/build-mr-body.sh"
REPORT_PATH="${ROOT_DIR}/tests/fixtures/gitlab/report.json"
EXPECTED_PATH="${ROOT_DIR}/tests/fixtures/gitlab/mr-body.expected.md"
GENERATED_PATH="$(mktemp)"

trap 'rm -f "${GENERATED_PATH}"' EXIT

chmod +x "${BUILD_SCRIPT}"
"${BUILD_SCRIPT}" "${REPORT_PATH}" "${GENERATED_PATH}"
diff -u "${EXPECTED_PATH}" "${GENERATED_PATH}"
