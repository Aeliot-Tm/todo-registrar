#!/bin/bash

if [ $# -ne 3 ]; then
  echo "Usage: $0 <MR_TARGET_BRANCH> <MR_SOURCE_BRANCH> <MR_TITLE>"
  exit 1
fi

GITLAB_API_URL="$CI_SERVER_URL/api/v4"
PRIVATE_TOKEN=$MR_PRIVATE_TOKEN
PROJECT_ID=$CI_PROJECT_ID
SOURCE_BRANCH=$2
TARGET_BRANCH=$1
TITLE=$3

curl -sS --request POST --header "PRIVATE-TOKEN: $PRIVATE_TOKEN" \
  "$GITLAB_API_URL/projects/$PROJECT_ID/merge_requests" \
  --form "source_branch=$SOURCE_BRANCH" \
  --form "target_branch=$TARGET_BRANCH" \
  --form "remove_source_branch=true" \
  --form "title=$TITLE" \
  --form "description=Automated merge request from CI/CD pipeline"
