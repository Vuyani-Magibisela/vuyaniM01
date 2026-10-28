-- Private workLOG workspace. Additive and safe to apply repeatedly.
CREATE TABLE IF NOT EXISTS worklog_workspaces (
    owner_id INT PRIMARY KEY,
    revision BIGINT NOT NULL DEFAULT 0,
    timezone VARCHAR(64) NOT NULL DEFAULT 'Africa/Johannesburg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS worklog_records (
    owner_id INT NOT NULL,
    id CHAR(36) NOT NULL,
    kind ENUM('task','week') NOT NULL,
    version INT NOT NULL DEFAULT 1,
    data LONGTEXT NOT NULL CHECK (JSON_VALID(data)),
    deleted_at DATETIME NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (owner_id, id),
    INDEX idx_worklog_kind (owner_id, kind, deleted_at),
    FOREIGN KEY (owner_id) REFERENCES worklog_workspaces(owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS worklog_changes (
    owner_id INT NOT NULL,
    revision BIGINT NOT NULL,
    record_id CHAR(36) NOT NULL,
    snapshot LONGTEXT NOT NULL CHECK (JSON_VALID(snapshot)),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (owner_id, revision),
    FOREIGN KEY (owner_id) REFERENCES worklog_workspaces(owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS worklog_devices (
    id CHAR(36) PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    last_seen DATETIME NULL,
    acknowledged_revision BIGINT NOT NULL DEFAULT 0,
    revoked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES worklog_workspaces(owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS worklog_operations (
    owner_id INT NOT NULL,
    operation_id CHAR(36) NOT NULL,
    request_hash CHAR(64) NOT NULL,
    result LONGTEXT NOT NULL CHECK (JSON_VALID(result)),
    PRIMARY KEY (owner_id, operation_id),
    FOREIGN KEY (owner_id) REFERENCES worklog_workspaces(owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS worklog_conflicts (
    id CHAR(36) PRIMARY KEY,
    owner_id INT NOT NULL,
    record_id CHAR(36) NOT NULL,
    detail LONGTEXT NOT NULL CHECK (JSON_VALID(detail)),
    resolved_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_worklog_conflicts (owner_id, resolved_at),
    FOREIGN KEY (owner_id) REFERENCES worklog_workspaces(owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
