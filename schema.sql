-- People who contacted you
CREATE TABLE contacts (
  id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY, -- or AUTO_INCREMENT
  name         VARCHAR(120) NOT NULL,
  email        VARCHAR(255) NOT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Each form submission (the message itself)
CREATE TABLE messages (
  id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  contact_id   BIGINT NOT NULL REFERENCES contacts(id),   -- FK
  subject      VARCHAR(200) NOT NULL,
  body         TEXT NOT NULL,
  ip_address   VARCHAR(45),       -- IPv4/IPv6
  user_agent   TEXT,
  status       VARCHAR(24) NOT NULL DEFAULT 'queued',  -- queued|sent|failed
  sent_at      TIMESTAMP,          -- when the email was successfully sent
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Optional: track delivery/processing events (useful for debugging)
CREATE TABLE message_events (
  id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  message_id   BIGINT NOT NULL REFERENCES messages(id),
  event        VARCHAR(32) NOT NULL,     -- e.g., 'stored','email_sent','email_failed'
  details      JSON,                     -- or TEXT if DB lacks JSON
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Optional: file uploads in the future
CREATE TABLE attachments (
  id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  message_id   BIGINT NOT NULL REFERENCES messages(id),
  filename     VARCHAR(255) NOT NULL,
  mime_type    VARCHAR(127),
  size_bytes   BIGINT,
  storage_url  TEXT,                     -- S3/GCS/local path
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Helpful indexes
CREATE INDEX idx_contacts_email        ON contacts(email);
CREATE INDEX idx_messages_contact_id   ON messages(contact_id);
CREATE INDEX idx_messages_created_at   ON messages(created_at);
CREATE INDEX idx_messages_status       ON messages(status);
