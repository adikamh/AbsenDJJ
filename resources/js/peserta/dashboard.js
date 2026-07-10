// Page-specific scripts for dashboard/peserta.blade.php.

document.addEventListener('DOMContentLoaded', () => {
    // Detect user's geolocation for attendance panel
    const locationEl = document.getElementById('location-coordinate');
    let userCoordinates = '';

    if (locationEl && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude.toFixed(6);
                const lng = pos.coords.longitude.toFixed(6);
                userCoordinates = `${lat}, ${lng}`;
                locationEl.textContent = userCoordinates;
            },
            () => {
                locationEl.textContent = 'Lokasi tidak dapat dideteksi';
                userCoordinates = '-6.914744, 107.625680'; // Fallback coordinate (e.g., Bandung center/ITENAS)
            },
            { timeout: 10000, enableHighAccuracy: true }
        );
    } else if (locationEl) {
        locationEl.textContent = 'Browser tidak mendukung GPS';
        userCoordinates = '-6.914744, 107.625680';
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

    let stream = null;
    let capturedSelfieBase64 = null;

    // Preload Logo PU
    const puLogo = new Image();
    puLogo.src = '/images/Logo/Logo_PU.png';

    // Start Webcam
    if (btnStartCamera) {
        btnStartCamera.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    },
                    audio: false
                });
                webcamVideo.srcObject = stream;
                webcamVideo.style.display = 'block';
                selfiePreview.style.display = 'none';
                
                btnStartCamera.style.display = 'none';
                btnCapturePhoto.style.display = 'inline-flex';
                btnRetakePhoto.style.display = 'none';
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
        btnCapturePhoto.addEventListener('click', () => {
            if (!webcamVideo || !canvas) return;

            const context = canvas.getContext('2d');
            
            // Set canvas size matching the webcam video frame
            const width = webcamVideo.videoWidth || 640;
            const height = webcamVideo.videoHeight || 480;
            canvas.width = width;
            canvas.height = height;

            // Draw video frame (mirrored horizontal scale for standard selfie viewing)
            context.translate(width, 0);
            context.scale(-1, 1);
            context.drawImage(webcamVideo, 0, 0, width, height);
            
            // Reset transformation before drawing overlays
            context.setTransform(1, 0, 0, 1, 0, 0);

            // Calculate margins and sizes based on canvas dimension
            const margin = Math.round(width * 0.03);
            
            // 1. Draw PU Logo in bottom-right corner
            const logoWidth = Math.round(width * 0.12);
            const logoHeight = logoWidth; // Square logo
            const logoX = width - logoWidth - margin;
            const logoY = height - logoHeight - margin;

            if (puLogo.complete) {
                context.drawImage(puLogo, logoX, logoY, logoWidth, logoHeight);
            } else {
                puLogo.onload = () => {
                    context.drawImage(puLogo, logoX, logoY, logoWidth, logoHeight);
                };
            }

            // 2. Draw Timestamp in bottom-left corner
            const now = new Date();
            const pad = (n) => n.toString().padStart(2, '0');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const timestampStr = `${pad(now.getDate())} ${months[now.getMonth()]} ${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

            const fontSize = Math.max(13, Math.round(width * 0.03));
            context.font = `bold ${fontSize}px 'Outfit', 'Inter', sans-serif`;
            context.fillStyle = '#ffcc33'; // PU yellow/gold color
            
            // Text shadow/stroke outline for best readability
            context.shadowColor = 'rgba(0, 0, 0, 0.8)';
            context.shadowBlur = 4;
            context.lineWidth = 3;
            context.strokeStyle = 'rgba(15, 23, 42, 0.9)';
            context.strokeText(timestampStr, margin, height - margin);
            
            context.shadowBlur = 0; // reset shadow
            context.fillText(timestampStr, margin, height - margin);

            // Convert to base64 jpeg
            capturedSelfieBase64 = canvas.toDataURL('image/jpeg', 0.9);

            // Update UI preview
            selfiePreview.src = capturedSelfieBase64;
            selfiePreview.style.display = 'block';
            
            stopCamera();

            btnCapturePhoto.style.display = 'none';
            btnRetakePhoto.style.display = 'inline-flex';
            btnSubmitIn.disabled = false;
        });
    }

    // Retake Photo
    if (btnRetakePhoto) {
        btnRetakePhoto.addEventListener('click', () => {
            capturedSelfieBase64 = null;
            selfiePreview.style.display = 'none';
            btnSubmitIn.disabled = true;
            btnRetakePhoto.style.display = 'none';
            
            // Restart camera
            btnStartCamera.click();
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

    // Start checkout webcam
    if (btnStartCameraOut) {
        btnStartCameraOut.addEventListener('click', async () => {
            try {
                streamOut = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false
                });
                webcamVideoOut.srcObject = streamOut;
                webcamVideoOut.style.display = 'block';
                selfiePreviewOut.style.display = 'none';

                btnStartCameraOut.style.display = 'none';
                btnCaptureOut.style.display = 'inline-flex';
                btnRetakeOut.style.display = 'none';
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
        btnCaptureOut.addEventListener('click', () => {
            if (!webcamVideoOut || !canvasOut) return;

            const ctx = canvasOut.getContext('2d');
            const width  = webcamVideoOut.videoWidth  || 640;
            const height = webcamVideoOut.videoHeight || 480;
            canvasOut.width  = width;
            canvasOut.height = height;

            // Mirror-draw frame
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(webcamVideoOut, 0, 0, width, height);
            ctx.setTransform(1, 0, 0, 1, 0, 0);

            const margin = Math.round(width * 0.03);

            // 1. PU Logo — bottom-right
            const logoWidth  = Math.round(width * 0.12);
            const logoHeight = logoWidth;
            const logoX = width  - logoWidth  - margin;
            const logoY = height - logoHeight - margin;

            if (puLogo.complete) {
                ctx.drawImage(puLogo, logoX, logoY, logoWidth, logoHeight);
            } else {
                puLogo.onload = () => ctx.drawImage(puLogo, logoX, logoY, logoWidth, logoHeight);
            }

            // 2. Timestamp — bottom-left
            const now = new Date();
            const pad = n => n.toString().padStart(2, '0');
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            const ts = `${pad(now.getDate())} ${months[now.getMonth()]} ${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

            const fontSize = Math.max(13, Math.round(width * 0.03));
            ctx.font        = `bold ${fontSize}px 'Outfit', 'Inter', sans-serif`;
            ctx.fillStyle   = '#ffcc33';
            ctx.shadowColor = 'rgba(0,0,0,0.8)';
            ctx.shadowBlur  = 4;
            ctx.lineWidth   = 3;
            ctx.strokeStyle = 'rgba(15,23,42,0.9)';
            ctx.strokeText(ts, margin, height - margin);
            ctx.shadowBlur = 0;
            ctx.fillText(ts, margin, height - margin);

            capturedOutBase64 = canvasOut.toDataURL('image/jpeg', 0.9);

            selfiePreviewOut.src = capturedOutBase64;
            selfiePreviewOut.style.display = 'block';

            stopCameraOut();

            btnCaptureOut.style.display = 'none';
            btnRetakeOut.style.display  = 'inline-flex';
            if (btnSubmitOut) btnSubmitOut.disabled = false;
        });
    }

    // Retake checkout photo
    if (btnRetakeOut) {
        btnRetakeOut.addEventListener('click', () => {
            capturedOutBase64 = null;
            selfiePreviewOut.style.display = 'none';
            if (btnSubmitOut) btnSubmitOut.disabled = true;
            btnRetakeOut.style.display = 'none';
            btnStartCameraOut.click();
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
});

