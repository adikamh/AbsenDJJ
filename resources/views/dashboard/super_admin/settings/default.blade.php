@extends('dashboard.layout')

@section('title', 'Jadwal & Kehadiran - Pengaturan')
@section('header_title', 'Jadwal & Kehadiran')

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

        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 20px;">
                <h2 class="card-title" style="display: flex; align-items: center; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="color: var(--accent-primary);">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    Jadwal & Waktu Kehadiran
                </h2>
            </div>
            <div class="tab-content active" id="tab-default">
                <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 0.9rem;">
                    Jadwal default berlaku untuk semua hari kecuali yang di-override di tab "Jadwal per Hari" atau "Tanggal Khusus".
                </p>
                <form action="{{ route('super-admin.settings.update-default') }}" method="POST" class="modal-form">
                    @csrf
                    @method('PUT')

                    <div class="time-inputs-grid">
                        <div class="form-group">
                            <label>Jam Masuk</label>
                            <div class="input-with-icon">
                                <input type="text" class="time-picker" name="jam_masuk" value="{{ old('jam_masuk', substr($settings->jam_masuk, 0, 5)) }}" required>
                                <span class="input-icon-right"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Batas Absensi (Waktu Terakhir Absen Masuk)</label>
                            <div class="input-with-icon">
                                <input type="text" class="time-picker" name="batas_keterlambatan" value="{{ old('batas_keterlambatan', substr($settings->batas_keterlambatan, 0, 5)) }}" required>
                                <span class="input-icon-right"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Jam Pulang</label>
                            <div class="input-with-icon">
                                <input type="text" class="time-picker" name="jam_pulang" value="{{ old('jam_pulang', substr($settings->jam_pulang, 0, 5)) }}" required>
                                <span class="input-icon-right"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Simpan Default
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Section 2: Jadwal Harian Khusus (Day Overrides) --}}
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 20px;">
                <h2 class="card-title" style="display: flex; align-items: center; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="color: var(--accent-primary);">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    Jadwal Harian Khusus
                </h2>
            </div>
            <div class="tab-content active">
                <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 0.9rem;">
                    Atur jadwal berbeda untuk hari tertentu dalam seminggu. Jika tidak di-override, jadwal default di atas akan digunakan.
                </p>

                <div class="day-grid">
                    @php
                        $dayLabels = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    @endphp
                    @for($d = 1; $d <= 6; $d++)
                        @php
                            $dayIndex = $d < 7 ? $d : 0;
                            $override = $dayOverrides[$dayIndex] ?? null;
                        @endphp
                        <div class="day-card {{ $override ? ($override->is_holiday ? 'day-holiday' : 'day-custom') : '' }}">
                            <div class="day-card-header">
                                <span class="day-name">{{ $dayLabels[$dayIndex] }}</span>
                                @if($override)
                                    @if($override->is_holiday)
                                        <span class="badge-holiday">Libur</span>
                                    @else
                                        <span class="badge-custom">Custom</span>
                                    @endif
                                @else
                                    <span class="badge-default">Default</span>
                                @endif
                            </div>
                            <div class="day-card-body">
                                @if($override && $override->is_holiday)
                                    <p style="color: var(--text-muted); font-style: italic; margin: 0;">{{ $override->keterangan ?? 'Hari Libur' }}</p>
                                @elseif($override)
                                    <div class="day-times">
                                        <span>Masuk: <strong>{{ substr($override->jam_masuk, 0, 5) }}</strong></span>
                                        <span>Terlambat: <strong>{{ substr($override->batas_keterlambatan, 0, 5) }}</strong></span>
                                        <span>Pulang: <strong>{{ substr($override->jam_pulang, 0, 5) }}</strong></span>
                                    </div>
                                @else
                                    <div class="day-times">
                                        <span>Masuk: <strong>{{ substr($settings->jam_masuk, 0, 5) }}</strong></span>
                                        <span>Terlambat: <strong>{{ substr($settings->batas_keterlambatan, 0, 5) }}</strong></span>
                                        <span>Pulang: <strong>{{ substr($settings->jam_pulang, 0, 5) }}</strong></span>
                                    </div>
                                @endif
                            </div>
                            <div class="day-card-actions" style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 8px;">
                                <div class="left-actions" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                    <button type="button" class="btn-day-edit" data-day="{{ $dayIndex }}" data-day-name="{{ $dayLabels[$dayIndex] }}" data-override-id="{{ $override->id ?? '' }}" data-jam-masuk="{{ $override ? substr($override->jam_masuk ?? '', 0, 5) : substr($settings->jam_masuk, 0, 5) }}" data-batas="{{ $override ? substr($override->batas_keterlambatan ?? '', 0, 5) : substr($settings->batas_keterlambatan, 0, 5) }}" data-jam-pulang="{{ $override ? substr($override->jam_pulang ?? '', 0, 5) : substr($settings->jam_pulang, 0, 5) }}" data-is-holiday="{{ $override && $override->is_holiday ? '1' : '0' }}" data-keterangan="{{ $override->keterangan ?? '' }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    @if($override && !$override->is_holiday)
                                        <form action="{{ route('super-admin.schedules.destroy', $override->id) }}" method="POST" class="inline-form" onsubmit="return handleDayDelete(event)">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-day-delete" title="Kembalikan to default">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                Reset
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <label class="switch-toggle card-holiday-toggle" title="Tandai sebagai Hari Libur" style="margin-left: auto; flex-shrink: 0;">
                                    <input type="checkbox" class="js-card-holiday-toggle" 
                                           data-day="{{ $dayIndex }}" 
                                           data-day-name="{{ $dayLabels[$dayIndex] }}"
                                           data-override-id="{{ $override->id ?? '' }}"
                                           data-keterangan="{{ $override->keterangan ?? '' }}"
                                           {{ $override && $override->is_holiday ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                </label>
                            </div>
                        </div>
                    @endfor
                    {{-- Sunday --}}
                    @php
                        $override = $dayOverrides[0] ?? null;
                    @endphp
                    <div class="day-card {{ $override ? ($override->is_holiday ? 'day-holiday' : 'day-custom') : '' }}">
                        <div class="day-card-header">
                            <span class="day-name">Minggu</span>
                            @if($override)
                                @if($override->is_holiday)
                                    <span class="badge-holiday">Libur</span>
                                @else
                                    <span class="badge-custom">Custom</span>
                                @endif
                            @else
                                <span class="badge-default">Default</span>
                            @endif
                        </div>
                        <div class="day-card-body">
                            @if($override && $override->is_holiday)
                                <p style="color: var(--text-muted); font-style: italic; margin: 0;">{{ $override->keterangan ?? 'Hari Libur' }}</p>
                            @elseif($override)
                                <div class="day-times">
                                    <span>Masuk: <strong>{{ substr($override->jam_masuk, 0, 5) }}</strong></span>
                                    <span>Terlambat: <strong>{{ substr($override->batas_keterlambatan, 0, 5) }}</strong></span>
                                    <span>Pulang: <strong>{{ substr($override->jam_pulang, 0, 5) }}</strong></span>
                                </div>
                            @else
                                <div class="day-times">
                                    <span>Masuk: <strong>{{ substr($settings->jam_masuk, 0, 5) }}</strong></span>
                                    <span>Terlambat: <strong>{{ substr($settings->batas_keterlambatan, 0, 5) }}</strong></span>
                                    <span>Pulang: <strong>{{ substr($settings->jam_pulang, 0, 5) }}</strong></span>
                                </div>
                            @endif
                        </div>
                        <div class="day-card-actions" style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 8px;">
                            <div class="left-actions" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                <button type="button" class="btn-day-edit" data-day="0" data-day-name="Minggu" data-override-id="{{ $override->id ?? '' }}" data-jam-masuk="{{ $override ? substr($override->jam_masuk ?? '', 0, 5) : substr($settings->jam_masuk, 0, 5) }}" data-batas="{{ $override ? substr($override->batas_keterlambatan ?? '', 0, 5) : substr($settings->batas_keterlambatan, 0, 5) }}" data-jam-pulang="{{ $override ? substr($override->jam_pulang ?? '', 0, 5) : substr($settings->jam_pulang, 0, 5) }}" data-is-holiday="{{ $override && $override->is_holiday ? '1' : '0' }}" data-keterangan="{{ $override->keterangan ?? '' }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                @if($override && !$override->is_holiday)
                                    <form action="{{ route('super-admin.schedules.destroy', $override->id) }}" method="POST" class="inline-form" onsubmit="return handleDayDelete(event)">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-day-delete" title="Kembalikan to default">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Reset
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <label class="switch-toggle card-holiday-toggle" title="Tandai sebagai Hari Libur" style="margin-left: auto; flex-shrink: 0;">
                                <input type="checkbox" class="js-card-holiday-toggle" 
                                       data-day="0" 
                                       data-day-name="Minggu"
                                       data-override-id="{{ $override->id ?? '' }}"
                                       data-keterangan="{{ $override->keterangan ?? '' }}"
                                       {{ $override && $override->is_holiday ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Edit Day Override --}}
    <div class="modal-backdrop" id="day-override-modal" style="display: none;">
        <div class="modal-container" style="max-width: 480px;">
            <div class="modal-header">
                <h3 class="modal-title" id="day-modal-title">Edit Jadwal Hari</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('day-override-modal').style.display='none'">&times;</button>
            </div>
            <form id="day-override-form" method="POST" class="modal-form">
                @csrf
                <input type="hidden" name="_method" value="POST" id="day-form-method">
                <input type="hidden" name="type" value="day">
                <input type="hidden" name="day_of_week" id="day-form-day">

                <div class="modal-body">
                    <div class="holiday-switch-container">
                        <span class="holiday-switch-label">Tandai sebagai Hari Libur</span>
                        <label class="switch-toggle">
                            <input type="checkbox" name="is_holiday" id="day-form-holiday" value="1" onchange="toggleDayTimeInputs()">
                            <span class="switch-slider"></span>
                        </label>
                    </div>

                    <div id="day-time-inputs">
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Jam Masuk Kerja</label>
                            <input type="text" class="time-picker" name="jam_masuk" id="day-form-masuk">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Batas Absensi (Waktu Terakhir Absen Masuk)</label>
                            <input type="text" class="time-picker" name="batas_keterlambatan" id="day-form-batas">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Jam Pulang Kerja</label>
                            <input type="text" class="time-picker" name="jam_pulang" id="day-form-pulang">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label>Keterangan Acara/Alasan (opsional)</label>
                        <input type="text" name="keterangan" id="day-form-keterangan" placeholder="Contoh: Jumat Berkah, Jam Kerja Pendek" maxlength="170">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('day-override-modal').style.display='none'" style="padding: 10px 20px; border-radius: 8px; cursor: pointer;">Batal</button>
                    <button type="submit" class="btn-primary" style="padding: 10px 20px; border-radius: 8px; cursor: pointer;">Simpan Jadwal</button>
                </div>
            </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form.modal-form').forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        return;
                    }
                    
                    const isHolidayCheckbox = form.querySelector('input[name="is_holiday"]');
                    const isHolidayChecked = isHolidayCheckbox ? isHolidayCheckbox.checked : false;
                    
                    if (isHolidayChecked) {
                        event.preventDefault();
                        
                        if (window.Swal) {
                            window.Swal.fire({
                                title: 'Konfirmasi Libur',
                                text: 'Apakah Anda yakin ingin menetapkan hari ini sebagai hari libur? Seluruh absensi peserta pada hari ini akan dikunci.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                cancelButtonColor: '#6b7280',
                                confirmButtonText: 'Ya, Tetapkan Libur',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const btn = form.querySelector('button[type="submit"]');
                                    if (btn) btn.disabled = true;
                                    
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
                                    form.submit();
                                }
                            });
                        } else {
                            if (confirm('Apakah Anda yakin ingin menetapkan hari ini sebagai hari libur?')) {
                                const btn = form.querySelector('button[type="submit"]');
                                if (btn) btn.disabled = true;
                                form.submit();
                            }
                        }
                    } else {
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
                    }
                });
            });
        });
    </script>
    @vite('resources/js/super_admin/settings.js')
@endpush
