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
        $table->string('nim', 20)->unique();
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
            'id' => 1,
            'nim' => '2201010001',
            'nama' => 'Andi Saputra',
            'email' => 'andi.saputra@example.com',
            'usia' => 20,
            'program_studi_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2,
            'nim' => '2201010002',
            'nama' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'usia' => 19,
            'program_studi_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 3,
            'nim' => '2201020001',
            'nama' => 'Budi Pratama',
            'email' => 'budi@example.com',
            'usia' => 21,
            'program_studi_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 4,
            'nim' => '2201010003',
            'nama' => 'Rina Kurnia',
            'email' => 'rina@example.com',
            'usia' => 20,
            'program_studi_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
});

it('displays all 4 students on index page ordered by nim', function () {
    $response = $this->get(route('mahasiswa.index'));

    $response->assertOk()
        ->assertSee('Andi Saputra')
        ->assertSee('Siti Rahma')
        ->assertSee('Budi Pratama')
        ->assertSee('Rina Kurnia');
});

it('filters students by search keyword q', function () {
    $response = $this->get(route('mahasiswa.index', ['q' => 'Rina']));

    $response->assertOk()
        ->assertSee('Rina Kurnia')
        ->assertDontSee('Andi Saputra')
        ->assertDontSee('Siti Rahma')
        ->assertDontSee('Budi Pratama');
});

it('filters students by study program id', function () {
    $response = $this->get(route('mahasiswa.index', ['program_studi_id' => 2]));

    $response->assertOk()
        ->assertSee('Budi Pratama')
        ->assertDontSee('Andi Saputra')
        ->assertDontSee('Siti Rahma')
        ->assertDontSee('Rina Kurnia');
});

it('filters students by both keyword and study program', function () {
    $response = $this->get(route('mahasiswa.index', ['q' => 'Rina', 'program_studi_id' => 1]));

    $response->assertOk()
        ->assertSee('Rina Kurnia')
        ->assertDontSee('Andi Saputra');
});

it('shows edit form with existing student values', function () {
    $response = $this->get(route('mahasiswa.edit', 4));

    $response->assertOk()
        ->assertSee('Ubah Mahasiswa')
        ->assertSee('Rina Kurnia')
        ->assertSee('rina@example.com')
        ->assertSee('Simpan Perubahan');
});

it('redirects with error when student id to edit is not found', function () {
    $response = $this->get(route('mahasiswa.edit', 9999));

    $response->assertRedirect(route('mahasiswa.index'))
        ->assertSessionHas('gagal', 'Data mahasiswa tidak ditemukan.');
});

it('successfully updates student data', function () {
    $response = $this->put(route('mahasiswa.update', 4), [
        'nim' => '2201010003',
        'nama' => 'Rina Kurniawati',
        'email' => 'rina.kurniawati@example.com',
        'usia' => 21,
        'program_studi_id' => 1,
    ]);

    $response->assertRedirect(route('mahasiswa.index'))
        ->assertSessionHas('berhasil', 'Data mahasiswa berhasil diperbarui.');

    $this->assertDatabaseHas('mahasiswa', [
        'id' => 4,
        'nama' => 'Rina Kurniawati',
        'email' => 'rina.kurniawati@example.com',
        'usia' => 21,
    ]);
});

it('rejects update when email is already taken by another student', function () {
    $response = $this->put(route('mahasiswa.update', 4), [
        'nim' => '2201010003',
        'nama' => 'Rina Kurnia',
        'email' => 'andi.saputra@example.com',
        'usia' => 20,
        'program_studi_id' => 1,
    ]);

    $response->assertSessionHas('gagal', 'NIM atau email sudah digunakan mahasiswa lain.');

    $this->assertDatabaseHas('mahasiswa', [
        'id' => 4,
        'email' => 'rina@example.com',
    ]);
});

it('successfully deletes a student', function () {
    $response = $this->delete(route('mahasiswa.destroy', 3));

    $response->assertRedirect(route('mahasiswa.index'))
        ->assertSessionHas('berhasil', 'Data mahasiswa berhasil dihapus.');

    $this->assertDatabaseMissing('mahasiswa', [
        'id' => 3,
        'nama' => 'Budi Pratama',
    ]);
    $this->assertDatabaseCount('mahasiswa', 3);
});

it('redirects with error when deleting non-existent student', function () {
    $response = $this->delete(route('mahasiswa.destroy', 9999));

    $response->assertRedirect(route('mahasiswa.index'))
        ->assertSessionHas('gagal', 'Data mahasiswa tidak ditemukan.');
});
