document.addEventListener('DOMContentLoaded', () => {
    // ===== Database Schedule Data Passed to JS =====
    const globalDefaultSettings = window.settingsConfig.globalDefaultSettings;
    const rawDayOverrides = window.settingsConfig.dayOverrides;
    const rawDateOverrides = window.settingsConfig.dateOverrides;

    const dayOverrides = {};
    rawDayOverrides.forEach(ovr => {
        dayOverrides[ovr.day_of_week] = {
            id: ovr.id,
            jam_masuk: ovr.jam_masuk ? ovr.jam_masuk.substring(0, 5) : null,
            batas_keterlambatan: ovr.batas_keterlambatan ? ovr.batas_keterlambatan.substring(0, 5) : null,
            jam_pulang: ovr.jam_pulang ? ovr.jam_pulang.substring(0, 5) : null,
            is_holiday: ovr.is_holiday == 1,
            keterangan: ovr.keterangan || ''
        };
    });

    const dateOverrides = {};
    rawDateOverrides.forEach(ovr => {
        if (ovr.specific_date) {
            const dateKey = ovr.specific_date.substring(0, 10);
            dateOverrides[dateKey] = {
                id: ovr.id,
                jam_masuk: ovr.jam_masuk ? ovr.jam_masuk.substring(0, 5) : null,
                batas_keterlambatan: ovr.batas_keterlambatan ? ovr.batas_keterlambatan.substring(0, 5) : null,
                jam_pulang: ovr.jam_pulang ? ovr.jam_pulang.substring(0, 5) : null,
                is_holiday: ovr.is_holiday == 1,
                keterangan: ovr.keterangan || ''
            };
        }
    });

    // ===== Calendar Rendering Logic =====
    let currentCalDate = new Date();
    
    function renderCalendar() {
        const year = currentCalDate.getFullYear();
        const month = currentCalDate.getMonth();
        
        const monthNames = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];
        document.getElementById('calendar-month-year-label').textContent = `${monthNames[month]} ${year}`;
        
        const container = document.getElementById('calendar-days-container');
        if (!container) return;
        container.innerHTML = '';
        
        const firstDayIndex = new Date(year, month, 1).getDay();
        const totalDays = new Date(year, month + 1, 0).getDate();
        
        for (let i = 0; i < firstDayIndex; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'calendar-cell cell-empty';
            container.appendChild(emptyCell);
        }
        
        for (let day = 1; day <= totalDays; day++) {
            const dateObj = new Date(year, month, day);
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const dayOfWeek = dateObj.getDay();
            
            const cell = document.createElement('div');
            cell.className = 'calendar-cell';
            if (dayOfWeek === 0) {
                cell.classList.add('cell-sunday');
            }
            
            let statusClass = 'cell-status-default';
            let infoText = `${globalDefaultSettings.jam_masuk} - ${globalDefaultSettings.jam_pulang}`;
            let isHoliday = false;
            let overrideId = '';
            let jamMasuk = globalDefaultSettings.jam_masuk;
            let batas = globalDefaultSettings.batas_keterlambatan;
            let jamPulang = globalDefaultSettings.jam_pulang;
            let keterangan = '';
            
            if (dateOverrides[dateStr]) {
                const ovr = dateOverrides[dateStr];
                overrideId = ovr.id;
                isHoliday = ovr.is_holiday;
                keterangan = ovr.keterangan || '';
                
                if (isHoliday) {
                    statusClass = 'cell-status-holiday';
                    infoText = 'Libur';
                } else {
                    statusClass = 'cell-status-date';
                    jamMasuk = ovr.jam_masuk;
                    batas = ovr.batas_keterlambatan;
                    jamPulang = ovr.jam_pulang;
                    infoText = `${jamMasuk} - ${jamPulang}`;
                }
            }
            else if (dayOverrides[dayOfWeek]) {
                const ovr = dayOverrides[dayOfWeek];
                overrideId = ovr.id;
                isHoliday = ovr.is_holiday;
                keterangan = ovr.keterangan || '';
                
                if (isHoliday) {
                    statusClass = 'cell-status-holiday';
                    infoText = 'Libur';
                } else {
                    statusClass = 'cell-status-day';
                    jamMasuk = ovr.jam_masuk;
                    batas = ovr.batas_keterlambatan;
                    jamPulang = ovr.jam_pulang;
                    infoText = `${jamMasuk} - ${jamPulang}`;
                }
            }
            else if (dayOfWeek === 0) {
                statusClass = 'cell-status-holiday';
                infoText = 'Libur';
                keterangan = 'Minggu Libur';
                isHoliday = true;
            }
            
            cell.classList.add(statusClass);
            
            let infoHTML = '';
            if (isHoliday) {
                infoHTML = `<div class="cell-info" title="Libur">Libur</div>`;
            } else {
                infoHTML = `
                    <div class="cell-info" title="${infoText}">
                        <span class="time-start">${jamMasuk.substring(0, 5)}</span>
                        <span class="time-dash">-</span>
                        <span class="time-end">${jamPulang.substring(0, 5)}</span>
                    </div>
                `;
            }
            
            cell.innerHTML = `
                <span class="cell-number">${day}</span>
                <div style="display: flex; flex-direction: column; width: 100%; align-items: center; justify-content: flex-end; flex-grow: 1; min-height: 42px;">
                    ${infoHTML}
                    ${keterangan ? `<div class="cell-desc" title="${keterangan}">${keterangan}</div>` : ''}
                </div>
            `;
            
            cell.addEventListener('click', () => {
                const modal = document.getElementById('date-override-modal');
                const form = document.getElementById('date-override-form');
                if (!modal || !form) return;
                
                document.getElementById('date-modal-title').textContent = isHoliday && dateOverrides[dateStr] ? 'Edit Tanggal Khusus' : 'Atur Tanggal Khusus / Libur';
                
                document.getElementById('date-form-date').value = dateStr;
                document.getElementById('date-form-date').readOnly = true;
                document.getElementById('date-form-date-group').style.display = 'block';
                
                document.getElementById('date-form-masuk').value = isHoliday ? globalDefaultSettings.jam_masuk : jamMasuk;
                document.getElementById('date-form-batas').value = isHoliday ? globalDefaultSettings.batas_keterlambatan : batas;
                document.getElementById('date-form-pulang').value = isHoliday ? globalDefaultSettings.jam_pulang : jamPulang;
                
                document.getElementById('date-form-holiday').checked = isHoliday;
                document.getElementById('date-form-keterangan').value = keterangan;
                toggleDateTimeInputs();
                
                if (dateOverrides[dateStr]) {
                    form.action = `/super-admin/schedules/${overrideId}`;
                    document.getElementById('date-form-method').value = 'PUT';
                } else {
                    form.action = window.settingsConfig.routes.storeSchedule;
                    document.getElementById('date-form-method').value = 'POST';
                }
                
                modal.style.display = 'flex';
            });
            
            container.appendChild(cell);
        }
    }
    
    renderCalendar();
    
    document.getElementById('calendar-prev-month')?.addEventListener('click', () => {
        currentCalDate.setMonth(currentCalDate.getMonth() - 1);
        renderCalendar();
    });
    
    document.getElementById('calendar-next-month')?.addEventListener('click', () => {
        currentCalDate.setMonth(currentCalDate.getMonth() + 1);
        renderCalendar();
    });

    // ===== Tab switching =====
    document.querySelectorAll('.schedule-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.schedule-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });

    // ===== Day Override Modal =====
    document.querySelectorAll('.btn-day-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById('day-override-modal');
            const form = document.getElementById('day-override-form');
            const overrideId = btn.dataset.overrideId;

            document.getElementById('day-modal-title').textContent = 'Edit Jadwal : ' + btn.dataset.dayName;
            document.getElementById('day-form-day').value = btn.dataset.day;
            document.getElementById('day-form-masuk').value = btn.dataset.jamMasuk;
            document.getElementById('day-form-batas').value = btn.dataset.batas;
            document.getElementById('day-form-pulang').value = btn.dataset.jamPulang;
            document.getElementById('day-form-keterangan').value = btn.dataset.keterangan;

            const holidayCheckbox = document.getElementById('day-form-holiday');
            holidayCheckbox.checked = btn.dataset.isHoliday === '1';
            toggleDayTimeInputs();

            if (overrideId) {
                form.action = '/super-admin/schedules/' + overrideId;
                document.getElementById('day-form-method').value = 'PUT';
            } else {
                form.action = window.settingsConfig.routes.storeSchedule;
                document.getElementById('day-form-method').value = 'POST';
            }

            modal.style.display = 'flex';
        });
    });

    // ===== Date Override Modal =====
    document.getElementById('btn-add-date-override')?.addEventListener('click', () => {
        const modal = document.getElementById('date-override-modal');
        const form = document.getElementById('date-override-form');

        document.getElementById('date-modal-title').textContent = 'Tambah Tanggal Khusus';
        form.action = window.settingsConfig.routes.storeSchedule;
        document.getElementById('date-form-method').value = 'POST';
        document.getElementById('date-form-date').value = '';
        document.getElementById('date-form-date').readOnly = false;
        document.getElementById('date-form-date-group').style.display = 'block';
        document.getElementById('date-form-masuk').value = globalDefaultSettings.jam_masuk;
        document.getElementById('date-form-batas').value = globalDefaultSettings.batas_keterlambatan;
        document.getElementById('date-form-pulang').value = globalDefaultSettings.jam_pulang;
        document.getElementById('date-form-holiday').checked = false;
        document.getElementById('date-form-keterangan').value = '';
        toggleDateTimeInputs();

        modal.style.display = 'flex';
    });

    document.querySelectorAll('.btn-date-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById('date-override-modal');
            const form = document.getElementById('date-override-form');

            document.getElementById('date-modal-title').textContent = 'Edit Tanggal Khusus';
            form.action = '/super-admin/schedules/' + btn.dataset.id;
            document.getElementById('date-form-method').value = 'PUT';
            document.getElementById('date-form-date').value = btn.dataset.date;
            document.getElementById('date-form-date').readOnly = true;
            document.getElementById('date-form-masuk').value = btn.dataset.jamMasuk;
            document.getElementById('date-form-batas').value = btn.dataset.batas;
            document.getElementById('date-form-pulang').value = btn.dataset.jamPulang;
            document.getElementById('date-form-holiday').checked = btn.dataset.isHoliday === '1';
            document.getElementById('date-form-keterangan').value = btn.dataset.keterangan;
            toggleDateTimeInputs();

            modal.style.display = 'flex';
        });
    });

    // ===== Leaflet Map (preserved from before) =====
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
            window.Swal.fire({ ...getSwalColors(), icon, title, text, confirmButtonText: 'Mengerti' });
        } else {
            alert(text);
        }
    };

    const map = L.map('map').setView([defaultLat, defaultLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
    let circle = L.circle([defaultLat, defaultLng], {
        color: '#10b981', fillColor: '#10b981', fillOpacity: 0.15, radius: defaultRadius
    }).addTo(map);

    function updateCoordinates(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        circle.setLatLng([lat, lng]);
    }

    marker.on('drag', () => { const p = marker.getLatLng(); updateCoordinates(p.lat, p.lng); });
    marker.on('dragend', () => { const p = marker.getLatLng(); updateCoordinates(p.lat, p.lng); map.panTo(p); });
    map.on('click', (e) => { marker.setLatLng(e.latlng); updateCoordinates(e.latlng.lat, e.latlng.lng); });
    radiusInput.addEventListener('input', () => { circle.setRadius(parseInt(radiusInput.value) || 0); });

    // Search
    const searchInput = document.getElementById('map-search-input');
    const searchBtn = document.getElementById('btn-map-search');
    function performSearch() {
        const query = searchInput.value.trim();
        if (!query) return;
        searchBtn.disabled = true; searchBtn.textContent = 'Mencari...';
        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`)
            .then(r => r.json()).then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
                    marker.setLatLng([lat, lng]); map.setView([lat, lng], 16); updateCoordinates(lat, lng);
                } else { showMapAlert('warning', 'Lokasi Tidak Ditemukan', 'Coba masukkan kata kunci yang lebih spesifik.'); }
            }).catch(() => { showMapAlert('error', 'Koneksi Gagal', 'Terjadi kesalahan koneksi saat mencari lokasi.'); })
            .finally(() => { searchBtn.disabled = false; searchBtn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Cari`; });
    }
    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); performSearch(); } });

    // GPS
    const gpsBtn = document.getElementById('btn-map-gps');
    gpsBtn.addEventListener('click', () => {
        if (!navigator.geolocation) { showMapAlert('error', 'GPS Tidak Didukung', 'Browser Anda tidak mendukung layanan lokasi GPS.'); return; }
        gpsBtn.disabled = true; gpsBtn.textContent = 'Mendeteksi...';
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude, lng = pos.coords.longitude;
                marker.setLatLng([lat, lng]); map.setView([lat, lng], 17); updateCoordinates(lat, lng);
                gpsBtn.disabled = false; gpsBtn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> GPS`;
            },
            () => {
                showMapAlert('warning', 'GPS Gagal', 'Gagal mendeteksi lokasi GPS Anda. Pastikan izin lokasi telah aktif.');
                gpsBtn.disabled = false; gpsBtn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> GPS`;
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
        );
    });

    // ===== Pagination for Specific Dates Table =====
    const tableBody = document.getElementById('table-date-body');
    const paginationContainer = document.getElementById('table-date-pagination');
    
    if (tableBody && paginationContainer) {
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        const itemsPerPage = 5;
        const totalItems = rows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        let currentTablePage = 1;
        
        if (totalItems <= itemsPerPage) {
            paginationContainer.style.display = 'none';
        } else {
            const prevBtn = document.getElementById('btn-date-prev');
            const nextBtn = document.getElementById('btn-date-next');
            const infoLabel = document.getElementById('date-pagination-info');
            
            function showTablePage(page) {
                currentTablePage = page;
                
                rows.forEach((row, idx) => {
                    const startIdx = (page - 1) * itemsPerPage;
                    const endIdx = page * itemsPerPage;
                    if (idx >= startIdx && idx < endIdx) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                const startNum = (page - 1) * itemsPerPage + 1;
                const endNum = Math.min(page * itemsPerPage, totalItems);
                infoLabel.textContent = `Menampilkan ${startNum} - ${endNum} dari ${totalItems} data`;
                
                prevBtn.disabled = (page === 1);
                nextBtn.disabled = (page === totalPages);
                
                prevBtn.style.opacity = (page === 1) ? '0.5' : '1';
                prevBtn.style.cursor = (page === 1) ? 'not-allowed' : 'pointer';
                nextBtn.style.opacity = (page === totalPages) ? '0.5' : '1';
                nextBtn.style.cursor = (page === totalPages) ? 'not-allowed' : 'pointer';
            }
            
            prevBtn.addEventListener('click', () => {
                if (currentTablePage > 1) {
                    showTablePage(currentTablePage - 1);
                }
            });
            
            nextBtn.addEventListener('click', () => {
                if (currentTablePage < totalPages) {
                    showTablePage(currentTablePage + 1);
                }
            });
            
            showTablePage(1);
        }
    }
});

