<?php

use App\Models\Buku;
use App\Models\Kategori;
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

    Schema::connection('sqlite')->create('kategoris', function (Blueprint $table): void {
        $table->id();
        $table->string('nama')->unique();
        $table->timestamps();
    });

    Schema::connection('sqlite')->create('bukus', function (Blueprint $table): void {
        $table->id();
        $table->string('kode', 10)->unique();
        $table->string('judul');
        $table->foreignId('kategori_id')->constrained('kategoris');
        $table->timestamps();
    });

    DB::connection('sqlite')->table('kategoris')->insert([
        ['id' => 1, 'nama' => 'Basis Data', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'nama' => 'Pemrograman', 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::connection('sqlite')->table('bukus')->insert([
        [
            'id' => 1,
            'kode' => 'BK001',
            'judul' => 'Dasar PHP',
            'kategori_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => 2,
            'kode' => 'BK002',
            'judul' => 'Belajar Laravel',
            'kategori_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
});

it('lists books with their categories and supports searching by code or title', function () {
    $this->get(route('buku.index'))
        ->assertOk()
        ->assertSee('BK001')
        ->assertSee('Dasar PHP')
        ->assertSee('Belajar Laravel')
        ->assertSee('Pemrograman');

    $this->get(route('buku.index', ['cari' => 'BK002']))
        ->assertOk()
        ->assertSee('Belajar Laravel')
        ->assertDontSee('Dasar PHP');
});

it('shows create form with categories', function () {
    $this->get(route('buku.create'))
        ->assertOk()
        ->assertSee('Basis Data')
        ->assertSee('Pemrograman')
        ->assertSee('name="_token"', false);
});

it('creates a book using validated Eloquent data', function () {
    $this->post(route('buku.store'), [
        'kode' => 'BK003',
        'judul' => 'Pengantar SQL',
        'kategori_id' => 1,
    ])
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('status', 'Buku ditambahkan.');

    $this->assertDatabaseHas('bukus', [
        'kode' => 'BK003',
        'judul' => 'Pengantar SQL',
        'kategori_id' => 1,
    ]);

    expect(Buku::where('kode', 'BK003')->firstOrFail()->kategori->nama)
        ->toBe('Basis Data');
    expect(Kategori::findOrFail(1)->bukus)->toHaveCount(1);
});

it('rejects duplicate book codes without changing existing data', function () {
    $this->from(route('buku.create'))
        ->post(route('buku.store'), [
            'kode' => 'BK001',
            'judul' => 'Duplikat',
            'kategori_id' => 1,
        ])
        ->assertRedirect(route('buku.create'))
        ->assertSessionHasErrors([
            'kode' => 'Kode buku sudah digunakan.',
        ]);

    $this->assertDatabaseCount('bukus', 2);
    $this->assertDatabaseHas('bukus', ['kode' => 'BK001', 'judul' => 'Dasar PHP']);
});

it('rejects missing fields and unknown category ids', function () {
    $this->from(route('buku.create'))
        ->post(route('buku.store'), [
            'kode' => '',
            'judul' => '',
            'kategori_id' => 999,
        ])
        ->assertRedirect(route('buku.create'))
        ->assertSessionHasErrors(['kode', 'judul', 'kategori_id']);

    $this->assertDatabaseCount('bukus', 2);
});

it('updates an existing book and allows it to keep its own unique code', function () {
    $this->put(route('buku.update', 1), [
        'kode' => 'BK001',
        'judul' => 'Dasar PHP dan MySQL',
        'kategori_id' => 1,
    ])
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('status', 'Buku diperbarui.');

    $this->assertDatabaseHas('bukus', [
        'id' => 1,
        'kode' => 'BK001',
        'judul' => 'Dasar PHP dan MySQL',
        'kategori_id' => 1,
    ]);
});

it('rejects an update that uses another books code', function () {
    $this->from(route('buku.edit', 2))
        ->put(route('buku.update', 2), [
            'kode' => 'BK001',
            'judul' => 'Judul baru',
            'kategori_id' => 1,
        ])
        ->assertRedirect(route('buku.edit', 2))
        ->assertSessionHasErrors([
            'kode' => 'Kode buku sudah digunakan.',
        ]);

    $this->assertDatabaseHas('bukus', [
        'id' => 2,
        'kode' => 'BK002',
        'judul' => 'Belajar Laravel',
    ]);
});

it('shows existing values on the edit form', function () {
    $this->get(route('buku.edit', 1))
        ->assertOk()
        ->assertSee('value="BK001"', false)
        ->assertSee('value="Dasar PHP"', false)
        ->assertSee('Ubah Buku');
});

it('deletes a book and its route model binding resolves by id', function () {
    $this->delete(route('buku.destroy', 2))
        ->assertRedirect(route('buku.index'))
        ->assertSessionHas('status', 'Buku dihapus.');

    $this->assertDatabaseMissing('bukus', ['id' => 2]);
    $this->assertDatabaseCount('bukus', 1);
});

it('returns not found when a resource book id does not exist', function () {
    $this->get(route('buku.edit', 999))
        ->assertNotFound();
});
