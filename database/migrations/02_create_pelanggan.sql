CREATE TABLE pelanggan (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW()
);

COMMENT ON TABLE pelanggan IS 'Data pelanggan yang melakukan pemesanan, diidentifikasi lewat nomor HP unik';