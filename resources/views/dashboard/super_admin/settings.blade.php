@extends('dashboard.layout')

@section('title', 'Pengaturan Aplikasi')
@section('header_title', 'Pengaturan Parameter Global')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @vite('resources/css/super_admin/settings.css')
@endpush

@section('content')
    <div class="settings-container">
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif

        {{-- ===== Card: Jadwal Kehadiran (3-Tab System) ===== --}}
        <div class="content-card" style="margin-bottom: 24px;">
            <div class="card-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 20px;">
                <h2 class="card-title" style="display: flex; align-items: center; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="color: var(--accent-primary);">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    Jadwal & Waktu Kehadiran
                </h2>
            </div>

            {{-- Tab Navigation --}}
            <div class="schedule-tabs">
                <button type="button" class="schedule-tab active" data-tab="tab-calendar">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                    Kalender Jadwal
                </button>
                <button type="button" class="schedule-tab" data-tab="tab-default">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    Jadwal Keseluruhan
                </button>
                <button type="button" class="schedule-tab" data-tab="tab-day">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                    Jadwal per Hari
                </button>
                <button type="button" class="schedule-tab" data-tab="tab-date">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/></svg>
                    Tanggal Khusus / Libur
                </button>
            </div>

            {{-- ===== Tab 1: Kalender Jadwal (Interactive Monthly Calendar) ===== --}}
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

            {{-- ===== Tab 2: Default Schedule ===== --}}
            <div class="tab-content" id="tab-default">
                <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 0.9rem;">
                    Jadwal default berlaku untuk semua hari kecuali yang di-override di tab "Jadwal per Hari" atau "Tanggal Khusus".
                </p>
                <form action="{{ route('super-admin.settings.update') }}" method="POST" class="modal-form">
                    @csrf
                    @method('PUT')

                    <div class="time-inputs-grid">
                        <div class="form-group">
                            <label>Jam Masuk</label>
                            <div class="input-with-icon">
                                <input type="time" name="jam_masuk" value="{{ old('jam_masuk', substr($settings->jam_masuk, 0, 5)) }}" required>
                                <span class="input-icon-right"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Batas Terlambat</label>
                            <div class="input-with-icon">
                                <input type="time" name="batas_keterlambatan" value="{{ old('batas_keterlambatan', substr($settings->batas_keterlambatan, 0, 5)) }}" required>
                                <span class="input-icon-right"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Jam Pulang</label>
                            <div class="input-with-icon">
                                <input type="time" name="jam_pulang" value="{{ old('jam_pulang', substr($settings->jam_pulang, 0, 5)) }}" required>
                                <span class="input-icon-right"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden geofencing fields so the default form still works --}}
                    <input type="hidden" name="latitude_kantor" value="{{ $settings->latitude_kantor }}">
                    <input type="hidden" name="longitude_kantor" value="{{ $settings->longitude_kantor }}">
                    <input type="hidden" name="radius_meter" value="{{ $settings->radius_meter }}">

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Simpan Default
                        </button>
                    </div>
                </form>
            </div>

            {{-- ===== Tab 2: Per-Day Schedule ===== --}}
            <div class="tab-content" id="tab-day">
                <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 0.9rem;">
                    Atur jadwal berbeda untuk hari tertentu dalam seminggu. Jika tidak di-override, jadwal default akan digunakan.
                </p>

                <div class="day-grid">
                    @php
                        $dayLabels = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    @endphp
                    @for($d = 1; $d <= 6; $d++)
                        @php
                            $dow = $d % 7; // 1=Senin..6=Sabtu, 0=Minggu
                        @endphp
                        @if($d == 6)
                            @php $dow = 6; @endphp
                        @endif
                        @php
                            // Reorder: Senin(1), Selasa(2), Rabu(3), Kamis(4), Jumat(5), Sabtu(6), Minggu(0)
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
                            <div class="day-card-actions">
                                <button type="button" class="btn-day-edit" data-day="{{ $dayIndex }}" data-day-name="{{ $dayLabels[$dayIndex] }}" data-override-id="{{ $override->id ?? '' }}" data-jam-masuk="{{ $override ? substr($override->jam_masuk ?? '', 0, 5) : substr($settings->jam_masuk, 0, 5) }}" data-batas="{{ $override ? substr($override->batas_keterlambatan ?? '', 0, 5) : substr($settings->batas_keterlambatan, 0, 5) }}" data-jam-pulang="{{ $override ? substr($override->jam_pulang ?? '', 0, 5) : substr($settings->jam_pulang, 0, 5) }}" data-is-holiday="{{ $override && $override->is_holiday ? '1' : '0' }}" data-keterangan="{{ $override->keterangan ?? '' }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                @if($override)
                                    <form action="{{ route('super-admin.schedules.destroy', $override->id) }}" method="POST" class="inline-form" onsubmit="return handleDayDelete(event)">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-day-delete" title="Kembalikan ke default">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Reset
                                        </button>
                                    </form>
                                @endif
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
                        <div class="day-card-actions">
                            <button type="button" class="btn-day-edit" data-day="0" data-day-name="Minggu" data-override-id="{{ $override->id ?? '' }}" data-jam-masuk="{{ $override ? substr($override->jam_masuk ?? '', 0, 5) : substr($settings->jam_masuk, 0, 5) }}" data-batas="{{ $override ? substr($override->batas_keterlambatan ?? '', 0, 5) : substr($settings->batas_keterlambatan, 0, 5) }}" data-jam-pulang="{{ $override ? substr($override->jam_pulang ?? '', 0, 5) : substr($settings->jam_pulang, 0, 5) }}" data-is-holiday="{{ $override && $override->is_holiday ? '1' : '0' }}" data-keterangan="{{ $override->keterangan ?? '' }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            @if($override)
                                <form action="{{ route('super-admin.schedules.destroy', $override->id) }}" method="POST" class="inline-form" onsubmit="return handleDayDelete(event)">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-day-delete" title="Kembalikan ke default">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        Reset
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Tab 3: Specific Dates / Holidays ===== --}}
            <div class="tab-content" id="tab-date">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">
                        Atur jadwal khusus untuk tanggal tertentu atau tandai sebagai hari libur nasional.
                    </p>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
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
                                    <th>Terlambat</th>
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

        {{-- ===== Card: Geofencing (Separate Form) ===== --}}
        <form action="{{ route('super-admin.settings.update') }}" method="POST" class="modal-form">
            @csrf
            @method('PUT')

            {{-- Hidden time fields so this form still works --}}
            <input type="hidden" name="jam_masuk" value="{{ substr($settings->jam_masuk, 0, 5) }}">
            <input type="hidden" name="jam_pulang" value="{{ substr($settings->jam_pulang, 0, 5) }}">
            <input type="hidden" name="batas_keterlambatan" value="{{ substr($settings->batas_keterlambatan, 0, 5) }}">

            <div class="content-card">
                <div class="card-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 20px;">
                    <h2 class="card-title" style="display: flex; align-items: center; gap: 10px;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="color: var(--accent-primary);">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        Parameter Lokasi & Geofencing
                    </h2>
                </div>

                <!-- Map Search & Geolocation Bar -->
                <div class="map-search-bar" style="display: flex; gap: 8px; margin-bottom: 12px;">
                    <input type="text" id="map-search-input" placeholder="Cari nama lokasi atau alamat kantor..." style="flex: 1; font-size: 14px;">
                    <button type="button" id="btn-map-search" class="btn-secondary" style="padding: 10px 16px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        Cari
                    </button>
                    <button type="button" id="btn-map-gps" class="btn-primary" style="padding: 10px 16px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;" title="Gunakan Lokasi Saya Saat Ini">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                        </svg>
                        GPS
                    </button>
                </div>

                <!-- Map Container -->
                <div id="map"></div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="latitude_kantor">Latitude Kantor</label>
                    <input type="text" id="latitude_kantor" name="latitude_kantor" value="{{ old('latitude_kantor', $settings->latitude_kantor) }}" required readonly style="cursor: not-allowed;">
                    <small style="display: block; color: var(--text-secondary); margin-top: 4px;">Terisi otomatis dari penanda peta di atas.</small>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="longitude_kantor">Longitude Kantor</label>
                    <input type="text" id="longitude_kantor" name="longitude_kantor" value="{{ old('longitude_kantor', $settings->longitude_kantor) }}" required readonly style="cursor: not-allowed;">
                    <small style="display: block; color: var(--text-secondary); margin-top: 4px;">Terisi otomatis dari penanda peta di atas.</small>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="radius_meter">Radius Batas Absensi (Meter)</label>
                    <div class="input-with-icon">
                        <input type="number" id="radius_meter" name="radius_meter" min="1" value="{{ old('radius_meter', $settings->radius_meter) }}" required>
                        <span class="input-icon-right"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                    </div>
                    <small style="display: block; color: var(--text-secondary); margin-top: 4px;">Batas jarak maksimal (dalam meter) peserta boleh melakukan absen dari titik koordinat kantor.</small>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 28px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Simpan Geofencing
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ===== Modal: Edit Day Override ===== --}}
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
                            <input type="time" name="jam_masuk" id="day-form-masuk">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Batas Toleransi Keterlambatan</label>
                            <input type="time" name="batas_keterlambatan" id="day-form-batas">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Jam Pulang Kerja</label>
                            <input type="time" name="jam_pulang" id="day-form-pulang">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label>Keterangan Acara/Alasan (opsional)</label>
                        <input type="text" name="keterangan" id="day-form-keterangan" placeholder="Contoh: Jumat Berkah, Jam Kerja Pendek">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('day-override-modal').style.display='none'" style="padding: 10px 20px; border-radius: 8px; cursor: pointer;">Batal</button>
                    <button type="submit" class="btn-primary" style="padding: 10px 20px; border-radius: 8px; cursor: pointer;">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Modal: Add/Edit Date Override ===== --}}
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
                            <input type="time" name="jam_masuk" id="date-form-masuk">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Batas Toleransi Keterlambatan</label>
                            <input type="time" name="batas_keterlambatan" id="date-form-batas">
                        </div>
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label>Jam Pulang Kerja</label>
                            <input type="time" name="jam_pulang" id="date-form-pulang">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label>Keterangan Hari Libur/Acara (opsional)</label>
                        <input type="text" name="keterangan" id="date-form-keterangan" placeholder="Contoh: Hari Raya Idul Fitri, Cuti Bersama">
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @vite('resources/js/super_admin/settings.js')
@endpush
