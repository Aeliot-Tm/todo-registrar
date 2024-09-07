#!/bin/bash

if [ $# -ne 2 ]; then
  echo "Usage: $0 <MR_TARGET_BRANCH> <MR_SOURCE_BRANCH>"
  exit 1
fi

PRIVATE_TOKEN=$MR_PRIVATE_TOKEN
PROJECT_ID=$CI_PROJECT_ID
SOURCE_BRANCH=$2
TARGET_BRANCH=$1
GITLAB_API_URL="$CI_SERVER_URL/api/v4"
MR_EXIST=0
REGEX="\"source_branch\":\s*\"$SOURCE_BRANCH\""

# shellcheck disable=SC2034
for page in {1..15}
do
  MR_LIST=$(curl -sS --header "PRIVATE-TOKEN: $PRIVATE_TOKEN" \
    "$GITLAB_API_URL/projects/$PROJECT_ID/merge_requests?target_branch=$TARGET_BRANCH&state=opened&page=$page")

  if [ "$MR_LIST" == "[]" ]; then
    break
  fi
  if [[ "$MR_LIST" =~ $REGEX ]]; then
    MR_EXIST=1
    break
  fi
done

echo $MR_EXIST
