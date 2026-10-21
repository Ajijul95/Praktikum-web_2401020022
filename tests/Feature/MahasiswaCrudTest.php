<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
        'database.connections.sqlite.foreign_key_constraints' => true,
    ]);
    DB::purge('sqlite');

    Schema::connection('sqlite')->create('program_studi', function (Blueprint $table): void {
        $table->id();
        $table->string('nama_prodi', 100)->unique();
    });

    Schema::connection('sqlite')->create('mahasiswa', function (Blueprint $table): void {
        $table->id();
        $table->char('nim', 10)->unique();
        $table->string('nama', 100);
        $table->string('email', 100)->unique();
        $table->unsignedTinyInteger('usia');
        $table->foreignId('program_studi_id')->constrained('program_studi');
        $table->timestamps();
    });

    DB::connection('sqlite')->table('program_studi')->insert([
        ['id' => 1, 'nama_prodi' => 'Teknik Informatika'],
        ['id' => 2, 'nama_prodi' => 'Sistem Informasi'],
    ]);

    DB::connection('sqlite')->table('mahasiswa')->insert([
        [
            'nim' => '2201010001',
            'nama' => 'Andi Saputra',
            'email' => 'andi.saputra@example.com',
            'usia' => 20,
            'program_studi_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nim' => '2201010002',
            'nama' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'usia' => 19,
            'program_studi_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nim' => '2201020001',
            'nama' => 'Budi Pratama',
            'email' => 'budi@example.com',
            'usia' => 21,
            'program_studi_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
});

it('shows student rows and study programs on the read page', function () {
    $response = $this->get(route('mahasiswa.index'));

    $response
        ->assertOk()
        ->assertSee('Andi Saputra')
        ->assertSee('Siti Rahma')
        ->assertSee('Budi Pratama')
        ->assertSee('Teknik Informatika')
        ->assertSee('Sistem Informasi')
        ->assertSee('name="_token"', false);
});

it('creates a student with validated input and redirects with success feedback', function () {
    $dataMahasiswa = [
        'nim' => '2201010003',
        'nama' => 'Rina Kurnia',
        'email' => 'rina@example.com',
        'usia' => 20,
        'program_studi_id' => 1,
    ];

    $response = $this->from(route('mahasiswa.index'))
        ->post(route('mahasiswa.store'), $dataMahasiswa);

    $response
        ->assertRedirect(route('mahasiswa.index'))
        ->assertSessionHas('berhasil', 'Data mahasiswa berhasil ditambahkan.');

    $this->assertDatabaseHas('mahasiswa', [
        'nim' => '2201010003',
        'nama' => 'Rina Kurnia',
        'email' => 'rina@example.com',
        'usia' => 20,
        'program_studi_id' => 1,
    ]);
});

it('returns required-field errors without creating a student', function () {
    $response = $this->from(route('mahasiswa.index'))
        ->post(route('mahasiswa.store'), []);

    $response->assertSessionHasErrors([
        'nim',
        'nama',
        'email',
        'usia',
        'program_studi_id',
    ]);

    $this->assertDatabaseCount('mahasiswa', 3);
});

it('rejects an invalid email without creating a student', function () {
    $response = $this->from(route('mahasiswa.index'))
        ->post(route('mahasiswa.store'), [
            'nim' => '2201010003',
            'nama' => 'Rina Kurnia',
            'email' => 'email-tidak-valid',
            'usia' => 20,
            'program_studi_id' => 1,
        ]);

    $response->assertSessionHasErrors([
        'email' => 'Format email tidak valid.',
    ]);

    $this->assertDatabaseCount('mahasiswa', 3);
});

it('rejects a duplicate student number and keeps the existing records unchanged', function () {
    $response = $this->from(route('mahasiswa.index'))
        ->post(route('mahasiswa.store'), [
            'nim' => '2201010001',
            'nama' => 'Mahasiswa Duplikat',
            'email' => 'duplikat@example.com',
            'usia' => 20,
            'program_studi_id' => 1,
        ]);

    $response
        ->assertRedirect(route('mahasiswa.index'))
        ->assertSessionHas('gagal', 'NIM atau email sudah digunakan.')
        ->assertSessionHas('_old_input.nim', '2201010001');

    $this->assertDatabaseCount('mahasiswa', 3);
    $this->assertDatabaseMissing('mahasiswa', [
        'email' => 'duplikat@example.com',
    ]);
});

it('rejects a duplicate email and keeps the existing records unchanged', function () {
    $response = $this->from(route('mahasiswa.index'))
        ->post(route('mahasiswa.store'), [
            'nim' => '2201010003',
            'nama' => 'Mahasiswa Duplikat',
            'email' => 'andi.saputra@example.com',
            'usia' => 20,
            'program_studi_id' => 1,
        ]);

    $response
        ->assertRedirect(route('mahasiswa.index'))
        ->assertSessionHas('gagal', 'NIM atau email sudah digunakan.');

    $this->assertDatabaseCount('mahasiswa', 3);
});

it('rejects a program that does not exist', function () {
    $response = $this->from(route('mahasiswa.index'))
        ->post(route('mahasiswa.store'), [
            'nim' => '2201010003',
            'nama' => 'Rina Kurnia',
            'email' => 'rina@example.com',
            'usia' => 20,
            'program_studi_id' => 999,
        ]);

    $response->assertSessionHasErrors([
        'program_studi_id' => 'Program studi yang dipilih tidak tersedia.',
    ]);

    $this->assertDatabaseCount('mahasiswa', 3);
});

it('returns a safe error message when the database cannot be read', function () {
    config([
        'database.connections.sqlite.database' => storage_path(
            'app/missing-praktikum6-database/database.sqlite'
        ),
    ]);
    DB::purge('sqlite');

    $response = $this->get(route('mahasiswa.index'));

    $response
        ->assertInternalServerError()
        ->assertSee('Data tidak dapat dibaca. Periksa koneksi dan query.');
});
