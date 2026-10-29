-- Managed workLOG areas (task types). Additive and safe to apply repeatedly.
ALTER TABLE worklog_workspaces
    ADD COLUMN IF NOT EXISTS areas LONGTEXT NULL CHECK (areas IS NULL OR JSON_VALID(areas));
