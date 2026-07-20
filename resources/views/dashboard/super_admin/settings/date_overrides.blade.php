@extends('dashboard.layout')

@section('title', 'Tanggal Khusus / Libur - Pengaturan')
@section('header_title', 'Tanggal Khusus & Libur Nasional')

@push('styles')
    @vite('resources/css/super_admin/settings.css')
@endpush

@section('content')
    <div class="settings-container">
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #ef4444; font-weight: 500;">
                {{ session('error') }}
            </div>
        @endif

        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 20px;">
                <h2 class="card-title" style="display: flex; align-items: center; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="color: var(--accent-primary);">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    Jadwal & Waktu Kehadiran
                </h2>
            </div>
            <div class="tab-content active" id="tab-date">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">
                        Atur jadwal khusus untuk tanggal tertentu atau tandai sebagai hari libur nasional.
                    </p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button type="button" onclick="document.getElementById('holiday-info-modal').style.display='flex'" class="btn-secondary" style="padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Info Sumber Data
                        </button>
                        <form action="{{ route('super-admin.schedules.sync-holidays') }}" method="POST" style="display: inline;" onsubmit="return handleSyncHolidaysSubmit(event)">
                            @csrf
                            <button type="submit" class="btn-secondary" style="padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011-1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 110 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                </svg>
                                Sinkronisasi API Manual
                            </button>
                        </form>
                        <button type="button" id="btn-add-date-override" class="btn-primary" style="padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            Tambah Tanggal
                        </button>
                    </div>
                </div>

                @if($dateOverrides->count() > 0)
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Jam Masuk</th>
                                    <th>Batas Absensi</th>
                                    <th>Jam Pulang</th>
                                    <th>Keterangan</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-date-body">
                                @foreach($dateOverrides as $dateOvr)
                                    <tr>
                                        <td><strong>{{ \Carbon\Carbon::parse($dateOvr->specific_date)->translatedFormat('d M Y') }}</strong></td>
                                        <td>
                                            @if($dateOvr->is_holiday)
                                                <span class="badge-holiday">Libur</span>
                                            @else
                                                <span class="badge-custom">Custom</span>
                                            @endif
                                        </td>
                                        <td>{{ $dateOvr->is_holiday ? '-' : substr($dateOvr->jam_masuk, 0, 5) }}</td>
                                        <td>{{ $dateOvr->is_holiday ? '-' : substr($dateOvr->batas_keterlambatan, 0, 5) }}</td>
                                        <td>{{ $dateOvr->is_holiday ? '-' : substr($dateOvr->jam_pulang, 0, 5) }}</td>
                                        <td>{{ $dateOvr->keterangan ?? '-' }}</td>
                                        <td style="text-align: center;">
                                            <div style="display: flex; gap: 6px; justify-content: center;">
                                                <button type="button" class="btn-date-edit" data-id="{{ $dateOvr->id }}" data-date="{{ $dateOvr->specific_date->format('Y-m-d') }}" data-jam-masuk="{{ $dateOvr->jam_masuk ? substr($dateOvr->jam_masuk, 0, 5) : '' }}" data-batas="{{ $dateOvr->batas_keterlambatan ? substr($dateOvr->batas_keterlambatan, 0, 5) : '' }}" data-jam-pulang="{{ $dateOvr->jam_pulang ? substr($dateOvr->jam_pulang, 0, 5) : '' }}" data-is-holiday="{{ $dateOvr->is_holiday ? '1' : '0' }}" data-keterangan="{{ $dateOvr->keterangan ?? '' }}">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    Edit
                                                </button>
                                                <form action="{{ route('super-admin.schedules.destroy', $dateOvr->id) }}" method="POST" class="inline-form" onsubmit="return handleDateDelete(event)">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-date-delete">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="table-date-pagination" class="table-pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding: 0 4px;">
                        <span class="pagination-info" id="date-pagination-info" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">
                            Menampilkan 1 - 5 dari X data
                        </span>
                        <div class="pagination-buttons" style="display: flex; gap: 8px;">
                            <button type="button" id="btn-date-prev" class="btn-secondary" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                                Sebelumnya
                            </button>
                            <button type="button" id="btn-date-next" class="btn-secondary" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                Berikutnya
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </button>
                        </div>
                    </div>
                @else
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                        <svg width="40" height="40" viewBox="0 0 20 20" fill="currentColor" style="opacity: 0.3; margin-bottom: 12px;">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <p>Belum ada tanggal khusus atau hari libur yang ditambahkan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal: Add/Edit Date Override --}}
    <div class="modal-backdrop" id="date-override-modal" style="display: none;">
        <div class="modal-container" style="max-width: 480px;">
            <div class="modal-header">
                <h3 class="modal-title" id="date-modal-title">Tambah Tanggal Khusus</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('date-override-modal').style.display='none'">&times;</button>
            </div>
            <form id="date-override-form" method="POST" class="modal-form">
                @csrf
                <input type="hidden" name="_method" value="POST" id="date-form-method">
                <input type="hidden" name="type" value="date">

                <div class="modal-body">
                    <div class="form-group" style="margin-bottom: 14px;" id="date-form-date-group">
                        <label>Pilih Tanggal Khusus</label>
                        <input type="date" name="specific_date" id="date-form-date" required>
                    </div>

                    <div class="holiday-switch-container">
                        <span class="holiday-switch-label">Tandai sebagai Hari Libur</span>
                        <label class="switch-toggle">
                            <input type="checkbox" name="is_holiday" id="date-form-holiday" value="1" onchange="toggleDateTimeInputs()">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div id="date-time-inputs">
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Jam Masuk Kerja</label>
                            <input type="text" class="time-picker" name="jam_masuk" id="date-form-masuk">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Batas Absensi (Waktu Terakhir Absen Masuk)</label>
                            <input type="text" class="time-picker" name="batas_keterlambatan" id="date-form-batas">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Jam Pulang Kerja</label>
                            <input type="text" class="time-picker" name="jam_pulang" id="date-form-pulang">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label>Keterangan Hari Libur/Acara (opsional)</label>
                        <input type="text" name="keterangan" id="date-form-keterangan" placeholder="Contoh: Hari Raya Idul Fitri, Cuti Bersama" maxlength="170">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('date-override-modal').style.display='none'" style="padding: 10px 20px; border-radius: 8px; cursor: pointer;">Batal</button>
                    <button type="submit" class="btn-primary" style="padding: 10px 20px; border-radius: 8px; cursor: pointer;">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Holiday Info API --}}
    <div class="modal-backdrop" id="holiday-info-modal" style="display: none;">
        <div class="modal-container" style="max-width: 520px;">
            <div class="modal-header">
                <h3 class="modal-title">Informasi Sumber Data Libur Nasional</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('holiday-info-modal').style.display='none'">&times;</button>
            </div>
            <div class="modal-body" style="font-size: 0.95rem; line-height: 1.6; color: var(--text-primary);">
                <p style="margin-bottom: 12px;">
                    Data hari libur nasional disinkronkan secara otomatis menggunakan layanan API publik pihak ketiga:
                </p>
                <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass-border); padding: 12px; border-radius: 8px; font-family: monospace; font-size: 0.9rem; margin-bottom: 16px; word-break: break-all; color: var(--accent-primary);">
                    https://api-hari-libur.vercel.app
                </div>
                <p style="margin-bottom: 12px;">
                    <strong>Cara Kerja:</strong>
                </p>
                <ul style="margin-left: 20px; margin-bottom: 16px; list-style-type: disc;">
                    <li style="margin-bottom: 6px;">Sistem mengirimkan permintaan data berdasarkan tahun berjalan dan tahun berikutnya.</li>
                    <li style="margin-bottom: 6px;">Daftar tanggal merah beserta keterangannya (seperti Tahun Baru, Idul Fitri, dll.) akan diunduh dan disimpan otomatis ke tabel database jadwal kerja.</li>
                    <li style="margin-bottom: 6px;">Setelah tersimpan, absensi pada tanggal-tanggal tersebut otomatis akan ditutup untuk peserta.</li>
                </ul>
                <p style="margin-bottom: 0; font-size: 0.85rem; color: var(--text-secondary); font-style: italic;">
                    Catatan: Proses sinkronisasi otomatis berjalan di latar belakang saat Anda membuka halaman pengaturan ini. Anda juga dapat menambahkan hari libur kustom secara manual.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary" onclick="document.getElementById('holiday-info-modal').style.display='none'" style="padding: 10px 20px; border-radius: 8px; cursor: pointer;">Tutup</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <div id="settings-config" style="display:none;"
         data-global-default-settings='{"jam_masuk":"{{ substr($settings->jam_masuk, 0, 5) }}","batas_keterlambatan":"{{ substr($settings->batas_keterlambatan, 0, 5) }}","jam_pulang":"{{ substr($settings->jam_pulang, 0, 5) }}"}'
         data-day-overrides='@json($dayOverrides->values())'
         data-date-overrides='@json($dateOverrides)'
         data-routes-store-schedule="{{ route('super-admin.schedules.store') }}"
         data-routes-sync-holidays="{{ route('super-admin.schedules.sync-holidays') }}"
         data-csrf-token="{{ csrf_token() }}">
    </div>
    <script>
        function toggleDateTimeInputs() {
            const isHoliday = document.getElementById('date-form-holiday').checked;
            const timeInputs = document.getElementById('date-time-inputs');
            if (isHoliday) {
                timeInputs.style.display = 'none';
                document.getElementById('date-form-masuk').required = false;
                document.getElementById('date-form-batas').required = false;
                document.getElementById('date-form-pulang').required = false;
            } else {
                timeInputs.style.display = 'block';
                document.getElementById('date-form-masuk').required = true;
                document.getElementById('date-form-batas').required = true;
                document.getElementById('date-form-pulang').required = true;
            }
        }

        function handleDateDelete(event) {
            if (window.confirm('Apakah Anda yakin ingin menghapus jadwal khusus tanggal ini?')) {
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Menghapus Tanggal Khusus...',
                        text: 'Harap tunggu sebentar.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            window.Swal.showLoading();
                        }
                    });
                }
                return true;
            }
            event.preventDefault();
            return false;
        }

        function handleSyncHolidaysSubmit(event) {
            const form = event.target;
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="animation: spin 1s linear infinite; margin-right: 4px;">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"></circle>
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke-linecap="round"></path>
                    </svg>
                    Sinkronisasi...
                `;
            }
            if (window.Swal) {
                window.Swal.fire({
                    title: 'Menyinkronkan Hari Libur...',
                    text: 'Sedang mengambil data terbaru dari API. Proses ini memakan waktu beberapa detik, mohon jangan tutup halaman ini.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        window.Swal.showLoading();
                    }
                });
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form.modal-form').forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        return;
                    }
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                    }
                    if (window.Swal) {
                        window.Swal.fire({
                            title: 'Menyimpan Perubahan...',
                            text: 'Sedang menyimpan data pengaturan baru ke database. Harap tunggu sebentar.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                window.Swal.showLoading();
                            }
                        });
                    }
                });
            });
        });
    </script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
    @vite('resources/js/super_admin/settings.js')
@endpush
