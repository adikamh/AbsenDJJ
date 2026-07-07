@extends('dashboard.layout')

@section('title', 'Kelola Peserta')
@section('header_title', 'Kelola Peserta')

@section('content')
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Peserta</h2>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Pembimbing</th>
                        <th>Instansi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peserta as $user)
                        <tr>
                            <td><strong>{{ $user->nama_lengkap }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->pembimbing?->nama_lengkap ?? '-' }}</td>
                            <td>{{ $user->instansi?->nama_instansi ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $user->status_aktif ? 'badge-success' : 'badge-warning' }}">
                                    {{ $user->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">Belum ada peserta terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
