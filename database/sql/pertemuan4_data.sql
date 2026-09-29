USE praktikum_web_2401020022;

INSERT INTO program_studi (nama_prodi) VALUES
('Teknik Informatika'),
('Sistem Informasi');

INSERT INTO mahasiswa
(nim, nama, email, usia, program_studi_id)
VALUES
('2401020022', 'Azizul Rizky Mahadi', 'azizul@gmail.com', 20, 2),
('2401020003', 'Akbar Rizky Lingga', 'akbar@gmail.com', 21, 1),
('2401020031', 'Muhammad Al-Fikry Akbar', 'Fikry@gmail.com', 20, 2),
('2401020013', 'Farhan Dwi Saputra', 'farhan@gmail.com', 19, 2);

UPDATE mahasiswa
SET email = 'azizul.rizky@gmail.com'
WHERE nim = '2401020022';

DELETE FROM mahasiswa
WHERE nim = '2401020013';

SELECT
    m.nim,
    m.nama,
    m.email,
    m.usia,
    p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
    ON p.id = m.program_studi_id
ORDER BY m.nim;