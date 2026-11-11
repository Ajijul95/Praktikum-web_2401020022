<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $buku->exists ? 'Ubah Buku' : 'Tambah Buku' }}</title>
</head>
<body>
    <h1>{{ $buku->exists ? 'Ubah Buku' : 'Tambah Buku' }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST"
          action="{{ $buku->exists ? route('buku.update', $buku) : route('buku.store') }}">
        @csrf
        @if ($buku->exists)
            @method('PUT')
        @endif

        <p>
            <label for="kode">Kode</label>
            <input id="kode" name="kode" maxlength="10" value="{{ old('kode', $buku->kode) }}">
        </p>
        <p>
            <label for="judul">Judul</label>
            <input id="judul" name="judul" value="{{ old('judul', $buku->judul) }}">
        </p>
        <p>
            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id">
                <option value="">-- Pilih kategori --</option>
                @foreach ($kategoris as $kategori)
                    <option value="{{ $kategori->id }}"
                        @selected(old('kategori_id', $buku->kategori_id) == $kategori->id)>
                        {{ $kategori->nama }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Simpan</button>
    </form>

    <p><a href="{{ route('buku.index') }}">Kembali</a></p>
</body>
</html>
