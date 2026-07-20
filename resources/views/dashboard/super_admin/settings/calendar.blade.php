@extends('dashboard.layout')

@section('title', 'Kalender Jadwal - Pengaturan')
@section('header_title', 'Kalender Jadwal Kerja')

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
            {{-- Tab 1: Kalender Jadwal (Interactive Monthly Calendar) --}}
            <div class="tab-content active" id="tab-calendar">
                <p style="color: var(--text-secondary); margin-bottom: 16px; font-size: 0.9rem;">
                    Klik pada tanggal di kalender untuk menambahkan atau mengedit jadwal khusus/hari libur pada tanggal tersebut.
                </p>
                <div class="calendar-wrapper">
                    <div class="calendar-header-bar">
                        <button type="button" id="calendar-prev-month" class="btn-secondary" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <h3 id="calendar-month-year-label" class="calendar-title-text">Juli 2026</h3>
                        <button type="button" id="calendar-next-month" class="btn-secondary" style="padding: 6px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                    <div class="calendar-grid">
                        <div class="calendar-day-label">Min</div>
                        <div class="calendar-day-label">Sen</div>
                        <div class="calendar-day-label">Sel</div>
                        <div class="calendar-day-label">Rab</div>
                        <div class="calendar-day-label">Kam</div>
                        <div class="calendar-day-label">Jum</div>
                        <div class="calendar-day-label">Sab</div>
                        
                        <div id="calendar-days-container" class="calendar-days-grid"></div>
                    </div>
                    
                    <div class="calendar-legend">
                        <span class="legend-item"><span class="legend-dot dot-default"></span> Jadwal Default</span>
                        <span class="legend-item"><span class="legend-dot dot-day"></span> Custom Hari (Mingguan)</span>
                        <span class="legend-item"><span class="legend-dot dot-date"></span> Custom Tanggal (Khusus)</span>
                        <span class="legend-item"><span class="legend-dot dot-holiday"></span> Hari Libur / Tanggal Merah</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Add/Edit Date Override (Triggered from Calendar clicks) --}}
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
        // Toggle time inputs helper function inside view so it is available to the layout / script
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
