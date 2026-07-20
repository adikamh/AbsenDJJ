@extends('dashboard.layout')

@section('title', 'Lokasi & Geofencing - Pengaturan')
@section('header_title', 'Parameter Lokasi & Geofencing')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @vite('resources/css/super_admin/settings.css')
    <style>
        .locations-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .locations-table th, .locations-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }
        .locations-table th {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .locations-table td {
            font-size: 0.9rem;
            color: var(--text-primary);
        }
        .locations-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .badge-radius {
            background: rgba(46, 64, 133, 0.15);
            color: #4f46e5;
            border: 1px solid rgba(46, 64, 133, 0.3);
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-block;
        }
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-delete:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }
    </style>
@endpush

@section('content')
    <div class="settings-container">
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif

        {{-- ===== Card 1: List of Office Locations ===== --}}
        <div class="content-card">
            <div class="card-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 20px;">
                <h2 class="card-title" style="display: flex; align-items: center; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--accent-primary);">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Daftar Lokasi Kantor Utama Terdaftar
                </h2>
            </div>

            <div style="overflow-x: auto;">
                <table class="locations-table">
                    <thead>
                        <tr>
                            <th>Nama Lokasi / Gedung</th>
                            <th>Koordinat (Lat, Lng)</th>
                            <th>Radius Absensi</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $loc)
                            <tr>
                                <td style="font-weight: 600;">{{ $loc->name }}</td>
                                <td style="font-family: monospace; color: var(--text-secondary);">
                                    {{ number_format($loc->latitude, 6) }}, {{ number_format($loc->longitude, 6) }}
                                </td>
                                <td><span class="badge-radius">{{ $loc->radius }} meter</span></td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <button type="button" class="btn-secondary btn-edit-trigger" 
                                            data-id="{{ $loc->id }}"
                                            data-name="{{ $loc->name }}"
                                            data-lat="{{ $loc->latitude }}"
                                            data-lng="{{ $loc->longitude }}"
                                            data-radius="{{ $loc->radius }}"
                                            style="padding: 6px 12px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; margin-right: 4px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        Edit
                                    </button>
                                    <form action="{{ route('super-admin.settings.geofencing.destroy', $loc->id) }}" method="POST" class="delete-location-form" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                    Belum ada titik koordinat yang didaftarkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== Card 2: Add/Edit Location ===== --}}
        <form id="office-location-form" action="{{ route('super-admin.settings.geofencing.store') }}" method="POST" class="modal-form">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            <div class="content-card" style="margin-top: 25px;">
                <div class="card-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 20px;">
                    <h2 class="card-title" id="form-card-title" style="display: flex; align-items: center; gap: 10px;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="color: var(--accent-primary);">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <span id="title-text-indicator">Tambah Titik Lokasi Kantor Baru</span>
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
                    <label for="location_name">Nama Lokasi / Nama Gedung</label>
                    <input type="text" id="location_name" name="name" required placeholder="Contoh: Gedung A / Gerbang Depan / Lab Utama" value="{{ old('name') }}">
                    <small style="display: block; color: var(--text-secondary); margin-top: 4px;">Nama pengenal lokasi ini untuk memudahkan peserta.</small>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="latitude_kantor">Latitude Lokasi</label>
                    <input type="text" id="latitude_kantor" name="latitude" value="{{ old('latitude', $settings->latitude_kantor) }}" required readonly style="cursor: not-allowed;">
                    <small style="display: block; color: var(--text-secondary); margin-top: 4px;">Terisi otomatis dari penanda peta di atas.</small>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="longitude_kantor">Longitude Lokasi</label>
                    <input type="text" id="longitude_kantor" name="longitude" value="{{ old('longitude', $settings->longitude_kantor) }}" required readonly style="cursor: not-allowed;">
                    <small style="display: block; color: var(--text-secondary); margin-top: 4px;">Terisi otomatis dari penanda peta di atas.</small>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="radius_meter">Radius Batas Absensi (Meter)</label>
                    <div class="input-with-icon">
                        <input type="number" id="radius_meter" name="radius" min="1" value="{{ old('radius', 100) }}" required>
                        <span class="input-icon-right"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                    </div>
                    <small style="display: block; color: var(--text-secondary); margin-top: 4px;">Batas jarak maksimal (dalam meter) peserta boleh melakukan absen dari titik koordinat ini.</small>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                    <button type="button" id="btn-cancel-edit" class="btn-secondary" style="display: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        Batal Edit
                    </button>
                    <button type="submit" id="btn-submit-form" class="btn-primary" style="padding: 12px 28px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span id="btn-submit-text">Simpan Lokasi Kantor Baru</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <div id="settings-config" style="display:none;"
         data-global-default-settings='{"jam_masuk":"{{ substr($settings->jam_masuk, 0, 5) }}","batas_keterlambatan":"{{ substr($settings->batas_keterlambatan, 0, 5) }}","jam_pulang":"{{ substr($settings->jam_pulang, 0, 5) }}"}'
         data-day-overrides='@json($dayOverrides->values())'
         data-date-overrides='@json($dateOverrides)'
         data-routes-store-schedule="{{ route('super-admin.schedules.store') }}"
         data-routes-sync-holidays="{{ route('super-admin.schedules.sync-holidays') }}"
         data-csrf-token="{{ csrf_token() }}"
         data-locations='@json($locations)'>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Confirm delete location
            document.querySelectorAll('form.delete-location-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (window.Swal) {
                        const isLight = document.documentElement.getAttribute('data-theme') === 'light' ||
                            !document.body.classList.contains('dark-theme');
                        window.Swal.fire({
                            title: 'Hapus Lokasi Kantor?',
                            text: 'Apakah Anda yakin ingin menghapus lokasi kantor ini dari daftar geofencing?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal',
                            background: isLight ? '#ffffff' : '#1e293b',
                            color: isLight ? '#0f172a' : '#f8fafc'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    } else {
                        if (confirm('Apakah Anda yakin ingin menghapus lokasi kantor ini?')) {
                            form.submit();
                        }
                    }
                });
            });

            // Edit location trigger
            const form = document.getElementById('office-location-form');
            const formMethod = document.getElementById('form-method');
            const titleText = document.getElementById('title-text-indicator');
            const btnSubmitText = document.getElementById('btn-submit-text');
            const btnCancelEdit = document.getElementById('btn-cancel-edit');
            const storeAction = form ? form.getAttribute('action') : '';

            // Save default values on load for cancel reset
            const defaultLatVal = document.getElementById('latitude_kantor') ? document.getElementById('latitude_kantor').value : '';
            const defaultLngVal = document.getElementById('longitude_kantor') ? document.getElementById('longitude_kantor').value : '';
            const defaultRadiusVal = document.getElementById('radius_meter') ? document.getElementById('radius_meter').value : '100';

            document.querySelectorAll('.btn-edit-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const lat = parseFloat(this.getAttribute('data-lat'));
                    const lng = parseFloat(this.getAttribute('data-lng'));
                    const radius = parseInt(this.getAttribute('data-radius'));

                    // Set form inputs
                    document.getElementById('location_name').value = name;
                    
                    const latInput = document.getElementById('latitude_kantor');
                    const lngInput = document.getElementById('longitude_kantor');
                    const radiusInput = document.getElementById('radius_meter');

                    latInput.value = lat.toFixed(6);
                    lngInput.value = lng.toFixed(6);
                    radiusInput.value = radius;

                    // Trigger events to sync map in settings.js
                    latInput.dispatchEvent(new Event('change'));
                    radiusInput.dispatchEvent(new Event('input'));

                    // Double-enforce direct update if global objects exist
                    if (window.markerInstance && window.mapInstance && window.circleInstance) {
                        window.markerInstance.setLatLng([lat, lng]);
                        window.circleInstance.setLatLng([lat, lng]);
                        window.mapInstance.setView([lat, lng], 16);
                    }

                    // Update form action and method to PUT
                    if (form) {
                        form.setAttribute('action', `/super-admin/settings/geofencing/locations/${id}`);
                        formMethod.value = 'PUT';
                        titleText.textContent = `Edit Titik Lokasi Kantor: ${name}`;
                        btnSubmitText.textContent = 'Simpan Perubahan';
                        btnCancelEdit.style.display = 'inline-block';
                        
                        // Scroll smoothly to form
                        form.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Cancel edit mode
            if (btnCancelEdit) {
                btnCancelEdit.addEventListener('click', function() {
                    document.getElementById('location_name').value = '';
                    
                    const latInput = document.getElementById('latitude_kantor');
                    const lngInput = document.getElementById('longitude_kantor');
                    const radiusInput = document.getElementById('radius_meter');

                    latInput.value = defaultLatVal;
                    lngInput.value = defaultLngVal;
                    radiusInput.value = defaultRadiusVal;

                    // Trigger events to sync map in settings.js
                    latInput.dispatchEvent(new Event('change'));
                    radiusInput.dispatchEvent(new Event('input'));

                    // Double-enforce direct reset if global objects exist
                    if (window.markerInstance && window.mapInstance && window.circleInstance) {
                        const dLat = parseFloat(defaultLatVal) || -6.8988;
                        const dLng = parseFloat(defaultLngVal) || 107.6358;
                        const dRad = parseInt(defaultRadiusVal) || 100;
                        window.markerInstance.setLatLng([dLat, dLng]);
                        window.circleInstance.setLatLng([dLat, dLng]);
                        window.circleInstance.setRadius(dRad);
                        window.mapInstance.setView([dLat, dLng], 15);
                    }

                    if (form) {
                        form.setAttribute('action', storeAction);
                        formMethod.value = 'POST';
                        titleText.textContent = 'Tambah Titik Lokasi Kantor Baru';
                        btnSubmitText.textContent = 'Simpan Lokasi Kantor Baru';
                        btnCancelEdit.style.display = 'none';
                    }
                });
            }

            // Submission loading popup
            if (form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        return;
                    }
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                    }
                    if (window.Swal) {
                        const isEdit = formMethod.value === 'PUT';
                        window.Swal.fire({
                            title: isEdit ? 'Menyimpan Perubahan...' : 'Menyimpan Lokasi Baru...',
                            text: 'Sedang menyimpan data lokasi ke database. Harap tunggu sebentar.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                window.Swal.showLoading();
                            }
                        });
                    }
                });
            }
        });

        // Render existing locations on the map as static markers
        window.addEventListener('load', function() {
            const configEl = document.getElementById('settings-config');
            if (configEl && window.mapInstance) {
                try {
                    const locationsList = JSON.parse(configEl.getAttribute('data-locations') || '[]');
                    locationsList.forEach(loc => {
                        const lat = parseFloat(loc.latitude);
                        const lng = parseFloat(loc.longitude);
                        const radius = parseInt(loc.radius);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            // Draw a static blue circle for this location
                            L.circle([lat, lng], {
                                color: '#3b82f6',
                                fillColor: '#3b82f6',
                                fillOpacity: 0.1,
                                radius: radius
                            }).addTo(window.mapInstance)
                              .bindPopup(`<b>${loc.name}</b><br>Radius: ${radius}m`);

                            // Place a static marker with opacity
                            L.marker([lat, lng], { opacity: 0.65 })
                                .addTo(window.mapInstance)
                                .bindPopup(`<b>${loc.name}</b><br>Radius: ${radius}m`);
                        }
                    });
                } catch (e) {
                    console.error('Error parsing or rendering existing locations:', e);
                }
            }
        });
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @vite('resources/js/super_admin/settings.js')
@endpush
