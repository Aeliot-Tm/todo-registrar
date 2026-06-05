## When source files are saved

After each **successful** registration, the issue key is written into the comment in memory and the
**source file is saved to disk right away**. Saving happens per registration, not after all TODOs in
a file and not at the end of the run.

### Incremental save

This **incremental save** reduces the risk of losing progress when a run stops early:

- Issues already created in the tracker keep matching keys in source code.
- A CI timeout, network error, or killed process does not undo keys that were already written.
- On the next run, comments that already contain a recognized issue key are **skipped** — no duplicate
  issues for work already done.

### Partially updated files on error

The run **stops on the first error** (fail-fast). If processing fails while a file still has unhandled
TODOs, the file on disk may already contain keys for earlier TODOs in that file, while later comments
in the same file remain without keys.

Example: a file has three new TODOs. Registrations for the first two succeed and both keys are written
to disk. The third registration fails. The saved file contains keys for the first two comments only; the
third still has no key. After you fix the cause of the failure and run again, the first two TODOs are
skipped and only the third is processed.

This is expected behavior. Keys already written to a file are **not rolled back** automatically.

If registration fails for a TODO, that comment is not updated and no save is performed for that attempt.
Parse errors and other failures that occur before a successful registration also leave the file unchanged
for that TODO.
