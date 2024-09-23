#!/bin/bash

if [ $# -ne 2 ]; then
  echo "Usage: $0 <MR_NEW_BRANCH> <MR_TITLE>"
  exit 1
fi

NEW_BRANCH=$1
TITLE=$2

git config --global user.email "todo-registrar@example.com"
git config --global user.name "CI Bot"
git config remote.origin_ci.url >&- || git remote add origin_ci https://oauth2:${MR_PRIVATE_TOKEN}@${CI_SERVER_HOST}/${CI_PROJECT_PATH}.git
git checkout -b $NEW_BRANCH 1> /dev/null

git add .
TR_NOTHING_TO_COMMIT=$(git commit -m "$TITLE" | grep "nothing to commit")
if [[ -z  $TR_NOTHING_TO_COMMIT ]]; then
    git push origin_ci $NEW_BRANCH 1> /dev/null
    echo 1
else
    git checkout -b $CI_COMMIT_REF_SLUG 1> /dev/null
    git branch -D $NEW_BRANCH 1> /dev/null
    echo 0
fi
