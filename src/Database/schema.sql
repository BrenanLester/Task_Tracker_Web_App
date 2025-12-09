CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    activity_count INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tasks (
    task_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    subject TEXT,
    quadrant TEXT CHECK(quadrant IN ('urgent-important','important','urgent','others')) DEFAULT 'others',
    priority TEXT CHECK(priority IN ('Low','Medium','High')) DEFAULT 'Medium',
    status TEXT CHECK(status IN ('Pending','In progress', 'Completed')) DEFAULT 'Pending',
    due_date DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Trigger: increment user's activity_count when a task is inserted
    CREATE TRIGGER IF NOT EXISTS trg_tasks_increment_activity
    AFTER INSERT ON tasks
    FOR EACH ROW
    BEGIN
        UPDATE users SET activity_count = COALESCE(activity_count, 0) + 1 WHERE user_id = NEW.user_id;
    END;

    -- Trigger: decrement user's activity_count when a task is deleted
    CREATE TRIGGER IF NOT EXISTS trg_tasks_decrement_activity
    AFTER DELETE ON tasks
    FOR EACH ROW
    BEGIN
        UPDATE users SET activity_count = COALESCE(activity_count, 0) - 1 WHERE user_id = OLD.user_id;
    END;

    -- Trigger: adjust activity_count when a task is reassigned to a different user
    CREATE TRIGGER IF NOT EXISTS trg_tasks_update_userid_activity
    AFTER UPDATE OF user_id ON tasks
    FOR EACH ROW
    WHEN OLD.user_id IS NOT NEW.user_id
    BEGIN
        UPDATE users SET activity_count = COALESCE(activity_count, 0) - 1 WHERE user_id = OLD.user_id;
        UPDATE users SET activity_count = COALESCE(activity_count, 0) + 1 WHERE user_id = NEW.user_id;
    END;

-- Index to speed lookups by user
CREATE INDEX IF NOT EXISTS idx_tasks_user_id ON tasks(user_id);

-- Trigger: update tasks.updated_at timestamp on row update
-- (updated_at is maintained by application SQL on updates)