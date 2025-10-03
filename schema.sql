-- schema.sql
-- Schema for our Appointment App

-- NOTE: the EXCLUDE constraints below use the btree_gist extension.

-- 0. Extensions
-- Installs the btree_gist extension (required so
-- integers can be used with GiST/EXCLUDE)
CREATE EXTENSION IF NOT EXISTS btree_gist;

-- 1. users
CREATE TABLE IF NOT EXISTS users (
    id              BIGSERIAL PRIMARY KEY,
    email           VARCHAR(255) NOT NULL UNIQUE,
    password_hash   TEXT NOT NULL,
    full_name       VARCHAR(200),
    role            VARCHAR(20) NOT NULL, /* 'user' / 'provider' / 'admin' */
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 2. categories
CREATE TABLE IF NOT EXISTS categories (
    id              SERIAL PRIMARY KEY,
    name            VARCHAR(50) NOT NULL UNIQUE,
    description     TEXT
);

-- seed the categories.
INSERT INTO categories (name, description)
VALUES
  ('Medical', 'Medical services'),
  ('Beauty',  'Beauty & salon services'),
  ('Fitness', 'Fitness trainers, classes and sessions')
ON CONFLICT (name) DO NOTHING;

-- 3. provider_profiles (one-to-one with users where role = 'provider')
CREATE TABLE IF NOT EXISTS provider_profiles (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    business_name   VARCHAR(200),
    category_id     INT REFERENCES categories(id),
    bio             TEXT,
    location        TEXT,
    timezone        VARCHAR(64) DEFAULT 'UTC',
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 4. appointment_slots - provider publishes availability
CREATE TABLE IF NOT EXISTS appointment_slots (
    id              BIGSERIAL PRIMARY KEY,
    provider_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_id     INT REFERENCES categories(id),
    start_time      TIMESTAMPTZ NOT NULL,
    end_time        TIMESTAMPTZ NOT NULL,
    capacity        INT NOT NULL DEFAULT 1,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (end_time > start_time)
);

-- Povider can not insert two slots whose start_time and end_time are
-- exactly the same. The database will reject the second insert.
CREATE UNIQUE INDEX IF NOT EXISTS ux_provider_slot_time
  ON appointment_slots (provider_id, start_time, end_time);

-- Adds a new column `slot_range` of type tstzrange to appointment_slots
ALTER TABLE appointment_slots
  ADD COLUMN IF NOT EXISTS slot_range tstzrange
    -- Automatically compute and store the time-range [start_time, end_time] from the row
    -- end==next start is allowed because of '[)'
    GENERATED ALWAYS AS (tstzrange(start_time, end_time, '[)')) STORED;

-- GIST index for slot_range (speeds overlap/exclude)
CREATE INDEX IF NOT EXISTS idx_slots_provider_range
  ON appointment_slots USING GIST (provider_id, slot_range);


-- Prevent provider from creating overlapping slots
-- NOTE: A plain "ALTER TABLE ... ADD CONSTRAINT IF NOT EXISTS ..." failed
--       on the server (parser error: "VALID expected, got 'EXISTS'").
--       That is why we use PL/pgSQL DO block that checks pg_constraint first
--       and only executes the ALTER TABLE when the constraint is missing.
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_constraint
    WHERE conname = 'no_overlap_provider_slots'
  ) THEN
    EXECUTE
      'ALTER TABLE appointment_slots
         ADD CONSTRAINT no_overlap_provider_slots
         EXCLUDE USING GIST (provider_id WITH =, slot_range WITH &&)';
  END IF;
END;
$$ LANGUAGE plpgsql;

-- 5. appointments - actual bookings; snapshot start/end from slot when booking
CREATE TABLE IF NOT EXISTS appointments (
    id              BIGSERIAL PRIMARY KEY,
    slot_id         BIGINT NOT NULL UNIQUE REFERENCES appointment_slots(id) ON DELETE RESTRICT,
    user_id         BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_id     INT REFERENCES categories(id),
    start_time      TIMESTAMPTZ NOT NULL,
    end_time        TIMESTAMPTZ NOT NULL,
    status          VARCHAR(30) NOT NULL DEFAULT 'booked',
    notes           TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CHECK (end_time > start_time)
);

-- Purpose: make overlap checks (EXCLUDE, queries with &&)
-- simple and fast by using a single range colum
-- end==next start is allowed because of '[)'
ALTER TABLE appointments
  ADD COLUMN IF NOT EXISTS appt_range tstzrange
    GENERATED ALWAYS AS (tstzrange(start_time, end_time, '[)')) STORED;

-- Prevent user from having overlapping booked appointments
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_constraint
    WHERE conname = 'no_user_overlap'
  ) THEN
    EXECUTE
      'ALTER TABLE appointments
         ADD CONSTRAINT no_user_overlap
         EXCLUDE USING GIST (user_id WITH =, appt_range WITH &&)';
  END IF;
END;
$$ LANGUAGE plpgsql;

-- Prevent provider from having overlapping booked appointments
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_constraint
    WHERE conname = 'no_provider_overlap'
  ) THEN
    EXECUTE
      'ALTER TABLE appointments
         ADD CONSTRAINT no_provider_overlap
         EXCLUDE USING GIST (provider_id WITH =, appt_range WITH &&)';
  END IF;
END;
$$ LANGUAGE plpgsql;

-- Index to speed up queries that fetch a user's appointments ordered/filtered by creation time
CREATE INDEX IF NOT EXISTS idx_appointments_user_created ON appointments (user_id, created_at);

-- Index to speed up provider calendar queries (find appointments for a provider by start_time)
CREATE INDEX IF NOT EXISTS idx_appointments_provider_start ON appointments (provider_id, start_time);

-- Index to speed up category-based reports and time-range queries (e.g., appointments per category by start_time)
CREATE INDEX IF NOT EXISTS idx_appointments_category_start ON appointments (category_id, start_time);

-- 6. notifications - reminders / alerts / system messages
CREATE TABLE IF NOT EXISTS notifications (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT REFERENCES users(id) ON DELETE CASCADE,
    appointment_id  BIGINT REFERENCES appointments(id) ON DELETE CASCADE,
    type            VARCHAR(50),
    payload         JSONB,
    sent            BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Index to speed up fetching a user's notifications filtered by the sent flag (e.g., retrieve unsent notifications)
CREATE INDEX IF NOT EXISTS idx_notif_user_unsent
  ON notifications (user_id)
  WHERE sent = false;