// ===== Toggle helpers =====
window.toggleDayTimeInputs = function() {
    const isHoliday = document.getElementById('day-form-holiday').checked;
    document.getElementById('day-time-inputs').style.display = isHoliday ? 'none' : 'block';
}

window.toggleDateTimeInputs = function() {
    const isHoliday = document.getElementById('date-form-holiday').checked;
    document.getElementById('date-time-inputs').style.display = isHoliday ? 'none' : 'block';
}

// ===== Delete confirmation with SweetAlert2 =====
window.handleDayDelete = async function(event) {
    event.preventDefault();
    if (window.confirmDangerAction) {
        const confirmed = await window.confirmDangerAction({
            title: 'Reset Jadwal Hari Ini?',
            text: 'Override akan dihapus dan hari ini akan kembali mengikuti jadwal default.',
            confirmButtonText: 'Ya, Reset',
        });
        if (confirmed) event.target.submit();
    } else {
        event.target.submit();
    }
    return false;
}

window.handleDateDelete = async function(event) {
    event.preventDefault();
    if (window.confirmDangerAction) {
        const confirmed = await window.confirmDangerAction({
            title: 'Hapus Tanggal Khusus?',
            text: 'Override untuk tanggal ini akan dihapus permanen.',
            confirmButtonText: 'Ya, Hapus',
        });
        if (confirmed) event.target.submit();
    } else {
        event.target.submit();
    }
    return false;
}
