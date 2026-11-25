<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Buku</title>
</head>
<body>
    <h1>Daftar Buku</h1>
    <p>Masuk sebagai {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Keluar</button>
    </form>
    @can('kelola-buku')
        <p><a href="{{ route('buku.create') }}">Tambah Buku</a></p>
    @endcan

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <form method="GET" action="{{ route('buku.index') }}">
        <label for="cari">Cari kode atau judul</label>
        <input id="cari" name="cari" value="{{ $cari }}">
        <button type="submit">Cari</button>
        <a href="{{ route('buku.index') }}">Reset</a>
    </form>

    <table border="1">
        <thead>
            <tr>
                <th>Kode</th><th>Judul</th><th>Kategori</th>
                @can('kelola-buku')
                    <th>Aksi</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @forelse ($bukus as $buku)
                <tr>
                    <td>{{ $buku->kode }}</td>
                    <td>{{ $buku->judul }}</td>
                    <td>{{ $buku->kategori->nama }}</td>
                    @can('kelola-buku')
                        <td>
                            <a href="{{ route('buku.edit', $buku) }}">Ubah</a>
                            <form action="{{ route('buku.destroy', $buku) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus buku ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    @endcan
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->can('kelola-buku') ? 4 : 3 }}">
                        Data buku tidak ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
