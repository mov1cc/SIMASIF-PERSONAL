CREATE TABLE users (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telepon VARCHAR(20),
    role user_role NOT NULL DEFAULT 'pegawai',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    reset_token VARCHAR(255),
    reset_token_expires_at TIMESTAMP,
    CONSTRAINT users_email_check CHECK (email <> '')
);

COMMENT ON TABLE users IS 'Akun internal Owner dan Pegawai Studio Flamboyan';
COMMENT ON COLUMN users.password IS 'Disimpan dalam bentuk hash bcrypt, tidak pernah plaintext';
COMMENT ON COLUMN users.no_telepon IS 'Informasi kontak';
COMMENT ON COLUMN users.last_login_at IS 'Wkatu login terakhir, diperbarui oleh AuthService setiap kali login berhasil, dipakai Owner untuk memantau aktivitas pegawai';
COMMENT ON COLUMN users.reset_token IS 'Token sementara untuk fitur lupa password, dipakai di tahap 13';
COMMENT ON COLUMN users.reset_token_expires_at IS 'Batas waktu berlaku reset_token, dipakai di Tahap 13';
COMMENT ON CONSTRAINT users_email_check ON users IS 'Mencegah string kosong lolos sebagai email, UNIQUE saja tidak menjamin ini karena string kosong dianggap satu nilai valid yang berbeda dari NULL';
