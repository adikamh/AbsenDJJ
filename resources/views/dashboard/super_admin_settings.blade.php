@extends('dashboard.layout')

@section('title', 'Pengaturan Aplikasi')
@section('header_title', 'Pengaturan Parameter Global')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @vite('resources/css/super_admin_settings.css')
@endpush

@section('content')
    <div class="settings-container">
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('super-admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="settings-grid">
                <!-- Card 1: Parameter Waktu -->
                <div class="content-card">
                    <div class="card-header" style="border-bottom: 1px solid var(--glass-border); padding-bottom: 12px; margin-bottom: 20px;">
                        <h2 class="card-title" style="display: flex; align-items: center; gap: 10px;">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="color: var(--accent-primary);">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            Parameter Waktu Kehadiran
                        </h2>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="jam_masuk" style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Jam Masuk Kerja</label>
                        <div class="input-with-icon">
                            <input type="time" id="jam_masuk" name="jam_masuk" value="{{ old('jam_masuk', substr($settings->jam_masuk, 0, 5)) }}" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--input-bg); color: var(--text-main);">
                            <span class="input-icon-right">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </span>
                        </div>
                        <small style="display: block; color: var(--text-muted); margin-top: 4px;">Jam mulai kerja normal bagi peserta magang.</small>
                        @error('jam_masuk')
                            <span style="color: var(--badge-danger-text); font-size: 0.85rem; display: block; margin-top: 4px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="batas_keterlambatan" style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Batas Toleransi Keterlambatan</label>
                        <div class="input-with-icon">
                            <input type="time" id="batas_keterlambatan" name="batas_keterlambatan" value="{{ old('batas_keterlambatan', substr($settings->batas_keterlambatan, 0, 5)) }}" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--input-bg); color: var(--text-main);">
                            <span class="input-icon-right">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </span>
                        </div>
                        <small style="display: block; color: var(--text-muted); margin-top: 4px;">Toleransi keterlambatan check-in (absen masuk tetap dicatat normal sebelum batas ini).</small>
                        @error('batas_keterlambatan')
                            <span style="color: var(--badge-danger-text); font-size: 0.85rem; display: block; margin-top: 4px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="jam_pulang" style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Jam Pulang Kerja</label>
                        <div class="input-with-icon">
                            <input type="time" id="jam_pulang" name="jam_pulang" value="{{ old('jam_pulang', substr($settings->jam_pulang, 0, 5)) }}" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--input-bg); color: var(--text-main);">
                            <span class="input-icon-right">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </span>
                        </div>
                        <small style="display: block; color: var(--text-muted); margin-top: 4px;">Jam kepulangan normal peserta untuk check-out.</small>
                        @error('jam_pulang')
                            <span style="color: var(--badge-danger-text); font-size: 0.85rem; display: block; margin-top: 4px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Card 2: Parameter Lokasi & Geofencing -->
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
                        <input type="text" id="map-search-input" placeholder="Cari nama lokasi atau alamat kantor..." style="flex: 1; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--input-bg); color: var(--text-main); font-size: 14px;">
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
                        <label for="latitude_kantor" style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Latitude Kantor</label>
                        <input type="text" id="latitude_kantor" name="latitude_kantor" value="{{ old('latitude_kantor', $settings->latitude_kantor) }}" required readonly style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--input-bg); color: var(--text-main); cursor: not-allowed;">
                        <small style="display: block; color: var(--text-muted); margin-top: 4px;">Terisi otomatis dari penanda peta di atas.</small>
                        @error('latitude_kantor')
                            <span style="color: var(--badge-danger-text); font-size: 0.85rem; display: block; margin-top: 4px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="longitude_kantor" style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Longitude Kantor</label>
                        <input type="text" id="longitude_kantor" name="longitude_kantor" value="{{ old('longitude_kantor', $settings->longitude_kantor) }}" required readonly style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--input-bg); color: var(--text-main); cursor: not-allowed;">
                        <small style="display: block; color: var(--text-muted); margin-top: 4px;">Terisi otomatis dari penanda peta di atas.</small>
                        @error('longitude_kantor')
                            <span style="color: var(--badge-danger-text); font-size: 0.85rem; display: block; margin-top: 4px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="radius_meter" style="display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Radius Batas Absensi (Meter)</label>
                        <div class="input-with-icon">
                            <input type="number" id="radius_meter" name="radius_meter" min="1" value="{{ old('radius_meter', $settings->radius_meter) }}" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--input-bg); color: var(--text-main);">
                            <span class="input-icon-right">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </span>
                        </div>
                        <small style="display: block; color: var(--text-muted); margin-top: 4px;">Batas jarak maksimal (dalam meter) peserta boleh melakukan absen dari titik koordinat kantor.</small>
                        @error('radius_meter')
                            <span style="color: var(--badge-danger-text); font-size: 0.85rem; display: block; margin-top: 4px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="settings-actions" style="display: flex; justify-content: flex-end; margin-top: 24px;">
                <button type="submit" class="btn-primary" style="padding: 12px 28px; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapEl = document.getElementById('map');
            if (!mapEl) return;

            const latInput = document.getElementById('latitude_kantor');
            const lngInput = document.getElementById('longitude_kantor');
            const radiusInput = document.getElementById('radius_meter');

            let defaultLat = parseFloat(latInput.value) || -6.8988;
            let defaultLng = parseFloat(lngInput.value) || 107.6358;
            let defaultRadius = parseInt(radiusInput.value) || 100;

            const getSwalColors = () => {
                const isLight = document.documentElement.getAttribute('data-theme') === 'light' || 
                                !document.body.classList.contains('dark-theme');
                return {
                    background: isLight ? '#ffffff' : '#1e293b',
                    color: isLight ? '#0f172a' : '#f8fafc',
                    confirmButtonColor: '#2e4085'
                };
            };

            const showMapAlert = (icon, title, text) => {
                if (window.Swal) {
                    window.Swal.fire({
                        ...getSwalColors(),
                        icon: icon,
                        title: title,
                        text: text,
                        confirmButtonText: 'Mengerti'
                    });
                } else {
                    alert(text);
                }
            };

            // Initialize map
            const map = L.map('map').setView([defaultLat, defaultLng], 15);

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add Draggable Marker
            const marker = L.marker([defaultLat, defaultLng], {
                draggable: true
            }).addTo(map);

            // Add Geofence Circle
            let circle = L.circle([defaultLat, defaultLng], {
                color: '#10b981',
                fillColor: '#10b981',
                fillOpacity: 0.15,
                radius: defaultRadius
            }).addTo(map);

            // Update inputs and circle when marker is dragged
            function updateCoordinates(lat, lng) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                circle.setLatLng([lat, lng]);
            }

            marker.on('drag', (e) => {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            marker.on('dragend', (e) => {
                const position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
                map.panTo(position);
            });

            // Update marker and circle when map is clicked
            map.on('click', (e) => {
                const position = e.latlng;
                marker.setLatLng(position);
                updateCoordinates(position.lat, position.lng);
            });

            // Update circle radius when radius input changes
            radiusInput.addEventListener('input', () => {
                const r = parseInt(radiusInput.value) || 0;
                circle.setRadius(r);
            });

            // Search Location using Nominatim
            const searchInput = document.getElementById('map-search-input');
            const searchBtn = document.getElementById('btn-map-search');

            function performSearch() {
                const query = searchInput.value.trim();
                if (!query) return;

                searchBtn.disabled = true;
                searchBtn.textContent = 'Mencari...';

                fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const result = data[0];
                            const lat = parseFloat(result.lat);
                            const lng = parseFloat(result.lon);

                            marker.setLatLng([lat, lng]);
                            map.setView([lat, lng], 16);
                            updateCoordinates(lat, lng);
                        } else {
                            showMapAlert('warning', 'Lokasi Tidak Ditemukan', 'Coba masukkan kata kunci yang lebih spesifik.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showMapAlert('error', 'Koneksi Gagal', 'Terjadi kesalahan koneksi saat mencari lokasi.');
                    })
                    .finally(() => {
                        searchBtn.disabled = false;
                        searchBtn.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            Cari
                        `;
                    });
            }

            searchBtn.addEventListener('click', performSearch);
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });

            // Geolocation GPS
            const gpsBtn = document.getElementById('btn-map-gps');
            gpsBtn.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    showMapAlert('error', 'GPS Tidak Didukung', 'Browser Anda tidak mendukung layanan lokasi GPS.');
                    return;
                }

                gpsBtn.disabled = true;
                gpsBtn.textContent = 'Mendeteksi...';

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        marker.setLatLng([lat, lng]);
                        map.setView([lat, lng], 17);
                        updateCoordinates(lat, lng);

                        gpsBtn.disabled = false;
                        gpsBtn.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                            </svg>
                            GPS
                        `;
                    },
                    (error) => {
                        console.error(error);
                        showMapAlert('warning', 'GPS Gagal', 'Gagal mendeteksi lokasi GPS Anda. Pastikan izin lokasi telah aktif.');
                        gpsBtn.disabled = false;
                        gpsBtn.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                            </svg>
                            GPS
                        `;
                    },
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                );
            });
        });
    </script>
@endpush

