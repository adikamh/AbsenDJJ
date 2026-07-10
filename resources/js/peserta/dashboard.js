// Page-specific scripts for dashboard/peserta.blade.php.

document.addEventListener('DOMContentLoaded', () => {
    const locationEl = document.getElementById('location-coordinate');
    let userCoordinates = '';

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

    // Attempt to list cameras on load (labels might be empty until permission is granted)
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
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=id`, {
                headers: {
                    'User-Agent': 'AbsenDJJ-App/1.0'
                }
            });
            const data = await response.json();
            const address = data.address || {};
            
            const road = address.road || address.suburb || address.neighbourhood || address.village || '';
            const district = address.subdistrict || address.suburb || address.village || '';
            const city = address.city || address.city_district || address.regency || '';
            const state = address.state || '';
            
            let addressLines = [];
            if (road) addressLines.push(road);
            if (district && district !== road) addressLines.push(district);
            if (city) addressLines.push(city);
            if (state) addressLines.push(state);
            
            return addressLines;
        } catch (error) {
            console.error('Error fetching address:', error);
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

    // Geolocation Detection with reverse geocoding & distance checking
    const officeLat = locationEl ? parseFloat(locationEl.getAttribute('data-office-lat')) : NaN;
    const officeLng = locationEl ? parseFloat(locationEl.getAttribute('data-office-lng')) : NaN;
    const officeRadius = locationEl ? parseFloat(locationEl.getAttribute('data-office-radius')) : NaN;
    const distanceEl = document.getElementById('location-distance');

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

    if (locationEl && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                const lat = pos.coords.latitude.toFixed(6);
                const lng = pos.coords.longitude.toFixed(6);
                userCoordinates = `${lat}, ${lng}`;
                
                locationEl.innerHTML = `${userCoordinates}<br><span style="font-size: 0.82rem; font-weight: normal; color: var(--text-secondary); display: block; margin-top: 4px; line-height: 1.3;">Memuat nama lokasi...</span>`;
                
                // Calculate distance
                if (distanceEl && !isNaN(officeLat) && !isNaN(officeLng)) {
                    const distanceMeters = calculateDistance(parseFloat(lat), parseFloat(lng), officeLat, officeLng);
                    const formattedDistance = distanceMeters.toFixed(1);
                    
                    if (distanceMeters <= officeRadius) {
                        distanceEl.style.color = '#34d399';
                        distanceEl.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px; display: inline-block;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            ${formattedDistance} m (Di dalam zona / Maks: ${officeRadius} m)
                        `;
                    } else {
                        distanceEl.style.color = '#f87171';
                        distanceEl.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px; display: inline-block;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            ${formattedDistance} m (Di luar zona / Maks: ${officeRadius} m)
                        `;
                    }
                }
                
                const addressLines = await getAddressFromCoords(lat, lng);
                const fullAddress = addressLines.join(', ');
                locationEl.innerHTML = `${userCoordinates}<br><span style="font-size: 0.82rem; font-weight: normal; color: var(--text-secondary); display: block; margin-top: 4px; line-height: 1.3;">${fullAddress}</span>`;
            },
            async () => {
                userCoordinates = '-6.914744, 107.625680'; // Fallback
                locationEl.innerHTML = `${userCoordinates}<br><span style="font-size: 0.82rem; font-weight: normal; color: var(--text-secondary); display: block; margin-top: 4px; line-height: 1.3;">Lokasi tidak dapat dideteksi. Menggunakan lokasi default.</span>`;
                
                if (distanceEl && !isNaN(officeLat) && !isNaN(officeLng)) {
                    const distanceMeters = calculateDistance(-6.914744, 107.625680, officeLat, officeLng);
                    const formattedDistance = distanceMeters.toFixed(1);
                    
                    if (distanceMeters <= officeRadius) {
                        distanceEl.style.color = '#34d399';
                        distanceEl.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px; display: inline-block;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            ${formattedDistance} m (Di dalam zona / Maks: ${officeRadius} m)
                        `;
                    } else {
                        distanceEl.style.color = '#f87171';
                        distanceEl.innerHTML = `
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px; display: inline-block;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            ${formattedDistance} m (Di luar zona / Maks: ${officeRadius} m)
                        `;
                    }
                }
                
                const addressLines = await getAddressFromCoords(-6.914744, 107.625680);
                const fullAddress = addressLines.join(', ');
                locationEl.innerHTML = `${userCoordinates}<br><span style="font-size: 0.82rem; font-weight: normal; color: var(--text-secondary); display: block; margin-top: 4px; line-height: 1.3;">${fullAddress}</span>`;
            },
            { timeout: 10000, enableHighAccuracy: true }
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

        const fontSize = 12;
        ctx.font = `bold ${fontSize}px 'Outfit', 'Inter', sans-serif`;
        ctx.textAlign = 'right';
        ctx.textBaseline = 'top';

        let maxLineWidth = 0;
        textLines.forEach(line => {
            const w = ctx.measureText(line).width;
            if (w > maxLineWidth) maxLineWidth = w;
        });

        const padding = 10;
        const boxWidth = maxLineWidth + padding * 2;
        const boxHeight = textLines.length * (fontSize + 6) + padding * 2;
        const boxX = width - boxWidth - 15;
        const boxY = height - boxHeight - 15;

        // Semi-transparent background
        ctx.fillStyle = 'rgba(15, 23, 42, 0.7)';
        ctx.beginPath();
        if (ctx.roundRect) {
            ctx.roundRect(boxX, boxY, boxWidth, boxHeight, 8);
        } else {
            ctx.rect(boxX, boxY, boxWidth, boxHeight);
        }
        ctx.fill();

        // Draw text lines
        ctx.fillStyle = '#ffffff';
        textLines.forEach((line, idx) => {
            const lineY = boxY + padding + idx * (fontSize + 6);
            ctx.fillText(line, width - 15 - padding, lineY);
        });

        return canvas.toDataURL('image/jpeg', 0.9);
    }

    // Start Check-in Webcam — combined flow: Buka Kamera → Select Camera → Mulai Kamera
    const cameraSelectWrap = document.getElementById('camera-select-wrap');
    const btnConfirmCamera = document.getElementById('btn-confirm-camera');

    if (btnStartCamera) {
        btnStartCamera.addEventListener('click', async () => {
            // Step 1: Show camera selector panel and populate device list
            if (cameraSelectWrap) {
                await populateCameraDevices(cameraSelect);
                cameraSelectWrap.style.display = 'block';
                btnStartCamera.style.display = 'none';
            }
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

                // Re-populate devices to fetch labels now that permission has been granted
                await populateCameraDevices(cameraSelect);
            } catch (err) {
                console.error('Error accessing webcam:', err);
                alert('Gagal mengakses kamera. Pastikan izin kamera telah diberikan.');
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
                    btnRetakePhoto.style.display = 'inline-flex';
                    btnSubmitIn.disabled = false;
                }
            } catch (error) {
                console.error('Error watermarking check-in photo:', error);
                alert('Gagal memproses foto. Silakan coba kembali.');
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
            if (!capturedSelfieBase64) {
                alert('Silakan ambil foto selfie terlebih dahulu.');
                return;
            }
            if (!userCoordinates) {
                alert('Sedang mendeteksi lokasi, silakan tunggu sebentar.');
                return;
            }

            btnSubmitIn.disabled = true;
            btnSubmitIn.textContent = 'Memproses...';

            try {
                const response = await fetch('/peserta/attendance/check-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        koordinat: userCoordinates,
                        foto: capturedSelfieBase64
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal melakukan absen masuk.');
                    btnSubmitIn.disabled = false;
                    btnSubmitIn.textContent = 'Absen Masuk';
                }
            } catch (error) {
                console.error('Error during check-in:', error);
                alert('Terjadi kesalahan sistem. Silakan coba kembali.');
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

    // If checkout camera UI exists, keep btn-submit-out disabled until selfie taken
    if (btnStartCameraOut && btnSubmitOut && !btnSubmitOut.disabled) {
        btnSubmitOut.disabled = true;
    }

    // Start checkout webcam — combined flow: Buka Kamera → Select Camera → Mulai Kamera
    const cameraSelectOutWrap = document.getElementById('camera-select-out-wrap');
    const btnConfirmCameraOut = document.getElementById('btn-confirm-camera-out');

    if (btnStartCameraOut) {
        btnStartCameraOut.addEventListener('click', async () => {
            // Step 1: Show camera selector panel and populate device list
            if (cameraSelectOutWrap) {
                await populateCameraDevices(cameraSelectOut);
                cameraSelectOutWrap.style.display = 'block';
                btnStartCameraOut.style.display = 'none';
            }
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

                // Re-populate devices to fetch labels now that permission has been granted
                await populateCameraDevices(cameraSelectOut);
            } catch (err) {
                console.error('Error accessing webcam (out):', err);
                alert('Gagal mengakses kamera. Pastikan izin kamera telah diberikan.');
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
                    btnRetakeOut.style.display  = 'inline-flex';
                    if (btnSubmitOut) btnSubmitOut.disabled = false;
                }
            } catch (error) {
                console.error('Error watermarking check-out photo:', error);
                alert('Gagal memproses foto. Silakan coba kembali.');
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

    // ─────────────────────────────────────────────
    // SUBMIT CHECK-OUT (now requires selfie)
    // ─────────────────────────────────────────────
    if (btnSubmitOut) {
        btnSubmitOut.addEventListener('click', async () => {
            // If checkout camera UI exists, selfie is mandatory
            if (btnStartCameraOut && !capturedOutBase64) {
                alert('Silakan ambil foto selfie untuk absen pulang terlebih dahulu.');
                return;
            }
            if (!userCoordinates) {
                alert('Sedang mendeteksi lokasi, silakan tunggu sebentar.');
                return;
            }
            if (!confirm('Apakah Anda yakin ingin melakukan absen pulang?')) return;

            btnSubmitOut.disabled = true;
            btnSubmitOut.textContent = 'Memproses...';

            try {
                const response = await fetch('/peserta/attendance/check-out', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        koordinat: userCoordinates,
                        foto: capturedOutBase64
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal melakukan absen pulang.');
                    btnSubmitOut.disabled = false;
                    btnSubmitOut.textContent = 'Absen Pulang';
                }
            } catch (error) {
                console.error('Error during check-out:', error);
                alert('Terjadi kesalahan sistem. Silakan coba kembali.');
                btnSubmitOut.disabled = false;
                btnSubmitOut.textContent = 'Absen Pulang';
            }
        });
    }

    // ===== Modal Add Logbook =====
    const modalAddLogbook = document.getElementById('modal-add-logbook');
    const btnOpenAddLogbook = document.getElementById('open-add-logbook-modal');
    const btnCloseAddLogbook = document.getElementById('close-add-logbook-modal');
    const btnCancelAddLogbook = document.getElementById('cancel-add-logbook-modal');

    const toggleAddLogbookModal = (show) => {
        if (modalAddLogbook) {
            modalAddLogbook.classList.toggle('is-open', show);
        }
    };

    btnOpenAddLogbook?.addEventListener('click', () => toggleAddLogbookModal(true));
    btnCloseAddLogbook?.addEventListener('click', () => toggleAddLogbookModal(false));
    btnCancelAddLogbook?.addEventListener('click', () => toggleAddLogbookModal(false));

    // Close on clicking backdrop
    modalAddLogbook?.addEventListener('click', (e) => {
        if (e.target === modalAddLogbook) {
            toggleAddLogbookModal(false);
        }
    });

    // ===== Modal Add Leave Request =====
    const modalAddLeave = document.getElementById('modal-add-leave');
    const btnOpenAddLeave = document.getElementById('open-add-leave-modal');
    const btnCloseAddLeave = document.getElementById('close-add-leave-modal');
    const btnCancelAddLeave = document.getElementById('cancel-add-leave-modal');

    const toggleAddLeaveModal = (show) => {
        if (modalAddLeave) {
            modalAddLeave.classList.toggle('is-open', show);
        }
    };

    btnOpenAddLeave?.addEventListener('click', () => toggleAddLeaveModal(true));
    btnCloseAddLeave?.addEventListener('click', () => toggleAddLeaveModal(false));
    btnCancelAddLeave?.addEventListener('click', () => toggleAddLeaveModal(false));

    modalAddLeave?.addEventListener('click', (e) => {
        if (e.target === modalAddLeave) {
            toggleAddLeaveModal(false);
        }
    });

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
});

