CREATE TABLE IF NOT EXISTS patients (
  id BIGSERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  birth_date DATE,
  phone TEXT,
  cellphone TEXT,
  email TEXT,
  status TEXT NOT NULL DEFAULT 'novo',created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

