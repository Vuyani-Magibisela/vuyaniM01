# workLOG: admin tasks and Obsidian sync

## Daily use

Sign in as the configured owner and choose **To Do / workLOG** in the admin navigation, or open **/admin/todo**. The dashboard also provides a shortcut. Local development runs at http://localhost:8090/admin/todo.

- **Weeks:** edit tasks, priorities, status, areas, flexible schedules and dates. Changes autosave; wait for the saved indicator before closing.
- **Inbox:** capture tasks before assigning a week.
- **Today:** unfinished tasks planned for today or earlier, overdue deadlines, and waiting tasks whose follow-up date has arrived.
- **Weekly review:** record reflections and move selected unfinished tasks into the next week without duplicating them.
- **Obsidian:** inspect bridge acknowledgement, pair/revoke devices and resolve conflicts.
- **Archive bin:** restore removed tasks.

Planned dates and deadlines are separate. Dates use Africa/Johannesburg. Search covers task text, area and waiting-on person. Markdown links and emphasis remain editable text.

## Architecture and access

The existing PHP MVC application and MariaDB store the authoritative, versioned records. Migration 010 adds six tables: workspaces, records, changes, operations, devices and conflicts. Task/week payloads are validated JSON; ownership, versions and revisions are relational columns. Workspace locks serialize writes and imports; operation IDs make device retries idempotent.

Only the administrator whose user ID matches WORKLOG_OWNER_ID can access the page, browser APIs or navigation entry. Browser writes require a session and CSRF token. Device APIs accept a separate revocable bearer token, stored as a hash in the database. Private responses disable caching.

Browser routes are /todo/state, /todo/tasks, /todo/weeks, /todo/import, /todo/export/{week-id}, /todo/devices and /todo/conflicts/{id}. The Windows bridge uses /todosync/pull, /todosync/push and /todosync/ack. Production URLs include the site's configured BASE_URL, such as /public.

## Install or deploy the website

1. Back up the database and vault before migrating.
2. Deploy the branch's application files and additive migration.
3. Set WORKLOG_OWNER_ID to the intended existing admin user ID in the environment. An empty value disables access. The CLI command below lists local admin IDs.
4. Run **only migration 010** against an existing database. The legacy all-migrations command also replays seeds.
5. Sign in, verify owner access and import the original note using **Import note**. Review its dates and task count before committing. A changed workspace requires a new preview.

From the repository root:

~~~powershell
docker compose exec -T app php tools/worklog-admin.php info
docker compose exec -T app php database/run_migrations.php --file=010_create_worklog_tables.sql
~~~

For Docker, recreate the app service after changing .env so it receives the new environment. On hosting, use PHP CLI from portfolio-website/ and configure HTTPS. Nothing in this branch automatically deploys production.

The import commits atomically, preserves other tasks and refuses divergent existing task data. Ongoing note edits should use the bridge.

## Install the Windows bridge

Requirements: Node.js with native fetch and node:test support (Node 22 or newer), Windows PowerShell, a local vault and a reachable website. Stop the old Work/todo-dashboard/server.js writer before enabling the bridge.

In workLOG > Obsidian, pair a device and copy its token once. Run:

~~~powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\tools\worklog-bridge\install.ps1 -Url 'https://your-host/public' -Vault 'D:\path\to\vault'
~~~

The installer prompts privately for the token, verifies the initial note against imported tasks and registers **workLOG Obsidian Bridge** at Windows sign-in. It stores a Windows-user-encrypted credential, state, backups and logs under %LOCALAPPDATA%\workLOG with restricted permissions. Keep this directory outside the vault and outside Git.

The bridge polls about every ten seconds while the computer is awake and the Windows user is signed in. Network failures retry with backoff. The website remains usable while the bridge is offline; Obsidian edits synchronize after reconnection. The web UI requires connectivity and does not persist private drafts offline.

Managed vault files:

| File | Purpose |
| --- | --- |
| Work/To Do.md | Current week's tasks |
| Work/workLOG/Inbox.md | Tasks without a week |
| Work/workLOG/Weeks/YYYY-MM-DD.md | Other weeks, including archives |

Hidden IDs connect rows to database records. Preserve these comments when editing or moving existing rows. New rows can omit IDs. Keep the table headers intact. The bridge preserves unrelated note content and related-note links, backs up replaced files, and archives the previous current week before rollover.

Obsidian Sync remains responsible for forwarding local vault changes to other devices. Check that application's sync status separately. Do not run multiple bridge instances against the same vault.

## Conflicts, recovery and rollback

Different-field edits merge automatically. Competing edits to the same field pause note writes and appear in **Obsidian** for an explicit website/note choice. Removing a Markdown row also requires confirmation there; deleting an entire managed file stops sync rather than deleting its tasks.

For troubleshooting:

~~~powershell
Get-ScheduledTask -TaskName 'workLOG Obsidian Bridge'
Get-Content "$env:LOCALAPPDATA\workLOG\bridge.log" -Tail 30
Stop-ScheduledTask -TaskName 'workLOG Obsidian Bridge'
Start-ScheduledTask -TaskName 'workLOG Obsidian Bridge'
~~~

A normal running task can report LastTaskResult 267009. An invalid token requires pairing again and rerunning the installer. Reinstallation preserves sync state; changing to a different website/database requires a separate state directory and a verified import.

Interrupted writes recover from write-journal.json. If recovery detects a newer external edit, stop the bridge, copy the vault and private state directory, and reconcile the affected file with its journal and backup before restarting. Do not delete state/journals as a shortcut. Backups currently have no automatic retention policy.

To roll back, stop the scheduled task, export the latest weeks and back up both database and vault. Revert application code and disable WORKLOG_OWNER_ID. Keep the additive tables until recovery is verified; do not run reseed or drop production data. Only resume the old writer after saving the latest task data and ensuring the bridge is stopped.

## Verification

~~~powershell
node --test tools/worklog-bridge/markdown.test.mjs
docker compose exec -T -e DB_NAME=worklog_test app php tools/worklog-test.php
node tools/worklog-bridge/http-check.mjs --local
~~~

Create a separate worklog_test database and grant the development database user access before the PHP test. That test refuses other database names and clears only its isolated workLOG tables. The HTTP check is local-only and creates temporary sessions, tasks and devices in the development workspace; pause the bridge during it. With WORKLOG_TEST_FIXTURE=1, tools/worklog-cleanup-tests.php removes only explicitly identified test records. These CLI fixtures are outside the public document root.

Validated on 27 October 2026: 24 PHP assertions, nine Node tests, 17 local HTTP assertions, desktop/mobile UI checks, database backup restoration into a separate test database, and a live website → Obsidian → website round trip followed by test-task removal.

The local cutover imported 16 original note tasks and preserved the additional website task. The old Node dashboard writer was stopped; the scheduled bridge is active against localhost:8090. Production cutover remains separate.

## Productivity research and later additions

The implemented Today view follows the daily-focus approach described in [Microsoft's My Day guidance](https://support.microsoft.com/en-gb/todo/my-day-and-suggestions). Weekly reflections and carry-forward support the regular review process described by [Todoist](https://www.todoist.com/it/productivity-methods/weekly-review). Our implementation keeps overdue work visible rather than resetting daily selections.

Possible next increments are recurring task templates for routine reviews, optional reminders for waiting tasks, and calendar time blocks with estimated effort. These are proposals, not implemented features.

For vault conflicts, consult [Obsidian's official troubleshooting guidance](https://obsidian.md/help/sync/troubleshoot). Its file-level conflict settings complement the bridge's task-level conflict checks; neither replaces backups.
