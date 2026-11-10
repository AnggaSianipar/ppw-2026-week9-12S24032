<x-layout>
    <style>
        .mahasiswa-table {
            table-layout: fixed;
            min-width: 820px;
        }

        .mahasiswa-table th,
        .mahasiswa-table td {
            vertical-align: middle;
            padding: 0.8rem 0.75rem;
        }

        .mahasiswa-table th {
            white-space: nowrap;
        }

        .mahasiswa-table .col-nim { width: 13%; }
        .mahasiswa-table .col-nama { width: 20%; }
        .mahasiswa-table .col-email { width: 25%; }
        .mahasiswa-table .col-prodi { width: 20%; }
        .mahasiswa-table .col-ipk { width: 10%; }
        .mahasiswa-table .col-pinjaman { width: 12%; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">Daftar Mahasiswa</h1>
        <a href="{{ route('mahasiswa.create') }}" class="btn btn-del">+ Tambah Mahasiswa</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 mahasiswa-table">
                    <thead class="table-light">
                        <tr>
                            <th class="col-nim">NIM</th>
                            <th class="col-nama">Nama</th>
                            <th class="col-email">Email</th>
                            <th class="col-prodi">Program Studi</th>
                            <th class="col-ipk">IPK</th>
                            <th class="col-pinjaman text-end">Buku Dipinjam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mahasiswas as $mhs)
                            <tr>
                                <td>{{ $mhs->nim }}</td>
                                <td>{{ $mhs->nama }}</td>
                                <td>{{ $mhs->email }}</td>
                                <td class="text-truncate">{{ $mhs->programStudi->nama }}</td>
                                <td>{{ number_format($mhs->ipk, 2) }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('mahasiswa.peminjaman', $mhs) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        {{ $peminjamanCounts[$mhs->nim] ?? 0 }} transaksi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data mahasiswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $mahasiswas->links() }}
    </div>
</x-layout> 