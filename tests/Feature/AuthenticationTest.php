<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    config([
        'database.default' => 'sqlite',
        'database.connections.sqlite.database' => ':memory:',
    ]);
    DB::purge('sqlite');

    Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });

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
});

it('shows a CSRF-protected login form', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Login Praktikum')
        ->assertSee('name="_token"', false)
        ->assertSee('type="password"', false);
});

it('redirects guests away from the book catalog to login', function () {
    $this->get(route('buku.index'))
        ->assertRedirect(route('login'));
});

it('rejects invalid credentials without retaining the password', function () {
    User::create([
        'name' => 'Pemilik Akun',
        'email' => 'azizul@gmail.com',
        'password' => Hash::make('kata-sandi-uji'),
    ]);

    $this->from(route('login'))
        ->post(route('login.proses'), [
            'email' => 'azizul@gmail.com',
            'password' => 'salah',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors([
            'email' => 'Email atau kata sandi salah.',
        ])
        ->assertSessionHas('_old_input.email', 'azizul@gmail.com')
        ->assertSessionMissing('_old_input.password');

    $this->assertGuest();
});

it('authenticates valid credentials and shows the account on the catalog', function () {
    $user = User::create([
        'name' => 'Pemilik Akun',
        'email' => 'azizul@gmail.com',
        'password' => Hash::make('kata-sandi-uji'),
    ]);

    $this->post(route('login.proses'), [
        'email' => 'azizul@gmail.com',
        'password' => 'kata-sandi-uji',
    ])
        ->assertRedirect(route('buku.index'));

    $this->assertAuthenticatedAs($user);

    $this->get(route('buku.index'))
        ->assertOk()
        ->assertSee('Masuk sebagai Pemilik Akun')
        ->assertSee('name="_token"', false)
        ->assertSee('Keluar')
        ->assertHeader('Cache-Control', 'no-store, private');
});

it('logs out, invalidates the session, and protects the catalog afterward', function () {
    $user = User::create([
        'name' => 'Pemilik Akun',
        'email' => 'azizul@gmail.com',
        'password' => Hash::make('kata-sandi-uji'),
    ]);

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $this->get(route('buku.index'))
        ->assertRedirect(route('login'));
});

it('does not expose logout as a GET route', function () {
    $this->get(route('logout'))
        ->assertMethodNotAllowed();
});
