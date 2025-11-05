CREATE TABLE IF NOT EXISTS patients (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL CHECK (name ~ '^[A-Za-zÀ-ÿ\s]+$'),
  cpf CHAR(11) UNIQUE CHECK (cpf ~ '^[0-9]{11}$'),
  birth_date DATE CHECK (birth_date <= CURRENT_DATE),
  phone VARCHAR(15) CHECK (phone ~ '^[0-9]{8,15}$'),
  cellphone VARCHAR(15) CHECK (cellphone ~ '^[0-9]{8,15}$'),
  email VARCHAR(255) CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
  status TEXT NOT NULL DEFAULT 'novo' CHECK (status IN ('novo', 'ativo', 'inativo')),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_patients_status ON patients (status);
