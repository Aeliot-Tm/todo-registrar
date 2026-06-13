#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
STATISTIC_MARKER_SEARCH="TODO-REGISTRAR-STATISTIC:START"
STATISTIC_MARKER_START="<!-- TODO-REGISTRAR-STATISTIC:START -->"
STATISTIC_MARKER_END="<!-- TODO-REGISTRAR-STATISTIC:END -->"
GITLAB_CONTROL_URL="${GITLAB_CONTROL_URL:-https://gitlab.com/aeliot-tm/gitlab-control/-/raw/0.2.0/controller.sh}"

resolve_gitlab_control() {
  if [[ -n "${GITLAB_CONTROL:-}" && -x "$GITLAB_CONTROL" ]]; then
    printf '%s' "$GITLAB_CONTROL"
    return 0
  fi
  if [[ -x ./gitlab-control ]]; then
    printf '%s' "./gitlab-control"
    return 0
  fi
  if [[ -x "${SCRIPT_DIR}/gitlab-control" ]]; then
    printf '%s' "${SCRIPT_DIR}/gitlab-control"
    return 0
  fi
  if command -v gitlab-control >/dev/null 2>&1; then
    command -v gitlab-control
    return 0
  fi

  local bootstrap_dir="${RUNNER_TEMP:-/tmp}"
  local bootstrap_path="${bootstrap_dir}/gitlab-control-$$"
  mkdir -p "$bootstrap_dir"
  curl -sSL "$GITLAB_CONTROL_URL" -o "$bootstrap_path"
  chmod +x "$bootstrap_path"
  printf '%s' "$bootstrap_path"
}

resolve_mr_iid() {
  if [[ -n "$MR_IID" ]]; then
    printf '%s' "$MR_IID"
    return 0
  fi
  if [[ -n "${CI_MERGE_REQUEST_IID:-}" ]]; then
    printf '%s' "$CI_MERGE_REQUEST_IID"
    return 0
  fi
  if [[ -z "${CI_OPEN_MERGE_REQUESTS:-}" || -z "${CI_PROJECT_PATH:-}" ]]; then
    return 1
  fi

  local entry project iid
  for entry in $(echo "$CI_OPEN_MERGE_REQUESTS" | tr ',' ' '); do
    project="${entry%%!*}"
    iid="${entry##*!}"
    if [[ "$project" == "$CI_PROJECT_PATH" ]]; then
      printf '%s' "$iid"
      return 0
    fi
  done

  return 1
}

REPORT_PATH=""
COMMENT_PATH=""
MR_IID=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --comment-path)
      COMMENT_PATH="${2:?--comment-path requires value}"
      shift 2
      ;;
    --mr-iid)
      MR_IID="${2:?--mr-iid requires value}"
      shift 2
      ;;
    -*)
      echo "post-mr-statistic-comment: unknown option '$1'" >&2
      exit 1
      ;;
    *)
      if [[ -z "$REPORT_PATH" ]]; then
        REPORT_PATH="$1"
      else
        echo "post-mr-statistic-comment: unexpected argument '$1'" >&2
        exit 1
      fi
      shift
      ;;
  esac
done

if [[ -z "$REPORT_PATH" ]]; then
  echo "post-mr-statistic-comment: report path required" >&2
  exit 1
fi
if ! command -v jq >/dev/null 2>&1; then
  echo "post-mr-statistic-comment: jq is required" >&2
  exit 1
fi
if ! command -v curl >/dev/null 2>&1; then
  echo "post-mr-statistic-comment: curl is required" >&2
  exit 1
fi

BUILD_SCRIPT="${SCRIPT_DIR}/build-mr-comment.sh"
if [[ ! -x "$BUILD_SCRIPT" ]]; then
  echo "post-mr-statistic-comment: build script not found: $BUILD_SCRIPT" >&2
  exit 1
fi

if [[ -z "$COMMENT_PATH" ]]; then
  RUNNER_TEMP="${RUNNER_TEMP:-/tmp}"
  mkdir -p "$RUNNER_TEMP"
  COMMENT_PATH="${RUNNER_TEMP}/todo-registrar-mr-comment.md"
fi

GITLAB_CONTROL="$(resolve_gitlab_control)"

export TODO_REGISTRAR_STATISTIC_MARKER_START="$STATISTIC_MARKER_START"
export TODO_REGISTRAR_STATISTIC_MARKER_END="$STATISTIC_MARKER_END"

"$BUILD_SCRIPT" "$REPORT_PATH" "$COMMENT_PATH"

if ! MR_IID="$(resolve_mr_iid)"; then
  echo "post-mr-statistic-comment: no MR IID found, skipping" >&2
  exit 0
fi

UPSERT_ARGS=(upsert_mr_note "$STATISTIC_MARKER_SEARCH" "$COMMENT_PATH" --mr-iid "$MR_IID")

"$GITLAB_CONTROL" "${UPSERT_ARGS[@]}"
