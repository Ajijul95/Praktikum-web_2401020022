<?php

it('shows all four books on the index page', function () {
    $response = $this->get(route('buku.index'));

    $response->assertOk()
        ->assertSee('BK001')
        ->assertSee('BK002')
        ->assertSee('BK003')
        ->assertSee('BK004')
        ->assertSee('Pemrograman Web Modern');
});

it('searches book titles and keeps the query in the search field', function () {
    $response = $this->get(route('buku.index', ['cari' => 'Pemrograman Web']));

    $response->assertOk()
        ->assertSee('BK004')
        ->assertSee('Pemrograman Web Modern')
        ->assertSee('value="Pemrograman Web"', false)
        ->assertDontSee('BK001')
        ->assertDontSee('BK002')
        ->assertDontSee('BK003');
});

it('searches by book code and displays its title', function () {
    $response = $this->get(route('buku.index', ['cari' => 'BK004']));

    $response->assertOk()
        ->assertSee('BK004')
        ->assertSee('Pemrograman Web Modern')
        ->assertDontSee('BK001')
        ->assertDontSee('BK002')
        ->assertDontSee('BK003');
});

it('shows a message when no book matches the search', function () {
    $response = $this->get(route('buku.index', ['cari' => 'TidakAda']));

    $response->assertOk()
        ->assertSee('Data buku tidak ditemukan.')
        ->assertDontSee('BK004');
});

it('shows the detail page for BK004', function () {
    $response = $this->get(route('buku.show', 'BK004'));

    $response->assertOk()
        ->assertSee('Kode: BK004')
        ->assertSee('Judul: Pemrograman Web Modern')
        ->assertSee(route('buku.index'));
});

it('returns a 404 for an unknown book code', function () {
    $this->get(route('buku.show', 'BK999'))
        ->assertNotFound();
});

it('shows the default Laravel welcome page at the root URL', function () {
    $this->get(route('home'))
        ->assertOk();
});
