## Integration on GitLab CI

### Preconditions

Config env-variables:
- `MR_PRIVATE_TOKEN` - token for connection to GitLab API with permission to create merge request.

### Configuration of GitLab CI pipeline

You have to describe pipeline in `.gitlab-ci.yml`. See [example](../../examples/GitLab/.gitlab-ci.yml)
The example based on Docker container which contains all necessary dependencies. So installation of them is skipped in it.

For the duties of example it added into stages `tasts`. Usually it is quite good place.

**Algorithm of script:**
1. Create name for new branch which will hold commited IDs on new issues.
   The branch name fits pattern "todo-registrar-<suffix-with-8-random-symbols>".
2. Check if previous MR is opened yet. Stop working when "yes".
3. Run detection of not managed TODOs and creation of issues.
4. Create new branch.
5. Try to commit injected IDs of created issues.
6. Push when something is commited.
7. Create Merge Request.

The script presented in the example depends on additional bash-scripts:

1. [mr_check_existing.sh](../../examples/GitLab/scripts/mr_check_existing.sh) - check if opened MR exists
   from source depending on some patter to target branch.
2. [commit_and_push.sh](../../examples/GitLab/scripts/commit_and_push.sh) - responsible for creating of new branch,
   configuring of author of commit, commiting and pushing of IDs of created issues.
3. [mr_create.sh](../../examples/GitLab/scripts/mr_create.sh) - creates merge request.
