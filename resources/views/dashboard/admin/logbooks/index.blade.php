@extends('dashboard.layout')

@section('title', 'Logbook Anak Didik')
@section('header_title', 'Logbook Kegiatan Anak Didik')

@push('styles')
    @vite('resources/css/admin/interns.css')
@endpush

@section('content')
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Logbook Pending</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #fbbf24; margin-top: 6px;">{{ $pendingLogbooksCount }} Item</div>
        </div>
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Logbook Disetujui</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #34d399; margin-top: 6px;">{{ $approvedLogbooksCount }} Item</div>
        </div>
        <div class="stat-card hover-lift" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; backdrop-filter: blur(10px);">
            <div class="stat-label" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 500;">Logbook Ditolak</div>
            <div class="stat-value" style="font-size: 1.6rem; font-weight: 700; color: #f87171; margin-top: 6px;">{{ $rejectedLogbooksCount }} Item</div>
        </div>
    </div>

    <style>
        .switch-toggle-label input:checked + .switch-toggle-slider {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
        }
        .switch-toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        #global-auto-acc + .switch-toggle-slider:before {
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
        }
        .switch-toggle-label input:checked + .switch-toggle-slider:before {
            transform: translateX(24px);
        }
        
        /* Small Switch Toggle for Individual Intern Toggles */
        .switch-small {
            position: relative;
            display: inline-block;
            width: 38px;
            height: 20px;
        }
        .switch-small input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider-small {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255,255,255,0.1);
            border: 1px solid var(--glass-border);
            transition: .3s;
            border-radius: 20px;
        }
        .slider-small:before {
            position: absolute;
            content: "";
            height: 14px;
            width: 14px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .switch-small input:checked + .slider-small {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
        }
        .switch-small input:checked + .slider-small:before {
            transform: translateX(18px);
        }

        /* Responsive Intern Toggle Card styling */
        .intern-toggle-item {
            display: none;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 12px 16px;
            transition: background 0.2s;
            gap: 12px;
        }
        .intern-toggle-item:hover {
            background: rgba(255, 255, 255, 0.04);
        }
        .intern-info {
            flex: 1;
            min-width: 100px;
            text-align: left;
        }
        .intern-switches {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
            flex-shrink: 0;
        }
        .intern-list-scrollable {
            max-height: 280px;
            overflow-y: auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 15px;
            padding-right: 5px;
            box-sizing: border-box;
            margin-top: 5px;
        }
        @media (max-width: 480px) {
            .intern-list-scrollable {
                grid-template-columns: 1fr;
            }
        }
        .intern-list-scrollable::-webkit-scrollbar {
            width: 6px;
        }
        .intern-list-scrollable::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }
        .intern-list-scrollable::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .intern-list-scrollable::-webkit-scrollbar-thumb:hover {
            background: var(--accent-primary);
        }
        
        /* Custom Content Card for isolating desktop layout and preventing overflow clipping */
        .custom-content-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 30px;
            box-shadow: var(--card-shadow);
            min-width: 0;
            overflow: visible !important;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        @media (max-width: 1024px) {
            .custom-content-card {
                padding: 16px !important;
                border-radius: 16px !important;
            }
        }
    </style>

    <!-- Auto-Approve Settings Card -->
    <div class="custom-content-card" style="margin-bottom: 30px;">
        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: flex-start !important; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            <h2 class="card-title" style="margin: 0; font-size: 1.1rem; color: var(--text-primary); text-align: left;">Pengaturan Aktivitas & Kehadiran Anak Didik</h2>
        </div>
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 15px;">
            
            <!-- Global Toggles Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                <!-- 1. Auto Approve Global -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 16px 20px;">
                    <div>
                        <strong style="color: var(--text-primary); display: block; font-size: 0.95rem;">Persetujuan Otomatis (Global)</strong>
                        <span style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-top: 4px;">
                            Setujui otomatis semua logbook baru secara instan.
                        </span>
                    </div>
                    <div>
                        <label class="switch-toggle-label" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                            <input type="checkbox" id="global-auto-acc" {{ auth()->user()->auto_approve_logbook_global ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                            <span class="switch-toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); border: 1px solid var(--glass-border); transition: .3s; border-radius: 34px;"></span>
                        </label>
                    </div>
                </div>

                <!-- 2. Wajib Foto Absensi Global -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 16px 20px;">
                    <div>
                        <strong style="color: var(--text-primary); display: block; font-size: 0.95rem;">Wajib Foto Absensi (Global)</strong>
                        <span style="color: var(--text-secondary); font-size: 0.8rem; display: block; margin-top: 4px;">
                            Minta foto selfie saat absen masuk & pulang.
                        </span>
                    </div>
                    <div>
                        <label class="switch-toggle-label" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                            <input type="checkbox" id="global-photo-req" {{ auth()->user()->require_photo_attendance_global ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                            <span class="switch-toggle-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); border: 1px solid var(--glass-border); transition: .3s; border-radius: 34px;"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Collapsible Trigger -->
            <div style="text-align: center; margin-top: 5px;">
                <button type="button" id="toggle-individual-btn" style="background: none; border: none; color: var(--accent-primary); font-size: 0.88rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; outline: none; transition: color 0.2s;" onmouseover="this.style.color='var(--text-primary)';" onmouseout="this.style.color='var(--accent-primary)';">
                    <span>Pengaturan Khusus Per-Anak Didik ({{ $activeGuidedInterns->count() }} Orang Aktif)</span>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
            </div>

            <!-- Individual Toggles Wrapper (Collapsible) -->
            <div id="individual-toggles-wrapper" style="max-height: 0; overflow: hidden; transition: max-height 0.4s ease-out, opacity 0.3s ease; opacity: 0;">
                <div id="individual-toggles-section" style="margin-top: 15px; display: flex; flex-direction: column; gap: 12px; transition: opacity 0.3s ease;">
                    
                    <!-- Search Input -->
                    <div style="position: relative; width: 100%;">
                        <input type="text" id="search-intern-toggle" placeholder="Cari nama atau NIP/NIM anak didik..." style="width: 100%; padding: 10px 14px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(0,0,0,0.15); color: var(--text-primary); outline: none; box-sizing: border-box; transition: all 0.3s;">
                    </div>

                    <!-- Scrollable Container -->
                    <div class="intern-list-scrollable">
                        <div id="search-placeholder-text" style="color: var(--text-secondary); font-size: 0.88rem; text-align: center; padding: 30px 0; grid-column: 1 / -1; width: 100%;">Ketik nama atau NIP/NIM untuk mencari anak didik...</div>
                        <div id="search-no-match-text" style="color: var(--text-secondary); font-size: 0.88rem; text-align: center; padding: 30px 0; display: none; grid-column: 1 / -1; width: 100%;">Tidak ada anak didik yang cocok dengan pencarian Anda.</div>

                        @foreach($activeGuidedInterns as $intern)
                            <div class="intern-toggle-item" data-name="{{ strtolower($intern->nama_lengkap) }}" data-nip="{{ strtolower($intern->nip ?? '') }}">
                                <div class="intern-info">
                                    <strong style="color: var(--text-primary); display: block; font-size: 0.9rem;">
                                        {{ $intern->nama_lengkap }}
                                        @if($intern->nip)
                                            <span style="font-size: 0.75rem; color: var(--accent-primary); font-weight: normal; margin-left: 4px;">({{ $intern->nip }})</span>
                                        @endif
                                    </strong>
                                    <span style="color: var(--text-secondary); font-size: 0.78rem; display: block; margin-top: 2px;">{{ $intern->instansi?->nama_instansi ?? '-' }}</span>
                                </div>
                                <div class="intern-switches">
                                    <!-- Auto Approve Switch -->
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; width: 100%;">
                                        <span style="font-size: 0.78rem; color: var(--text-secondary); font-weight: 500;">Auto Acc</span>
                                        <label class="switch-small">
                                            <input type="checkbox" class="intern-auto-acc" data-intern-id="{{ $intern->user_code }}" {{ $intern->auto_approve_logbook || auth()->user()->auto_approve_logbook_global ? 'checked' : '' }} {{ auth()->user()->auto_approve_logbook_global ? 'disabled' : '' }}>
                                            <span class="slider-small"></span>
                                        </label>
                                    </div>
                                    <!-- Wajib Foto Switch -->
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; width: 100%;">
                                        <span style="font-size: 0.78rem; color: var(--text-secondary); font-weight: 500;">Wajib Foto</span>
                                        <label class="switch-small">
                                            <input type="checkbox" class="intern-photo-req" data-intern-id="{{ $intern->user_code }}" {{ $intern->require_photo_attendance || auth()->user()->require_photo_attendance_global ? 'checked' : '' }} {{ auth()->user()->require_photo_attendance_global ? 'disabled' : '' }}>
                                            <span class="slider-small"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cetak & Rekap Logbook Card -->
    <div class="custom-content-card" style="margin-bottom: 30px;">
        <div class="card-header" style="padding: 20px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: flex-start !important; align-items: center; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <h2 class="card-title" style="margin: 0; font-size: 1.1rem; color: var(--text-primary); text-align: left;">Cetak & Rekap Logbook Anak Didik</h2>
        </div>
        <div style="padding: 24px;">
            <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: flex-end;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; letter-spacing: 0.5px;">PILIH ANAK DIDIK</label>
                    <select name="user_id" required style="padding: 10px 14px; font-size: 0.88rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(0,0,0,0.15); color: var(--text-primary); width: 100%; transition: all 0.3s ease; outline: none; cursor: pointer;">
                        <option value="" style="background: #1a1a2e; color: #fff;">-- Pilih Anak Bimbingan --</option>
                        @foreach($guidedInterns as $intern)
                            <option value="{{ $intern->user_code }}" style="background: #1a1a2e; color: #fff;">{{ $intern->nama_lengkap }} ({{ $intern->instansi?->nama_instansi ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; letter-spacing: 0.5px;">PILIH BULAN</label>
                    <select name="month" style="padding: 10px 14px; font-size: 0.88rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(0,0,0,0.15); color: var(--text-primary); width: 100%; transition: all 0.3s ease; outline: none; cursor: pointer;">
                        <option value="" style="background: #1a1a2e; color: #fff;">Semua Bulan</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" style="background: #1a1a2e; color: #fff;">{{ \Carbon\Carbon::create(now()->year, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; letter-spacing: 0.5px;">PILIH TAHUN</label>
                    <select name="year" style="padding: 10px 14px; font-size: 0.88rem; border: 1px solid var(--glass-border); border-radius: 8px; background: rgba(0,0,0,0.15); color: var(--text-primary); width: 100%; transition: all 0.3s ease; outline: none; cursor: pointer;">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }} style="background: #1a1a2e; color: #fff;">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button type="submit" formaction="{{ route('peserta.logbook.pdf') }}" formtarget="_blank" class="btn-primary hover-lift" style="padding: 10px 20px; font-size: 0.88rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: var(--accent-primary); border-color: var(--accent-primary); font-weight: 600; flex: 1; min-width: 140px; height: 42px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Cetak PDF
                    </button>
                    <button type="submit" formaction="{{ route('peserta.logbook.csv') }}" class="btn-secondary hover-lift" style="padding: 10px 20px; font-size: 0.88rem; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; flex: 1; min-width: 140px; height: 42px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Rekap CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logbooks List Card (Unified Search, Filter, and Table) -->
    <div class="custom-content-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; padding: 20px; border-bottom: 1px solid var(--glass-border);">
            <h2 class="card-title" style="margin: 0;">Daftar Logbook Anak Bimbingan</h2>
            
            <form action="{{ route('admin.logbooks') }}" method="GET" style="margin: 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- Search input -->
                <input type="text" name="search" value="{{ request('search') }}" class="filter-input" placeholder="Cari nama/kegiatan..." style="padding: 8px 12px; font-size: 0.85rem; width: 220px; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-primary);" onchange="this.form.submit()">
                
                <!-- Status approval select -->
                <select name="status_approval" class="filter-select" style="padding: 8px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--text-primary); width: 140px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Pending" {{ request('status_approval') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status_approval') === 'Approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Rejected" {{ request('status_approval') === 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <!-- Date Filter -->
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="filter-input" style="padding: 8px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(255,255,255,0.05); color: var(--text-primary); cursor: pointer;" onchange="this.form.submit()">

                <!-- Month Filter -->
                <select name="bulan" class="filter-select" style="padding: 8px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--text-primary); width: 130px;" onchange="this.form.submit()">
                    <option value="">Semua Bulan</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(now()->year, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>

                <!-- Year Filter -->
                <select name="tahun" class="filter-select" style="padding: 8px 12px; font-size: 0.85rem; border: 1px solid var(--glass-border); border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--text-primary); width: 120px;" onchange="this.form.submit()">
                    <option value="">Semua Tahun</option>
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>

                <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 0.85rem; border-radius: 6px; cursor: pointer;">Filter</button>
                @if(request()->anyFilled(['search', 'status_approval', 'tanggal', 'bulan', 'tahun']))
                    <a href="{{ route('admin.logbooks') }}" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; text-decoration: none; border-radius: 6px; display: inline-block;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive" id="logbook-table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Anak Bimbingan</th>
                        <th>Kegiatan</th>
                        <th>Status</th>
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
                                <span class="badge {{ $logbook->status_approval === 'Approved' ? 'badge-success' : ($logbook->status_approval === 'Rejected' ? 'badge-danger' : ($logbook->status_approval === 'Revisi' ? 'badge-warning-custom' : 'badge-warning')) }}">
                                    {{ $logbook->status_approval }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px; flex-direction: column;">
                                    <button type="button" class="badge badge-info logbook-detail-trigger" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;"
                                        data-id="{{ $logbook->logbook_code }}"
                                        data-name="{{ $logbook->user?->nama_lengkap }}"
                                        data-instansi="{{ $logbook->user?->instansi?->nama_instansi ?? '-' }}"
                                        data-date="{{ $logbook->tanggal->format('d M Y') }}"
                                        data-kegiatan="{{ $logbook->kegiatan }}"
                                        data-description="{{ $logbook->deskripsi }}"
                                        data-tags="{{ $logbook->tags }}"
                                        data-status="{{ $logbook->status_approval }}"
                                        data-comment="{{ $logbook->catatan_pembimbing ?? '-' }}">
                                        Detail
                                    </button>
                                    @if($logbook->status_approval === 'Pending')
                                        <button type="button" class="badge badge-success logbook-approve-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;" data-action-url="{{ route('admin.logbook.approve', $logbook->logbook_code) }}">Setujui</button>
                                        <button type="button" class="badge badge-danger logbook-reject-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center;" data-action-url="{{ route('admin.logbook.reject', $logbook->logbook_code) }}">Tolak</button>
                                        <button type="button" class="badge badge-warning-custom logbook-revision-btn" style="border: none; cursor: pointer; padding: 4px 8px; font-weight: 600; width: 100%; text-align: center; background: #fbbf24 !important; color: #1e1b4b !important;" data-action-url="{{ route('admin.logbook.revision', $logbook->logbook_code) }}">Minta Revisi</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">Tidak ada logbook anak didik yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logbooks->hasPages())
            {{ $logbooks->links('partials.pagination') }}
        @endif
    </div>

    <!-- Logbook Detail Modal -->
    <div id="logbook-detail-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 16px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 30px; position: relative; color: var(--text-primary); box-shadow: 0 15px 35px rgba(0,0,0,0.5); backdrop-filter: blur(20px);">
            <button id="close-modal-btn" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
            
            <div style="margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">Detail Logbook Kegiatan</h3>
                <p id="modal-intern-info" class="muted-small" style="margin: 6px 0 0 0; color: var(--text-secondary);"></p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Tanggal</span>
                    <p id="modal-date" style="margin: 6px 0 0 0; font-weight: 500;"></p>
                </div>
                
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Kegiatan</span>
                    <p id="modal-kegiatan" style="margin: 6px 0 0 0; font-weight: 700; font-size: 1.1rem; color: var(--accent-primary);"></p>
                </div>
                
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Deskripsi Detail</span>
                    <p id="modal-description" style="margin: 6px 0 0 0; white-space: pre-wrap; line-height: 1.6; color: var(--text-primary); background: rgba(255,255,255,0.02); padding: 12px; border-radius: 8px; border: 1px solid var(--glass-border);"></p>
                </div>

                <div>
                    <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Tags / Label</span>
                    <div id="modal-tags" style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Status Approval</span>
                        <div style="margin-top: 6px;">
                            <span id="modal-status" class="badge"></span>
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Catatan Pembimbing</span>
                        <p id="modal-comment" style="margin: 6px 0 0 0; color: var(--text-primary); font-style: italic;"></p>
                    </div>
                </div>
            </div>

            <!-- Action buttons if pending -->
            <div id="modal-actions" style="margin-top: 30px; border-top: 1px solid var(--glass-border); padding-top: 20px; display: flex; gap: 12px; justify-content: flex-end; flex-wrap: wrap;">
                <button type="button" id="modal-approve-btn" class="btn-primary" style="padding: 10px 20px; border-radius: 8px; background: #10b981; border: none; font-weight: 600; cursor: pointer; color: #fff;">Setujui Logbook</button>
                <button type="button" id="modal-reject-btn" class="btn-secondary" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #ef4444; color: #ef4444; background: none; font-weight: 600; cursor: pointer;">Tolak Logbook</button>
                <button type="button" id="modal-revision-btn" class="btn-warning" style="padding: 10px 20px; border-radius: 8px; background: #fbbf24; border: none; font-weight: 600; cursor: pointer; color: #1e1b4b;">Minta Revisi</button>
            </div>
        </div>
    </div>

    <!-- Action Confirmation Modal -->
    <div id="action-confirm-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1100; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 16px; width: 100%; max-width: 450px; padding: 24px; position: relative; color: var(--text-primary); box-shadow: 0 15px 35px rgba(0,0,0,0.5); backdrop-filter: blur(20px);">
            <h3 id="confirm-modal-title" style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Konfirmasi Tindakan</h3>
            <p id="confirm-modal-text" style="margin: 0 0 20px 0; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5;"></p>
            
            <form id="confirm-action-form" method="POST" style="display: flex; flex-direction: column; gap: 15px; margin: 0;">
                @csrf
                <div id="confirm-comment-group" style="display: flex; flex-direction: column; gap: 8px;">
                    <label id="confirm-comment-label" for="confirm-catatan" style="font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">Catatan Pembimbing</label>
                    <textarea id="confirm-catatan" name="catatan_pembimbing" placeholder="Tulis catatan (opsional)..." style="padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: var(--text-primary); width: 100%; min-height: 80px; resize: vertical; font-family: inherit; font-size: 0.9rem;"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" id="confirm-modal-cancel" class="btn-secondary" style="padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; cursor: pointer;">Batal</button>
                    <button type="submit" id="confirm-modal-submit" class="btn-primary" style="padding: 8px 16px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; color: #fff; font-size: 0.85rem;"></button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/admin/logbooks.js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const globalCheckbox = document.getElementById('global-auto-acc');
            const individualCheckboxes = document.querySelectorAll('.intern-auto-acc');
            
            const globalPhotoCheckbox = document.getElementById('global-photo-req');
            const individualPhotoCheckboxes = document.querySelectorAll('.intern-photo-req');

            // Helper function to send post requests
            const toggleSetting = async (url, enabled) => {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ enabled })
                    });
                    return await response.json();
                } catch (error) {
                    console.error(error);
                    return { success: false, message: 'Terjadi kesalahan server.' };
                }
            };

            // Global Auto Approve Listener
            globalCheckbox?.addEventListener('change', async (e) => {
                const enabled = e.target.checked;
                e.target.disabled = true;
                const result = await toggleSetting("{{ route('admin.settings.toggle-global') }}", enabled);
                e.target.disabled = false;

                if (result.success) {
                    if (enabled) {
                        individualCheckboxes.forEach(cb => {
                            cb.checked = true;
                            cb.disabled = true;
                        });
                    } else {
                        // Reload page to fetch precise database individual settings
                        window.location.reload();
                    }
                    
                    if (window.Swal) {
                        window.Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: result.message,
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                } else {
                    e.target.checked = !enabled;
                    alert(result.message);
                }
            });

            // Global Photo Requirement Listener
            globalPhotoCheckbox?.addEventListener('change', async (e) => {
                const enabled = e.target.checked;
                e.target.disabled = true;
                const result = await toggleSetting("{{ route('admin.settings.toggle-global-photo') }}", enabled);
                e.target.disabled = false;

                if (result.success) {
                    if (enabled) {
                        individualPhotoCheckboxes.forEach(cb => {
                            cb.checked = true;
                            cb.disabled = true;
                        });
                    } else {
                        window.location.reload();
                    }
                    
                    if (window.Swal) {
                        window.Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: result.message,
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                } else {
                    e.target.checked = !enabled;
                    alert(result.message);
                }
            });

            // Individual Auto Approve Listener
            individualCheckboxes.forEach(cb => {
                cb.addEventListener('change', async (e) => {
                    const enabled = e.target.checked;
                    const internId = e.target.getAttribute('data-intern-id');
                    const url = `/admin/settings/toggle-intern/${internId}`;

                    e.target.disabled = true;
                    const result = await toggleSetting(url, enabled);
                    e.target.disabled = false;

                    if (result.success) {
                        if (window.Swal) {
                            window.Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: result.message,
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                        }
                    } else {
                        e.target.checked = !enabled;
                        alert(result.message);
                    }
                });
            });

            // Individual Photo Requirement Listener
            individualPhotoCheckboxes.forEach(cb => {
                cb.addEventListener('change', async (e) => {
                    const enabled = e.target.checked;
                    const internId = e.target.getAttribute('data-intern-id');
                    const url = `/admin/settings/toggle-intern-photo/${internId}`;

                    e.target.disabled = true;
                    const result = await toggleSetting(url, enabled);
                    e.target.disabled = false;

                    if (result.success) {
                        if (window.Swal) {
                            window.Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: result.message,
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                        }
                    } else {
                        e.target.checked = !enabled;
                        alert(result.message);
                    }
                });
            });

            // Collapsible panel triggers
            const toggleBtn = document.getElementById('toggle-individual-btn');
            const wrapper = document.getElementById('individual-toggles-wrapper');
            const chevron = toggleBtn?.querySelector('.chevron-icon');

            toggleBtn?.addEventListener('click', () => {
                const isOpen = wrapper.style.maxHeight !== '0px' && wrapper.style.maxHeight !== '';
                if (isOpen) {
                    wrapper.style.maxHeight = '0px';
                    wrapper.style.opacity = '0';
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                } else {
                    wrapper.style.maxHeight = '600px'; // sufficiently large to contain search and scroll list
                    wrapper.style.opacity = '1';
                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                }
            });

            // Instant Search filter logic
            const searchInput = document.getElementById('search-intern-toggle');
            const items = document.querySelectorAll('.intern-toggle-item');
            const searchPlaceholder = document.getElementById('search-placeholder-text');
            const searchNoMatch = document.getElementById('search-no-match-text');

            searchInput?.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                
                if (query === '') {
                    // Hide everything and show default placeholder
                    items.forEach(item => item.style.display = 'none');
                    if (searchPlaceholder) searchPlaceholder.style.display = 'block';
                    if (searchNoMatch) searchNoMatch.style.display = 'none';
                } else {
                    // Hide default placeholder
                    if (searchPlaceholder) searchPlaceholder.style.display = 'none';
                    
                    let matchCount = 0;
                    items.forEach(item => {
                        const name = item.getAttribute('data-name') || '';
                        const nip = item.getAttribute('data-nip') || '';
                        if (name.includes(query) || nip.includes(query)) {
                            item.style.display = 'flex';
                            matchCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Show no match placeholder if no items matched
                    if (searchNoMatch) {
                        searchNoMatch.style.display = (matchCount === 0) ? 'block' : 'none';
                    }
                }
            });
        });
    </script>
@endpush
