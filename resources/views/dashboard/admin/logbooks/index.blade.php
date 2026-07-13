@extends('dashboard.layout')

@section('title', 'Logbook Anak Didik')
@section('header_title', 'Logbook Kegiatan Anak Didik')

@push('styles')
    @vite('resources/css/admin/interns.css')
@endpush

@section('content')
    <!-- Filter & Search Card -->
    <div class="content-card" style="margin-bottom: 30px;">
        <div class="card-header">
            <h2 class="card-title">Filter & Pencarian Logbook</h2>
        </div>
        <form action="{{ route('admin.logbooks') }}" method="GET" class="filter-row" style="padding: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div class="filter-group" style="flex: 2; min-width: 250px; display: flex; flex-direction: column; gap: 8px;">
                <label for="search" style="font-weight: 500; font-size: 0.85rem; color: var(--text-secondary);">Cari (Nama Intern / Kegiatan / Deskripsi)</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Masukkan nama intern atau kata kunci kegiatan..." style="width: 100%;" onchange="this.form.submit()">
            </div>
            
            <div class="filter-group" style="flex: 1; min-width: 180px; display: flex; flex-direction: column; gap: 8px;">
                <label for="status_approval" style="font-weight: 500; font-size: 0.85rem; color: var(--text-secondary);">Status Approval</label>
                <select id="status_approval" name="status_approval" class="filter-select" style="width: 100%;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Pending" {{ request('status_approval') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status_approval') === 'Approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Rejected" {{ request('status_approval') === 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div class="filter-actions" style="margin-top: 26px; display: flex; gap: 8px;">
                <button type="submit" class="btn-primary" style="padding: 10px 20px;">Filter</button>
                @if(request()->anyFilled(['search', 'status_approval']))
                    <a href="{{ route('admin.logbooks') }}" class="btn-secondary" style="padding: 10px 20px; text-decoration: none; display: inline-block;">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logbooks List Card -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Daftar Logbook Intern</h2>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Intern</th>
                        <th>Kegiatan</th>
                        <th>Deskripsi</th>
                        <th>Tag</th>
                        <th>Status</th>
                        <th>Catatan Pembimbing</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logbooks as $logbook)
                        <tr>
                            <td>{{ $logbook->tanggal->format('d M Y') }}</td>
                            <td>
                                <strong>{{ $logbook->user?->nama_lengkap }}</strong>
                                <br>
                                <span class="muted-small">{{ $logbook->user?->instansi?->nama_instansi ?? '-' }}</span>
                            </td>
                            <td><strong>{{ $logbook->kegiatan }}</strong></td>
                            <td>
                                <span class="muted-small" style="display: block; max-width: 250px; white-space: normal; line-height: 1.4;">
                                    {{ $logbook->deskripsi }}
                                </span>
                            </td>
                            <td>
                                @if($logbook->tags)
                                    @foreach(explode(',', $logbook->tags) as $tag)
                                        <span class="badge badge-info" style="font-size: 0.7rem; padding: 2px 6px; margin: 1px;">#{{ trim($tag) }}</span>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $logbook->status_approval === 'Approved' ? 'badge-success' : ($logbook->status_approval === 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $logbook->status_approval }}
                                </span>
                            </td>
                            <td>{{ $logbook->catatan_pembimbing ?? '-' }}</td>
                            <td>
                                @if($logbook->status_approval === 'Pending')
                                    <div style="display: flex; gap: 6px; flex-direction: column;">
                                        <form action="{{ route('admin.logbook.approve', $logbook->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="badge badge-success" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%;">Setujui</button>
                                        </form>
                                        <form action="{{ route('admin.logbook.reject', $logbook->id) }}" method="POST" style="margin: 0; display: flex; flex-direction: column; gap: 4px;">
                                            @csrf
                                            <input type="text" name="catatan_pembimbing" placeholder="Catatan..." style="font-size: 0.7rem; padding: 4px; border: 1px solid var(--glass-border); border-radius: 4px; background: rgba(255,255,255,0.05); color: #fff; width: 90px;" required>
                                            <button type="submit" class="badge badge-danger" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600;">Tolak</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="muted-small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">Tidak ada logbook anak didik yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logbooks->hasPages())
            {{ $logbooks->links('partials.pagination') }}
        @endif
    </div>
@endsection
