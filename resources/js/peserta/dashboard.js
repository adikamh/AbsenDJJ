// Page-specific scripts for dashboard/peserta.blade.php.

document.addEventListener('DOMContentLoaded', () => {
    const locationEl = document.getElementById('location-coordinate');
    let userCoordinates = '';

    // SweetAlert2 Helpers for dynamic theme-matching alerts
    function getSwalTheme() {
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        return {
            background: isLight ? '#ffffff' : '#1e293b',
            color: isLight ? '#0f172a' : '#f8fafc',
            confirmButtonColor: isLight ? '#2e4085' : '#ffcc33',
        };
    }

    // Alert helper
    function showSwalAlert(icon, title, text) {
        if (!window.Swal) {
            alert(text || title);
            return Promise.resolve();
        }
        return window.Swal.fire({
            ...getSwalTheme(),
            icon,
            title,
            text,
            confirmButtonText: 'Mengerti'
        });
    }

    function showSwalSuccess(title, text) {
        return showSwalAlert('success', title, text);
    }

    function showSwalError(title, text) {
        return showSwalAlert('error', title, text);
    }

    async function showSwalConfirm(title, text) {
        if (!window.Swal) {
            return confirm(text || title);
        }
        const result = await window.Swal.fire({
            ...getSwalTheme(),
            icon: 'warning',
            title,
            text,
            showCancelButton: true,
            confirmButtonColor: '#ffcc33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        });
        return result.isConfirmed;
    }

    // Show Toast alert reminder if they haven't checked in yet today
    if (window.userNeedsAttendanceReminder && window.Swal) {
        const theme = getSwalTheme();
        const Toast = window.Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            background: theme.background,
            color: theme.color,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', window.Swal.stopTimer)
                toast.addEventListener('mouseleave', window.Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'warning',
            title: 'Belum Absen Hari Ini',
            text: 'Anda belum melakukan absensi masuk. Harap segera melakukan absen masuk!'
        });
    }

    // Camera & Attendance elements
    const webcamVideo = document.getElementById('webcam-video');
    const selfiePreview = document.getElementById('selfie-preview');
    const canvas = document.getElementById('attendance-canvas');
    const btnStartCamera = document.getElementById('btn-start-camera');
    const btnCapturePhoto = document.getElementById('btn-capture-photo');
    const btnRetakePhoto = document.getElementById('btn-retake-photo');
    const btnSubmitIn = document.getElementById('btn-submit-in');
    const btnSubmitOut = document.getElementById('btn-submit-out');

    const cameraSelect = document.getElementById('camera-select');
    const cameraSelectOut = document.getElementById('camera-select-out');

    let stream = null;
    let capturedSelfieBase64 = null;

    // Helper to list and populate camera devices
    async function populateCameraDevices(selectEl) {
        if (!selectEl) return;
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');

            // Keep the selected value if any
            const currentValue = selectEl.value;

            selectEl.innerHTML = '';
            if (videoDevices.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'Tidak ada kamera ditemukan';
                selectEl.appendChild(opt);
                return;
            }

            videoDevices.forEach((device, index) => {
                const opt = document.createElement('option');
                opt.value = device.deviceId;
                opt.textContent = device.label || `Kamera ${index + 1}`;
                if (device.deviceId === currentValue) {
                    opt.selected = true;
                }
                selectEl.appendChild(opt);
            });
        } catch (err) {
            console.error('Error enumerating cameras:', err);
            selectEl.innerHTML = '<option value="">Gagal mendeteksi kamera</option>';
        }
    }

    // Attempt to list cameras on load
    populateCameraDevices(cameraSelect);
    populateCameraDevices(cameraSelectOut);

    // Also populate cameras when browser media devices change
    if (navigator.mediaDevices && navigator.mediaDevices.addEventListener) {
        navigator.mediaDevices.addEventListener('devicechange', () => {
            populateCameraDevices(cameraSelect);
            populateCameraDevices(cameraSelectOut);
        });
    }

    // Preload Logo PU
    const puLogo = new Image();
    puLogo.src = '/images/Logo/Logo_PU.png';

    // ===== Geocoding & Map watermarking helper functions =====
    async function getAddressFromCoords(lat, lng) {
        // 1. Try Esri Reverse Geocoding first (extremely detailed for Indonesian streets, subdistricts and villages)
        try {
            const response = await fetch(`https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode?f=pjson&location=${lng},${lat}`, {
                signal: AbortSignal.timeout(4000) // Timeout after 4 seconds to fallback quickly
            });
            const data = await response.json();
            if (data && data.address) {
                const address = data.address;
                
                let regency = address.Subregion || '';
                if (regency && !regency.startsWith('Kabupaten') && !regency.startsWith('Kota')) {
                    regency = 'Kabupaten ' + regency;
                }
                
                let subdistrict = address.City || '';
                if (subdistrict && !subdistrict.startsWith('Kecamatan') && !subdistrict.startsWith('Kec.')) {
                    subdistrict = 'Kecamatan ' + subdistrict;
                }
                
                const rawAddressLines = [
                    address.Address || '',
                    address.Neighborhood || '',
                    subdistrict,
                    regency,
                    address.Region || ''
                ];
                
                let addressLines = [];
                rawAddressLines.forEach(line => {
                    const trimmed = line.trim();
                    if (trimmed && !addressLines.includes(trimmed)) {
                        addressLines.push(trimmed);
                    }
                });
                
                if (addressLines.length > 0) {
                    return addressLines;
                }
            }
        } catch (esriError) {
            console.warn('Esri geocoding failed or timed out, falling back to Nominatim:', esriError);
        }

        // 2. Fallback to OpenStreetMap Nominatim
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=id`, {
                headers: {
                    'User-Agent': 'AbsenDJJ-App/1.0'
                }
            });
            const data = await response.json();
            const address = data.address || {};

            const rawAddressLines = [
                address.road || address.footway || address.street || address.path || '',
                address.village || address.suburb || address.neighbourhood || address.hamlet || address.isolated_dwellings || '',
                address.subdistrict || address.town || address.city_district || address.municipality || address.district || '',
                address.city || address.regency || address.county || address.state_district || '',
                address.state || ''
            ];

            let addressLines = [];
            rawAddressLines.forEach(line => {
                const trimmed = line.trim();
                if (trimmed && !addressLines.includes(trimmed)) {
                    addressLines.push(trimmed);
                }
            });

            return addressLines;
        } catch (osmError) {
            console.error('All geocoding services failed:', osmError);
            return ['Gagal mendapatkan alamat'];
        }
    }

    function loadStaticMapImage(lat, lng) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => resolve(img);
            img.onerror = () => reject(new Error('Map load failed'));
            img.src = `https://static-maps.yandex.ru/1.x/?ll=${lng},${lat}&z=14&size=150,150&l=map&pt=${lng},${lat},pm2rdm`;
        });
    }

    function loadLocalImage(src) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = src;
        });
    }

    // Multi-location Geofencing Setup
    const officeLat = locationEl ? parseFloat(locationEl.getAttribute('data-office-lat')) : NaN;
    const officeLng = locationEl ? parseFloat(locationEl.getAttribute('data-office-lng')) : NaN;
    const officeRadius = locationEl ? parseFloat(locationEl.getAttribute('data-office-radius')) : NaN;
    const distanceEl = document.getElementById('location-distance');

    let officeLocations = [];
    if (locationEl) {
        try {
            officeLocations = JSON.parse(locationEl.getAttribute('data-office-locations') || '[]');
        } catch (e) {
            console.error('Error parsing office locations:', e);
        }
    }

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth radius in meters
        const phi1 = lat1 * Math.PI / 180;
        const phi2 = lat2 * Math.PI / 180;
        const deltaPhi = (lat2 - lat1) * Math.PI / 180;
        const deltaLambda = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
                  Math.cos(phi1) * Math.cos(phi2) *
                  Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c; // in meters
    }

    function getClosestOfficeLocation(userLat, userLng) {
        if (!officeLocations || officeLocations.length === 0) {
            return {
                name: 'Kantor Utama',
                latitude: officeLat,
                longitude: officeLng,
                radius: officeRadius,
                distance: calculateDistance(userLat, userLng, officeLat, officeLng)
            };
        }

        let minDistance = null;
        let closest = null;

        officeLocations.forEach(loc => {
            const lat = parseFloat(loc.latitude);
            const lng = parseFloat(loc.longitude);
            const rad = parseInt(loc.radius);
            const dist = calculateDistance(userLat, userLng, lat, lng);
            if (minDistance === null || dist < minDistance) {
                minDistance = dist;
                closest = {
                    name: loc.name,
                    latitude: lat,
                    longitude: lng,
                    radius: rad,
                    distance: dist
                };
            }
        });

        return closest;
    }

    // Geolocation Real-time Tracking (watchPosition)
    if (locationEl && navigator.geolocation) {
        navigator.geolocation.watchPosition(
            async (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                const accuracy = pos.coords.accuracy ? Math.round(pos.coords.accuracy) : 0;
                const coordsStr = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                userCoordinates = coordsStr;

                // Update raw accuracy attribute for reference
                locationEl.setAttribute('data-accuracy', accuracy.toString());

                // Build Accuracy Badge
                let accuracyBadge = '';
                if (accuracy > 0) {
                    if (accuracy > 100) {
                        accuracyBadge = ` <span style="font-size: 0.78rem; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); margin-left: 6px; display: inline-block; vertical-align: middle;">±${accuracy}m (Akurasi Rendah)</span>`;
                    } else if (accuracy > 20) {
                        accuracyBadge = ` <span style="font-size: 0.78rem; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); margin-left: 6px; display: inline-block; vertical-align: middle;">±${accuracy}m (Akurasi Sedang)</span>`;
                    } else {
                        accuracyBadge = ` <span style="font-size: 0.78rem; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); margin-left: 6px; display: inline-block; vertical-align: middle;">±${accuracy}m (Akurasi Tinggi)</span>`;
                    }
                }

                // Temporary loading address text
                locationEl.innerHTML = `${coordsStr}${accuracyBadge}<br><span style="font-size: 0.82rem; font-weight: normal; color: var(--text-secondary); display: block; margin-top: 4px; line-height: 1.3;">Memuat nama lokasi...</span>`;

                // Calculate distance to closest location
                if (distanceEl) {
                    const closest = getClosestOfficeLocation(lat, lng);
                    const formattedDistance = closest.distance.toFixed(1);
                    const isInsideOrTolerated = closest.distance <= closest.radius || (closest.distance - accuracy) <= closest.radius;

                    if (isInsideOrTolerated) {
                        distanceEl.style.color = '#34d399';
                        let toleranceText = '';
                        if (closest.distance > closest.radius) {
                            toleranceText = ' (Toleransi Akurasi GPS)';
                        }
                        distanceEl.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px; display: inline-block;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            ${formattedDistance} m (Di dalam zona ${closest.name}${toleranceText} / Maks: ${closest.radius} m)
                        `;
                    } else {
                        distanceEl.style.color = '#f87171';
                        distanceEl.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px; display: inline-block;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            ${formattedDistance} m (Di luar zona ${closest.name} / Maks: ${closest.radius} m)
                        `;
                    }
                }

                // Fetch reverse geocoding address
                const addressLines = await getAddressFromCoords(lat, lng);
                const fullAddress = addressLines.join(', ');
                locationEl.innerHTML = `${coordsStr}${accuracyBadge}<br><span style="font-size: 0.82rem; font-weight: normal; color: var(--text-secondary); display: block; margin-top: 4px; line-height: 1.3;">${fullAddress}</span>`;
            },
            (err) => {
                console.error('WatchPosition error:', err);
                userCoordinates = '-6.914744, 107.625680'; // Fallback
                locationEl.innerHTML = `${userCoordinates}<br><span style="font-size: 0.82rem; font-weight: normal; color: var(--text-secondary); display: block; margin-top: 4px; line-height: 1.3;">Lokasi tidak dapat dideteksi. Menggunakan lokasi default.</span>`;
            },
            { timeout: 15000, enableHighAccuracy: true, maximumAge: 0 }
        );
    } else if (locationEl) {
        locationEl.textContent = 'Browser tidak mendukung GPS';
        userCoordinates = '-6.914744, 107.625680';
    }

    async function applyWatermarks(canvas, webcamVideo, coordinatesStr) {
        if (!canvas || !webcamVideo) return null;

        const ctx = canvas.getContext('2d');
        const width = webcamVideo.videoWidth || 640;
        const height = webcamVideo.videoHeight || 480;
        canvas.width = width;
        canvas.height = height;

        // Draw video frame (mirrored)
        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(webcamVideo, 0, 0, width, height);
        ctx.setTransform(1, 0, 0, 1, 0, 0);

        // Parse coordinates
        let lat = -6.914744;
        let lng = 107.625680;
        if (coordinatesStr) {
            const parts = coordinatesStr.split(',').map(p => p.trim());
            if (parts.length === 2) {
                lat = parseFloat(parts[0]) || lat;
                lng = parseFloat(parts[1]) || lng;
            }
        }

        // Fetch geocoding address & load static map / logo image
        const addressPromise = getAddressFromCoords(lat, lng);
        const mapPromise = loadStaticMapImage(lat, lng);
        const logoPromise = loadLocalImage('/images/Logo/logo_absen.png');

        const [addressLines, mapImg, logoImg] = await Promise.all([
            addressPromise,
            mapPromise.catch(() => null),
            logoPromise
        ]);

        // 1. Draw Logo (watermark) in top-left corner
        if (logoImg) {
            const logoWidth = Math.round(width * 0.45);
            const logoHeight = Math.round(logoImg.height * (logoWidth / logoImg.width));
            ctx.drawImage(logoImg, 15, 15, logoWidth, logoHeight);
        }

        // 2. Draw Mini Map in bottom-left corner
        if (mapImg) {
            const mapSize = 140;
            const mapX = 15;
            const mapY = height - mapSize - 15;
            ctx.drawImage(mapImg, mapX, mapY, mapSize, mapSize);

            // Draw map border
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)';
            ctx.lineWidth = 2;
            ctx.strokeRect(mapX, mapY, mapSize, mapSize);
        }

        // 3. Draw Info Box in bottom-right corner
        const now = new Date();
        const pad = (n) => n.toString().padStart(2, '0');
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const timeStr = `${pad(now.getDate())} ${months[now.getMonth()]} ${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

        const latStr = lat >= 0 ? `${lat.toFixed(6)}° N` : `${Math.abs(lat).toFixed(6)}° S`;
        const lngStr = lng >= 0 ? `${lng.toFixed(6)}° E` : `${Math.abs(lng).toFixed(6)}° W`;
        const coordStr = `${latStr}, ${lngStr}`;

        const textLines = [
            timeStr,
            coordStr,
            ...addressLines
        ];

        const fontSize = Math.max(16, Math.round(width * 0.028)); // Scaled font size (e.g. ~18px for 640px width, ~36px for 1280px)
        ctx.font = `bold ${fontSize}px 'Outfit', 'Inter', sans-serif`;
        ctx.textAlign = 'right';
        ctx.textBaseline = 'top';

        let maxLineWidth = 0;
        textLines.forEach(line => {
            const w = ctx.measureText(line).width;
            if (w > maxLineWidth) maxLineWidth = w;
        });

        const padding = Math.round(fontSize * 0.7); // Scaled padding inside the box
        const lineSpacing = Math.round(fontSize * 0.4); // Scaled line spacing
        const boxWidth = maxLineWidth + padding * 2;
        const boxHeight = textLines.length * (fontSize + lineSpacing) + padding * 2;
        const boxX = width - boxWidth - 15;
        const boxY = height - boxHeight - 15;

        // Semi-transparent background (larger rounded corners: 10px)
        ctx.fillStyle = 'rgba(15, 23, 42, 0.75)';
        ctx.beginPath();
        if (ctx.roundRect) {
            ctx.roundRect(boxX, boxY, boxWidth, boxHeight, 10);
        } else {
            ctx.rect(boxX, boxY, boxWidth, boxHeight);
        }
        ctx.fill();

        // Draw text lines
        ctx.fillStyle = '#ffffff';
        textLines.forEach((line, idx) => {
            const lineY = boxY + padding + idx * (fontSize + lineSpacing);
            ctx.fillText(line, width - 15 - padding, lineY);
        });

        return canvas.toDataURL('image/jpeg', 0.9);
    }

    // Start Check-in Webcam — combined flow: Buka Kamera → Select Camera → Mulai Kamera
    const cameraSelectWrap = document.getElementById('camera-select-wrap');
    const btnConfirmCamera = document.getElementById('btn-confirm-camera');

    if (btnStartCamera) {
        btnStartCamera.addEventListener('click', async () => {
            // Test user: lewati cek data-is-past-limit (server akan validasi ulang dengan waktu HP)
            if (!window.isTestUser && btnSubmitIn && btnSubmitIn.getAttribute('data-is-past-limit') === 'true') {
                showSwalError('Absensi Ditolak', 'Batas waktu absensi masuk hari ini telah berakhir pada pukul ' + btnSubmitIn.getAttribute('data-limit-time') + '. Anda tidak dapat melakukan absensi masuk lagi.');
                return;
            }
            if (!userCoordinates) {
                showSwalError('Lokasi Belum Terkunci', 'Sedang mendeteksi lokasi Anda, mohon tunggu sebentar.');
                return;
            }

            const coords = userCoordinates.split(',').map(c => parseFloat(c.trim()));
            const closest = getClosestOfficeLocation(coords[0], coords[1]);
            const gpsAccuracy = parseFloat(locationEl.getAttribute('data-accuracy') || '0');
            const isInsideOrTolerated = closest.distance <= closest.radius || (closest.distance - gpsAccuracy) <= closest.radius;

            if (!isInsideOrTolerated) {
                showSwalError('Gagal Absen Masuk', 'Absensi ditolak! Posisi Anda berada di luar radius kantor terdekat: ' + closest.name + ' (' + closest.distance.toFixed(0) + ' meter dari kantor, batas radius: ' + closest.radius + ' meter).');
                return;
            }

            // Step 1: Show camera selector panel and populate device list
            if (cameraSelectWrap) {
                await populateCameraDevices(cameraSelect);
                cameraSelectWrap.style.display = 'block';
                btnStartCamera.style.display = 'none';
                const btnStopCamera = document.getElementById('btn-stop-camera');
                if (btnStopCamera) btnStopCamera.style.display = 'inline-flex';
            }
        });
    }

    const btnStopCamera = document.getElementById('btn-stop-camera');
    if (btnStopCamera) {
        btnStopCamera.addEventListener('click', () => {
            stopCamera();
            if (cameraSelectWrap) cameraSelectWrap.style.display = 'none';
            if (btnCapturePhoto) btnCapturePhoto.style.display = 'none';
            if (btnRetakePhoto) btnRetakePhoto.style.display = 'none';
            if (btnStopCamera) btnStopCamera.style.display = 'none';
            if (btnStartCamera) btnStartCamera.style.display = 'inline-flex';
            if (selfiePreview) selfiePreview.style.display = 'none';
            capturedSelfieBase64 = null;
            btnSubmitIn.disabled = true;
        });
    }

    if (btnConfirmCamera) {
        btnConfirmCamera.addEventListener('click', async () => {
            try {
                const selectedDeviceId = cameraSelect ? cameraSelect.value : null;
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        deviceId: selectedDeviceId ? { exact: selectedDeviceId } : undefined,
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    },
                    audio: false
                });
                webcamVideo.srcObject = stream;
                webcamVideo.style.display = 'block';
                selfiePreview.style.display = 'none';

                if (cameraSelectWrap) cameraSelectWrap.style.display = 'none';
                btnCapturePhoto.style.display = 'inline-flex';
                btnRetakePhoto.style.display = 'none';
                if (btnStopCamera) btnStopCamera.style.display = 'inline-flex';

                // Re-populate devices to fetch labels now that permission has been granted
                await populateCameraDevices(cameraSelect);
            } catch (err) {
                console.error('Error accessing webcam:', err);
                showSwalError('Gagal Akses Kamera', 'Gagal mengakses kamera. Pastikan izin kamera telah diberikan.');
            }
        });
    }

    // Stop Webcam
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        if (webcamVideo) {
            webcamVideo.srcObject = null;
            webcamVideo.style.display = 'none';
        }
    }

    // Capture & Watermark Selfie
    if (btnCapturePhoto) {
        btnCapturePhoto.addEventListener('click', async () => {
            if (!webcamVideo || !canvas) return;

            // Set loading state
            btnCapturePhoto.disabled = true;
            btnCapturePhoto.textContent = 'Memproses Foto...';

            try {
                capturedSelfieBase64 = await applyWatermarks(canvas, webcamVideo, userCoordinates);

                if (capturedSelfieBase64) {
                    // Update UI preview
                    selfiePreview.src = capturedSelfieBase64;
                    selfiePreview.style.display = 'block';

                    stopCamera();

                    btnCapturePhoto.style.display = 'none';
                    if (btnStopCamera) btnStopCamera.style.display = 'none';
                    btnRetakePhoto.style.display = 'inline-flex';
                    btnSubmitIn.disabled = false;
                }
            } catch (error) {
                console.error('Error watermarking check-in photo:', error);
                showSwalError('Gagal Memproses Foto', 'Gagal memproses foto. Silakan coba kembali.');
            } finally {
                btnCapturePhoto.disabled = false;
                btnCapturePhoto.textContent = 'Ambil Foto';
            }
        });
    }

    // Retake Photo — go back to camera selector
    if (btnRetakePhoto) {
        btnRetakePhoto.addEventListener('click', async () => {
            capturedSelfieBase64 = null;
            selfiePreview.style.display = 'none';
            btnSubmitIn.disabled = true;
            btnRetakePhoto.style.display = 'none';

            // Show camera selector again
            if (cameraSelectWrap) {
                await populateCameraDevices(cameraSelect);
                cameraSelectWrap.style.display = 'block';
            }
        });
    }

    // CSRF Token Helper
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Submit Check-In
    if (btnSubmitIn) {
        btnSubmitIn.addEventListener('click', async () => {
            const configEl = document.getElementById('attendance-config-data');
            if (configEl && configEl.getAttribute('data-actual-masuk')) {
                return;
            }
            // Test user: lewati cek data-is-past-limit (server akan validasi ulang dengan waktu HP)
            if (!window.isTestUser && btnSubmitIn.getAttribute('data-is-past-limit') === 'true') {
                showSwalError('Absensi Ditolak', 'Batas waktu absensi masuk hari ini telah berakhir pada pukul ' + btnSubmitIn.getAttribute('data-limit-time') + '. Anda tidak dapat melakukan absensi masuk lagi.');
                return;
            }
            const requirePhoto = document.getElementById('location-coordinate')?.getAttribute('data-require-photo') === 'true';
            if (requirePhoto && !capturedSelfieBase64) {
                showSwalError('Data Belum Lengkap', 'Silakan ambil foto selfie terlebih dahulu.');
                return;
            }
            if (!userCoordinates) {
                showSwalError('Lokasi Belum Terkunci', 'Sedang mendeteksi lokasi, silakan tunggu sebentar.');
                return;
            }

            const coords = userCoordinates.split(',').map(c => parseFloat(c.trim()));
            const closest = getClosestOfficeLocation(coords[0], coords[1]);
            const gpsAccuracy = parseFloat(locationEl.getAttribute('data-accuracy') || '0');
            const isInsideOrTolerated = closest.distance <= closest.radius || (closest.distance - gpsAccuracy) <= closest.radius;

            if (!isInsideOrTolerated) {
                showSwalError('Gagal Absen Masuk', 'Absensi ditolak! Posisi Anda berada di luar radius kantor terdekat: ' + closest.name + ' (' + closest.distance.toFixed(0) + ' meter dari kantor, batas radius: ' + closest.radius + ' meter).');
                return;
            }

            btnSubmitIn.disabled = true;
            btnSubmitIn.textContent = 'Memproses...';

            try {
                const response = await fetch('/peserta/attendance/check-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        koordinat: userCoordinates,
                        akurasi_gps: gpsAccuracy,
                        foto: requirePhoto ? capturedSelfieBase64 : null,
                        // Kirim waktu lokal HP khusus akun test (yogi.sutana@gmail.com)
                        ...(window.isTestUser ? { client_time: new Date().toISOString() } : {})
                    })
                });

                const result = await response.json();

                if (result.success) {
                    await showSwalSuccess('Absen Masuk Berhasil', result.message);
                    window.location.reload();
                } else {
                    showSwalError('Gagal Absen Masuk', result.message || 'Gagal melakukan absen masuk.');
                    btnSubmitIn.disabled = false;
                    btnSubmitIn.textContent = 'Absen Masuk';
                }
            } catch (error) {
                console.error('Error during check-in:', error);
                showSwalError('Terjadi Kesalahan', 'Terjadi kesalahan sistem. Silakan coba kembali.');
                btnSubmitIn.disabled = false;
                btnSubmitIn.textContent = 'Absen Masuk';
            }
        });
    }

    // ─────────────────────────────────────────────
    // CHECKOUT CAMERA LOGIC
    // ─────────────────────────────────────────────
    const webcamVideoOut     = document.getElementById('webcam-video-out');
    const selfiePreviewOut   = document.getElementById('selfie-preview-out');
    const canvasOut          = document.getElementById('attendance-canvas-out');
    const btnStartCameraOut  = document.getElementById('btn-start-camera-out');
    const btnCaptureOut      = document.getElementById('btn-capture-photo-out');
    const btnRetakeOut       = document.getElementById('btn-retake-photo-out');

    let streamOut = null;
    let capturedOutBase64 = null;
    let earlyCheckoutReason = null;

    // If checkout camera UI exists, keep btn-submit-out disabled until selfie taken
    if (btnStartCameraOut && btnSubmitOut && !btnSubmitOut.disabled) {
        btnSubmitOut.disabled = true;
    }

    // Start checkout webcam — combined flow: Buka Kamera → Select Camera → Mulai Kamera
    const cameraSelectOutWrap = document.getElementById('camera-select-out-wrap');
    const btnConfirmCameraOut = document.getElementById('btn-confirm-camera-out');

    function isEarlyCheckout() {
        const configEl = document.getElementById('attendance-config-data');
        if (!configEl) return false;

        const jamPulang = configEl.getAttribute('data-jam-pulang');
        if (!jamPulang) return false;

        const jamMasuk = configEl.getAttribute('data-jam-masuk');
        const batasTerlambat = configEl.getAttribute('data-batas-terlambat');
        const actualMasuk = configEl.getAttribute('data-actual-masuk');

        const now = new Date();
        const [standardPulangH, standardPulangM] = jamPulang.split(':').map(Number);
        const targetPulang = new Date(now.getFullYear(), now.getMonth(), now.getDate(), standardPulangH, standardPulangM, 0);

        if (actualMasuk && jamMasuk) {
            const [actualH, actualM] = actualMasuk.split(':').map(Number);
            const [standardH, standardM] = jamMasuk.split(':').map(Number);

            const actualMasukDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), actualH, actualM, 0);
            const standardMasukDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), standardH, standardM, 0);

            if (actualMasukDate > standardMasukDate) {
                const diffMs = actualMasukDate - standardMasukDate;
                const diffMins = Math.floor(diffMs / 60000);
                targetPulang.setMinutes(targetPulang.getMinutes() + diffMins);
            }
        } else if (!actualMasuk && batasTerlambat && jamMasuk) {
            const [limitH, limitM] = batasTerlambat.split(':').map(Number);
            const [standardH, standardM] = jamMasuk.split(':').map(Number);

            const limitMasukDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), limitH, limitM, 0);
            const standardMasukDate = new Date(now.getFullYear(), now.getMonth(), now.getDate(), standardH, standardM, 0);

            if (limitMasukDate > standardMasukDate) {
                const diffMs = limitMasukDate - standardMasukDate;
                const diffMins = Math.floor(diffMs / 60000);
                targetPulang.setMinutes(targetPulang.getMinutes() + diffMins);
            }
        }

        return now < targetPulang;
    }

    if (btnStartCameraOut) {
        btnStartCameraOut.addEventListener('click', async () => {
            // Check if logbook is filled
            if (btnSubmitOut) {
                const todayLogbooksCount = parseInt(btnSubmitOut.getAttribute('data-today-logbooks-count') || '0');
                if (todayLogbooksCount === 0) {
                    if (window.Swal) {
                        window.Swal.fire({
                            ...getSwalTheme(),
                            icon: 'warning',
                            title: 'Logbook Belum Diisi',
                            text: 'Anda wajib mengisi minimal 1 logbook kegiatan hari ini sebelum melakukan absen pulang.',
                            showCancelButton: true,
                            confirmButtonColor: '#10b981',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Tulis Logbook Sekarang',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed && typeof window.toggleAddLogbookModal === 'function') {
                                window.toggleAddLogbookModal(true);
                            }
                        });
                    } else {
                        if (confirm('Anda wajib mengisi minimal 1 logbook kegiatan hari ini sebelum melakukan absen pulang.\n\nTulis logbook sekarang?')) {
                            if (typeof window.toggleAddLogbookModal === 'function') {
                                window.toggleAddLogbookModal(true);
                            }
                        }
                    }
                    return;
                }
            }
            if (!userCoordinates) {
                showSwalError('Lokasi Belum Terkunci', 'Sedang mendeteksi lokasi Anda, mohon tunggu sebentar.');
                return;
            }

            const coords = userCoordinates.split(',').map(c => parseFloat(c.trim()));
            const closest = getClosestOfficeLocation(coords[0], coords[1]);
            const gpsAccuracy = parseFloat(locationEl.getAttribute('data-accuracy') || '0');
            const isInsideOrTolerated = closest.distance <= closest.radius || (closest.distance - gpsAccuracy) <= closest.radius;

            if (!isInsideOrTolerated) {
                showSwalError('Gagal Absen Pulang', 'Absensi ditolak! Posisi Anda berada di luar radius kantor terdekat: ' + closest.name + ' (' + closest.distance.toFixed(0) + ' meter dari kantor, batas radius: ' + closest.radius + ' meter).');
                return;
            }

            // Early checkout verification and validation
            if (isEarlyCheckout()) {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark'
                    || document.body.classList.contains('dark-mode');

                const { value: text, dismiss } = await Swal.fire({
                    title: 'Pulang Sebelum Waktunya',
                    text: 'Anda terdeteksi melakukan absen pulang sebelum waktunya. Apakah Anda ingin melakukan pengajuan izin?',
                    input: 'textarea',
                    inputPlaceholder: 'Tulis alasan pengajuan izin Anda di sini...',
                    inputAttributes: {
                        'aria-label': 'Tulis alasan pengajuan izin Anda di sini'
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#ffcc33',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Ajukan Izin',
                    cancelButtonText: 'Batal',
                    background: isDark ? '#1e293b' : '#ffffff',
                    color: isDark ? '#f8fafc' : '#0f172a',
                    inputValidator: (value) => {
                        if (!value || !value.trim()) {
                            return 'Alasan pengajuan izin wajib diisi!';
                        }
                    }
                });

                if (dismiss || !text) {
                    return; // Stop and do not show camera
                }

                earlyCheckoutReason = text.trim();
            } else {
                earlyCheckoutReason = null;
            }

            // Step 1: Show camera selector panel and populate device list
            if (cameraSelectOutWrap) {
                await populateCameraDevices(cameraSelectOut);
                cameraSelectOutWrap.style.display = 'block';
                btnStartCameraOut.style.display = 'none';
                const btnStopCameraOut = document.getElementById('btn-stop-camera-out');
                if (btnStopCameraOut) btnStopCameraOut.style.display = 'inline-flex';
            }
        });
    }

    const btnStopCameraOut = document.getElementById('btn-stop-camera-out');
    if (btnStopCameraOut) {
        btnStopCameraOut.addEventListener('click', () => {
            stopCameraOut();
            if (cameraSelectOutWrap) cameraSelectOutWrap.style.display = 'none';
            if (btnCaptureOut) btnCaptureOut.style.display = 'none';
            if (btnRetakeOut) btnRetakeOut.style.display = 'none';
            if (btnStopCameraOut) btnStopCameraOut.style.display = 'none';
            if (btnStartCameraOut) btnStartCameraOut.style.display = 'inline-flex';
            if (selfiePreviewOut) selfiePreviewOut.style.display = 'none';
            capturedOutBase64 = null;
            if (btnSubmitOut) btnSubmitOut.disabled = true;
        });
    }

    if (btnConfirmCameraOut) {
        btnConfirmCameraOut.addEventListener('click', async () => {
            try {
                const selectedDeviceId = cameraSelectOut ? cameraSelectOut.value : null;
                streamOut = await navigator.mediaDevices.getUserMedia({
                    video: {
                        deviceId: selectedDeviceId ? { exact: selectedDeviceId } : undefined,
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    },
                    audio: false
                });
                webcamVideoOut.srcObject = streamOut;
                webcamVideoOut.style.display = 'block';
                selfiePreviewOut.style.display = 'none';

                if (cameraSelectOutWrap) cameraSelectOutWrap.style.display = 'none';
                btnCaptureOut.style.display = 'inline-flex';
                btnRetakeOut.style.display = 'none';
                if (btnStopCameraOut) btnStopCameraOut.style.display = 'inline-flex';

                // Re-populate devices to fetch labels now that permission has been granted
                await populateCameraDevices(cameraSelectOut);
            } catch (err) {
                console.error('Error accessing webcam (out):', err);
                showSwalError('Gagal Akses Kamera', 'Gagal mengakses kamera. Pastikan izin kamera telah diberikan.');
            }
        });
    }

    function stopCameraOut() {
        if (streamOut) {
            streamOut.getTracks().forEach(t => t.stop());
            streamOut = null;
        }
        if (webcamVideoOut) {
            webcamVideoOut.srcObject = null;
            webcamVideoOut.style.display = 'none';
        }
    }

    // Capture checkout selfie with watermarks
    if (btnCaptureOut) {
        btnCaptureOut.addEventListener('click', async () => {
            if (!webcamVideoOut || !canvasOut) return;

            // Set loading state
            btnCaptureOut.disabled = true;
            btnCaptureOut.textContent = 'Memproses Foto...';

            try {
                capturedOutBase64 = await applyWatermarks(canvasOut, webcamVideoOut, userCoordinates);

                if (capturedOutBase64) {
                    selfiePreviewOut.src = capturedOutBase64;
                    selfiePreviewOut.style.display = 'block';

                    stopCameraOut();

                    btnCaptureOut.style.display = 'none';
                    if (btnStopCameraOut) btnStopCameraOut.style.display = 'none';
                    btnRetakeOut.style.display  = 'inline-flex';
                    if (btnSubmitOut) btnSubmitOut.disabled = false;
                }
            } catch (error) {
                console.error('Error watermarking check-out photo:', error);
                showSwalError('Gagal Memproses Foto', 'Gagal memproses foto. Silakan coba kembali.');
            } finally {
                btnCaptureOut.disabled = false;
                btnCaptureOut.textContent = 'Ambil Foto';
            }
        });
    }

    // Retake checkout photo — go back to camera selector
    if (btnRetakeOut) {
        btnRetakeOut.addEventListener('click', async () => {
            capturedOutBase64 = null;
            selfiePreviewOut.style.display = 'none';
            if (btnSubmitOut) btnSubmitOut.disabled = true;
            btnRetakeOut.style.display = 'none';

            // Show camera selector again
            if (cameraSelectOutWrap) {
                await populateCameraDevices(cameraSelectOut);
                cameraSelectOutWrap.style.display = 'block';
            }
        });
    }

    let isSubmittingOut = false;

    // ─────────────────────────────────────────────
    // SUBMIT CHECK-OUT (now requires selfie)
    // ─────────────────────────────────────────────
    if (btnSubmitOut) {
        btnSubmitOut.addEventListener('click', async () => {
            if (isSubmittingOut) return;

            // Check if logbook is filled
            const todayLogbooksCount = parseInt(btnSubmitOut.getAttribute('data-today-logbooks-count') || '0');
            if (todayLogbooksCount === 0) {
                if (window.Swal) {
                    window.Swal.fire({
                        ...getSwalTheme(),
                        icon: 'warning',
                        title: 'Logbook Belum Diisi',
                        text: 'Anda wajib mengisi minimal 1 logbook kegiatan hari ini sebelum melakukan absen pulang.',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Tulis Logbook Sekarang',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed && typeof window.toggleAddLogbookModal === 'function') {
                            window.toggleAddLogbookModal(true);
                        }
                    });
                } else {
                    if (confirm('Anda wajib mengisi minimal 1 logbook kegiatan hari ini sebelum melakukan absen pulang.\n\nTulis logbook sekarang?')) {
                        if (typeof window.toggleAddLogbookModal === 'function') {
                            window.toggleAddLogbookModal(true);
                        }
                    }
                }
                return;
            }
            // If checkout camera UI exists, selfie is mandatory
            if (btnStartCameraOut && !capturedOutBase64) {
                showSwalError('Data Belum Lengkap', 'Silakan ambil foto selfie untuk absen pulang terlebih dahulu.');
                return;
            }
            if (!userCoordinates) {
                showSwalError('Lokasi Belum Terkunci', 'Sedang mendeteksi lokasi, silakan tunggu sebentar.');
                return;
            }

            const coords = userCoordinates.split(',').map(c => parseFloat(c.trim()));
            const closest = getClosestOfficeLocation(coords[0], coords[1]);
            const gpsAccuracy = parseFloat(locationEl.getAttribute('data-accuracy') || '0');
            const isInsideOrTolerated = closest.distance <= closest.radius || (closest.distance - gpsAccuracy) <= closest.radius;

            if (!isInsideOrTolerated) {
                showSwalError('Gagal Absen Pulang', 'Absensi ditolak! Posisi Anda berada di luar radius kantor terdekat: ' + closest.name + ' (' + closest.distance.toFixed(0) + ' meter dari kantor, batas radius: ' + closest.radius + ' meter).');
                return;
            }

            isSubmittingOut = true;
            const isConfirmed = await showSwalConfirm('Absen Pulang', 'Apakah Anda yakin ingin melakukan absen pulang?');
            if (!isConfirmed) {
                isSubmittingOut = false;
                return;
            }

            btnSubmitOut.disabled = true;
            btnSubmitOut.textContent = 'Memproses...';

            try {
                const response = await fetch('/peserta/attendance/check-out', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        koordinat: userCoordinates,
                        akurasi_gps: gpsAccuracy,
                        foto: capturedOutBase64,
                        alasan: earlyCheckoutReason,
                        // Kirim waktu lokal HP khusus akun test (yogi.sutana@gmail.com)
                        ...(window.isTestUser ? { client_time: new Date().toISOString() } : {})
                    })
                });

                const result = await response.json();

                if (result.success) {
                    await showSwalSuccess('Absen Pulang Berhasil', result.message);
                    window.location.reload();
                } else {
                    showSwalError('Gagal Absen Pulang', result.message || 'Gagal melakukan absen pulang.');
                    isSubmittingOut = false;
                    btnSubmitOut.disabled = false;
                    btnSubmitOut.textContent = 'Absen Pulang';
                }
            } catch (error) {
                console.error('Error during check-out:', error);
                showSwalError('Terjadi Kesalahan', 'Terjadi kesalahan sistem. Silakan coba kembali.');
                isSubmittingOut = false;
                btnSubmitOut.disabled = false;
                btnSubmitOut.textContent = 'Absen Pulang';
            }
        });
    }

    // ===== Selfie Preview Popup Modal =====
    const selfieModal = document.getElementById('selfie-modal');
    const modalSelfieImg = document.getElementById('modal-selfie-img');
    const modalSelfieTitle = document.getElementById('modal-selfie-title');
    const closeSelfieModal = document.getElementById('close-selfie-modal');

    window.showImageModal = function(src, title) {
        if (!selfieModal || !modalSelfieImg || !modalSelfieTitle) return;
        modalSelfieImg.src = src;
        modalSelfieTitle.textContent = title;
        selfieModal.classList.add('is-open');
    };

    closeSelfieModal?.addEventListener('click', () => {
        selfieModal?.classList.remove('is-open');
    });

    selfieModal?.addEventListener('click', (e) => {
        if (e.target === selfieModal) {
            selfieModal.classList.remove('is-open');
        }
    });

    // Real-time ticking clock synchronized with server time
    const clockContainer = document.querySelector('.digital-clock-container');
    const clockEl = document.getElementById('digital-clock');
    const dateEl = document.getElementById('digital-date');

    if (clockEl && dateEl && clockContainer) {
        const serverTimestamp = parseInt(clockContainer.getAttribute('data-server-timestamp'));
        // Test user: gunakan waktu HP murni. User biasa: sinkronisasi dengan server.
        const timeOffset = window.isTestUser ? 0 : (!isNaN(serverTimestamp) ? (serverTimestamp - Date.now()) : 0);

        function updateClock() {
            // Jika isTestUser: offset = 0 → murni waktu HP. Jika tidak: pakai offset server.
            const now = new Date(Date.now() + timeOffset);
            const pad = (n) => n.toString().padStart(2, '0');

            const hours = pad(now.getHours());
            const minutes = pad(now.getMinutes());
            const seconds = pad(now.getSeconds());
            clockEl.textContent = `${hours}:${minutes}:${seconds}`;

            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const day = pad(now.getDate());
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            dateEl.textContent = `${dayName}, ${day} ${monthName} ${year}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    }

    // Periodically poll backend to check if today has been set as holiday by Admin
    // Using static JSON cache file check to avoid booting Laravel/DB every 5 seconds.
    async function checkHolidayStatusRealtime() {
        try {
            const now = new Date();
            const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

            // Query the static file with a cache-buster timestamp parameter
            const response = await fetch(`/today_holiday.json?_t=${Date.now()}`);
            const data = await response.json();

            let isHoliday = false;
            let scheduleChanged = false;
            if (data && data.date === todayStr) {
                isHoliday = data.is_holiday;
                if (window.currentSchedule) {
                    const normTime = (t) => t ? t.substring(0, 5) : null;
                    const jsonMasuk = normTime(data.jam_masuk);
                    const jsonPulang = normTime(data.jam_pulang);
                    const jsonBatas = normTime(data.batas_keterlambatan);
                    
                    const curMasuk = normTime(window.currentSchedule.jam_masuk);
                    const curPulang = normTime(window.currentSchedule.jam_pulang);
                    const curBatas = normTime(window.currentSchedule.batas_keterlambatan);
                    
                    if (jsonMasuk !== curMasuk || jsonPulang !== curPulang || jsonBatas !== curBatas) {
                        scheduleChanged = true;
                    }
                }
            } else {
                // Fallback to Laravel boot check if the cached date is outdated (which will also self-heal/regenerate the static JSON file)
                const fallbackResponse = await fetch('/peserta/attendance/check-holiday');
                const fallbackData = await fallbackResponse.json();
                isHoliday = fallbackData.is_holiday;
            }

            if (scheduleChanged) {
                // Schedule changed: reload to sync UI
                window.location.reload();
                return;
            }

            const isHolidayView = document.body.innerHTML.includes('Absensi Libur');
            if (isHoliday && !isHolidayView) {
                // Changed from Workday to Holiday: reload to lock absensi
                window.location.reload();
            } else if (!isHoliday && isHolidayView) {
                // Changed from Holiday to Workday: reload to unlock absensi
                window.location.reload();
            }
        } catch (e) {
            console.warn('Failed to poll holiday status:', e);
        }
    }

    // Poll every 5 seconds (very safe and fast, Nginx/Apache serves it instantly from static file cache)
    setInterval(checkHolidayStatusRealtime, 5000);
});
