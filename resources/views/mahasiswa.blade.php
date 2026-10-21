<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
</head>

<body>
    <h1>Data Mahasiswa</h1>

    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>Tambah Mahasiswa</h2>
    <form action="{{ route('mahasiswa.store') }}" method="POST">
        @csrf

        <p>
            <label for="nim">NIM</label><br>
            <input id="nim" type="text" name="nim" value="{{ old('nim') }}">
        </p>

        <p>
            <label for="nama">Nama</label><br>
            <input id="nama" type="text" name="nama" value="{{ old('nama') }}">
        </p>

        <p>
            <label for="email">Email</label><br>
            <input id="email" type="email" name="email" value="{{ old('email') }}">
        </p>

        <p>
            <label for="usia">Usia</label><br>
            <input id="usia" type="number" name="usia" value="{{ old('usia') }}">
        </p>

        <p>
            <label for="program_studi_id">Program Studi</label><br>
            <select id="program_studi_id" name="program_studi_id">
                <option value="">-- Pilih Program Studi --</option>
                @foreach ($daftarProgramStudi as $programStudi)
                    <option value="{{ $programStudi['id'] }}"
                        @selected(old('program_studi_id') == $programStudi['id'])>
                        {{ $programStudi['nama_prodi'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Tambah Data</button>
    </form>

    <h2>Daftar Mahasiswa</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Usia</th>
                <th>Program Studi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarMahasiswa as $mahasiswa)
                <tr>
                    <td>{{ $mahasiswa['nim'] }}</td>
                    <td>{{ $mahasiswa['nama'] }}</td>
                    <td>{{ $mahasiswa['email'] }}</td>
                    <td>{{ $mahasiswa['usia'] }}</td>
                    <td>{{ $mahasiswa['nama_prodi'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Data belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
