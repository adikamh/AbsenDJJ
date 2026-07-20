document.addEventListener('DOMContentLoaded', () => {
    // ===== Initialize beautiful Flatpickr time pickers =====
    if (window.flatpickr) {
        window.flatpickr(".time-picker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            allowInput: true,
            disableMobile: true
        });
    }

    // ===== Frontend Security: Sanitize keterangan inputs in real-time =====
    // Allowed: letters, numbers, spaces, and: - / \ " ' & ( ) . ,
    // Blocked: < > ; = { } | ^ ` ~ # % + * ! @ $ ? [ ] and others
    const KETERANGAN_PATTERN = /[^a-zA-Z0-9\s\-\/\\\(\)\.,\'"&]/g;
    const MAX_KETERANGAN_LENGTH = 170;

    /**
     * Attach sanitizer and char counter to a keterangan input field.
     */
    function initKeteranganInput(inputEl) {
        if (!inputEl) return;

        // Create char counter element
        const counter = document.createElement('div');
        counter.style.cssText = 'font-size:0.73rem;text-align:right;margin-top:4px;transition:color .2s;';
        counter.style.color = 'var(--text-secondary)';
        inputEl.parentNode.insertBefore(counter, inputEl.nextSibling);

        function updateCounter() {
            const len = inputEl.value.length;
            const remaining = MAX_KETERANGAN_LENGTH - len;
            counter.textContent = `${len} / ${MAX_KETERANGAN_LENGTH} karakter`;
            counter.style.color = remaining <= 20
                ? (remaining <= 0 ? '#f87171' : '#fbbf24')
                : 'var(--text-secondary)';
        }

        inputEl.addEventListener('input', () => {
            // Strip forbidden characters instantly
            const cleaned = inputEl.value.replace(KETERANGAN_PATTERN, '');
            if (cleaned !== inputEl.value) {
                const pos = inputEl.selectionStart - (inputEl.value.length - cleaned.length);
                inputEl.value = cleaned;
                inputEl.setSelectionRange(pos, pos);
            }
            updateCounter();
        });

        inputEl.addEventListener('paste', () => {
            // Sanitize on paste (runs after value is set)
            setTimeout(() => {
                inputEl.value = inputEl.value.replace(KETERANGAN_PATTERN, '').substring(0, MAX_KETERANGAN_LENGTH);
                updateCounter();
            }, 0);
        });

        updateCounter();
    }

    // Attach to all keterangan inputs on page
    ['day-form-keterangan', 'date-form-keterangan'].forEach(id => {
        initKeteranganInput(document.getElementById(id));
    });

    // ===== Database Schedule Data Passed to JS =====
    const configEl = document.getElementById('settings-config');
    let globalDefaultSettings = {};
    let rawDayOverrides = [];
    let rawDateOverrides = [];
    let routes = {};
    let csrfToken = '';

    if (configEl) {
        try {
            globalDefaultSettings = JSON.parse(configEl.getAttribute('data-global-default-settings') || '{}');
            rawDayOverrides = JSON.parse(configEl.getAttribute('data-day-overrides') || '[]');
            rawDateOverrides = JSON.parse(configEl.getAttribute('data-date-overrides') || '[]');
            routes = {
                storeSchedule: configEl.getAttribute('data-routes-store-schedule') || '',
                syncHolidays: configEl.getAttribute('data-routes-sync-holidays') || ''
            };
            csrfToken = configEl.getAttribute('data-csrf-token') || '';
        } catch (e) {
            console.error('Error parsing settings config:', e);
        }
    }

    window.settingsConfig = {
        globalDefaultSettings,
        dayOverrides: rawDayOverrides,
        dateOverrides: rawDateOverrides,
        routes,
        csrfToken
    };


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
        const label = document.getElementById('calendar-month-year-label');
        const container = document.getElementById('calendar-days-container');
        if (!label || !container) return;

        label.textContent = `${monthNames[month]} ${year}`;
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
                // Keep overrideId empty because this is a day-level override, not a specific date-level override.
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


            cell.classList.add(statusClass);

            let infoHTML = '';
            if (isHoliday) {
                infoHTML = `<div class="cell-info" title="Libur">Libur</div>`;
            } else {
                const displayJamMasuk = (jamMasuk || globalDefaultSettings.jam_masuk || '--:--').substring(0, 5);
                const displayJamPulang = (jamPulang || globalDefaultSettings.jam_pulang || '--:--').substring(0, 5);
                infoHTML = `
                    <div class="cell-info" title="${infoText}">
                        <span class="time-start">${displayJamMasuk}</span>
                        <span class="time-dash">-</span>
                        <span class="time-end">${displayJamPulang}</span>
                    </div>
                `;
            }

            const isHolidayClassChecked = isHoliday ? 'checked' : '';
            const toggleHTML = `
                <label class="switch-toggle cell-holiday-toggle" title="Tandai sebagai Hari Libur" style="position: absolute; top: 6px; right: 6px; transform: scale(0.75); transform-origin: top right;" onclick="event.stopPropagation()">
                    <input type="checkbox" class="js-cell-holiday-toggle" 
                           data-date="${dateStr}" 
                           data-override-id="${overrideId}"
                           data-keterangan="${keterangan}"
                           ${isHolidayClassChecked}>
                    <span class="switch-slider"></span>
                </label>
            `;

            cell.innerHTML = `
                <span class="cell-number">${day}</span>
                ${toggleHTML}
                <div style="display: flex; flex-direction: column; width: 100%; align-items: center; justify-content: flex-end; flex-grow: 1; min-height: 42px;">
                    ${infoHTML}
                    ${keterangan ? `<div class="cell-desc" title="${keterangan}">${keterangan}</div>` : ''}
                </div>
            `;

            const cellToggle = cell.querySelector('.js-cell-holiday-toggle');
            if (cellToggle) {
                cellToggle.addEventListener('change', (e) => {
                    e.stopPropagation();
                    const dateStr = cellToggle.dataset.date;
                    const overrideId = cellToggle.dataset.overrideId;
                    const keterangan = cellToggle.dataset.keterangan || '';
                    const isChecked = cellToggle.checked;

                    // Resolve day of week to check if it's globally a holiday
                    const dateParts = dateStr.split('-');
                    const parsedDate = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));
                    const dayOfWeek = parsedDate.getDay();
                    const isWeeklyHoliday = dayOverrides[dayOfWeek] && dayOverrides[dayOfWeek].is_holiday;

                    if (isChecked) {
                        // User wants to mark it as HOLIDAY
                        if (isWeeklyHoliday) {
                            // Saturday is already globally a holiday.
                            // If there was a date override making it a workday, we just DELETE that override to revert Saturday back to holiday!
                            if (window.Swal) {
                                window.Swal.fire({
                                    title: 'Tandai Tanggal Libur?',
                                    text: `Mengembalikan tanggal ${dateStr} ke jadwal default Sabtu Libur.`,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Ya, Kembalikan',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        if (overrideId) {
                                            submitDeleteScheduleOverride(overrideId);
                                        } else {
                                            location.reload();
                                        }
                                    } else {
                                        cellToggle.checked = false;
                                    }
                                });
                            } else {
                                if (confirm(`Kembalikan tanggal ${dateStr} ke jadwal default Libur?`)) {
                                    if (overrideId) {
                                        submitDeleteScheduleOverride(overrideId);
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    cellToggle.checked = false;
                                }
                            }
                        } else {
                            // Globally it's a workday (e.g. Monday). We need to CREATE or UPDATE a specific date override to be a holiday.
                            if (window.Swal) {
                                window.Swal.fire({
                                    title: 'Tandai Tanggal Libur?',
                                    text: `Tetapkan tanggal ${dateStr} sebagai hari libur? Seluruh absensi peserta pada hari ini akan dikunci.`,
                                    input: 'text',
                                    inputPlaceholder: 'Keterangan Hari Libur (opsional)',
                                    inputValue: keterangan,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Ya, Tetapkan Libur',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        submitScheduleOverrideViaForm({
                                            type: 'date',
                                            specific_date: dateStr,
                                            is_holiday: 1,
                                            keterangan: result.value || '',
                                            id: overrideId
                                        });
                                    } else {
                                        cellToggle.checked = false;
                                    }
                                });
                            } else {
                                const reason = prompt(`Tetapkan tanggal ${dateStr} sebagai hari libur? Keterangan (opsional):`);
                                if (reason !== null) {
                                    submitScheduleOverrideViaForm({
                                        type: 'date',
                                        specific_date: dateStr,
                                        is_holiday: 1,
                                        keterangan: reason,
                                        id: overrideId
                                    });
                                } else {
                                    cellToggle.checked = false;
                                }
                            }
                        }
                    } else {
                        // User wants to mark it as WORKDAY (not holiday)
                        if (isWeeklyHoliday) {
                            // Saturday is globally a holiday. We need to CREATE or UPDATE a specific date override to be a workday (is_holiday = 0).
                            if (window.Swal) {
                                window.Swal.fire({
                                    title: 'Jadikan Hari Kerja?',
                                    text: `Khusus tanggal ${dateStr}, jadikan hari kerja masuk (mengabaikan libur Sabtu)?`,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Ya, Jadikan Hari Kerja',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        submitScheduleOverrideViaForm({
                                            type: 'date',
                                            specific_date: dateStr,
                                            is_holiday: 0,
                                            jam_masuk: window.settingsConfig.globalDefaultSettings.jam_masuk,
                                            batas_keterlambatan: window.settingsConfig.globalDefaultSettings.batas_keterlambatan,
                                            jam_pulang: window.settingsConfig.globalDefaultSettings.jam_pulang,
                                            id: overrideId
                                        });
                                    } else {
                                        cellToggle.checked = true;
                                    }
                                });
                            } else {
                                if (confirm(`Khusus tanggal ${dateStr}, jadikan hari kerja masuk?`)) {
                                    submitScheduleOverrideViaForm({
                                        type: 'date',
                                        specific_date: dateStr,
                                        is_holiday: 0,
                                        jam_masuk: window.settingsConfig.globalDefaultSettings.jam_masuk,
                                        batas_keterlambatan: window.settingsConfig.globalDefaultSettings.batas_keterlambatan,
                                        jam_pulang: window.settingsConfig.globalDefaultSettings.jam_pulang,
                                        id: overrideId
                                    });
                                } else {
                                    cellToggle.checked = true;
                                }
                            }
                        } else {
                            // Globally it's a workday. If there's an override making it a holiday, we just DELETE it to revert back to default workday.
                            if (window.Swal) {
                                window.Swal.fire({
                                    title: 'Kembalikan Menjadi Hari Kerja?',
                                    text: `Menghapus status libur untuk tanggal ${dateStr}. Jadwal akan kembali ke default.`,
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#6b7280',
                                    confirmButtonText: 'Ya, Kembalikan',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        if (overrideId) {
                                            submitDeleteScheduleOverride(overrideId);
                                        } else {
                                            location.reload();
                                        }
                                    } else {
                                        cellToggle.checked = true;
                                    }
                                });
                            } else {
                                if (confirm(`Kembalikan tanggal ${dateStr} ke jadwal default?`)) {
                                    if (overrideId) {
                                        submitDeleteScheduleOverride(overrideId);
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    cellToggle.checked = true;
                                }
                            }
                        }
                    }
                });
            }

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
            
            const masukInput = document.getElementById('day-form-masuk');
            const batasInput = document.getElementById('day-form-batas');
            const pulangInput = document.getElementById('day-form-pulang');
            
            masukInput.value = btn.dataset.jamMasuk;
            batasInput.value = btn.dataset.batas;
            pulangInput.value = btn.dataset.jamPulang;

            masukInput._flatpickr?.setDate(btn.dataset.jamMasuk);
            batasInput._flatpickr?.setDate(btn.dataset.batas);
            pulangInput._flatpickr?.setDate(btn.dataset.jamPulang);

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

        const masukInput = document.getElementById('date-form-masuk');
        const batasInput = document.getElementById('date-form-batas');
        const pulangInput = document.getElementById('date-form-pulang');

        masukInput.value = globalDefaultSettings.jam_masuk;
        batasInput.value = globalDefaultSettings.batas_keterlambatan;
        pulangInput.value = globalDefaultSettings.jam_pulang;

        masukInput._flatpickr?.setDate(globalDefaultSettings.jam_masuk);
        batasInput._flatpickr?.setDate(globalDefaultSettings.batas_keterlambatan);
        pulangInput._flatpickr?.setDate(globalDefaultSettings.jam_pulang);

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

            const masukInput = document.getElementById('date-form-masuk');
            const batasInput = document.getElementById('date-form-batas');
            const pulangInput = document.getElementById('date-form-pulang');

            masukInput.value = btn.dataset.jamMasuk;
            batasInput.value = btn.dataset.batas;
            pulangInput.value = btn.dataset.jamPulang;

            masukInput._flatpickr?.setDate(btn.dataset.jamMasuk);
            batasInput._flatpickr?.setDate(btn.dataset.batas);
            pulangInput._flatpickr?.setDate(btn.dataset.jamPulang);

            document.getElementById('date-form-holiday').checked = btn.dataset.isHoliday === '1';
            document.getElementById('date-form-keterangan').value = btn.dataset.keterangan;
            toggleDateTimeInputs();

            modal.style.display = 'flex';
        });
    });

    // ===== Leaflet Map (preserved from before) =====
    const mapEl = document.getElementById('map');
    if (mapEl) {
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

        window.mapInstance = map;
        window.markerInstance = marker;
        window.circleInstance = circle;

        function updateCoordinates(lat, lng) {
            latInput.value = lat.toFixed(6);
            lngInput.value = lng.toFixed(6);
            circle.setLatLng([lat, lng]);
        }

        function syncMapFromInputs() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            const rad = parseInt(radiusInput.value) || 100;
            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                circle.setRadius(rad);
                map.setView([lat, lng], 16);
            }
        }

        // Listen to changes on inputs to sync map
        latInput.addEventListener('change', syncMapFromInputs);
        lngInput.addEventListener('change', syncMapFromInputs);

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
    }

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

    // ===== Weekly Day Card Holiday Toggle Switch =====
    document.querySelectorAll('.js-card-holiday-toggle').forEach(toggle => {
        toggle.addEventListener('change', (e) => {
            const dayIndex = toggle.dataset.day;
            const dayName = toggle.dataset.dayName;
            const overrideId = toggle.dataset.overrideId;
            const keterangan = toggle.dataset.keterangan || '';
            const isChecked = toggle.checked;

            if (isChecked) {
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Tandai Libur Hari ' + dayName + '?',
                        text: `Hari ini akan ditetapkan sebagai hari libur mingguan. Seluruh absensi peserta pada hari ini akan dikunci.`,
                        input: 'text',
                        inputPlaceholder: 'Keterangan Hari Libur (opsional)',
                        inputValue: keterangan,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Tetapkan Libur',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitScheduleOverrideViaForm({
                                type: 'day',
                                day_of_week: dayIndex,
                                is_holiday: 1,
                                keterangan: result.value || '',
                                id: overrideId
                            });
                        } else {
                            toggle.checked = false;
                        }
                    });
                } else {
                    const reason = prompt('Tetapkan hari ' + dayName + ' sebagai hari libur? Keterangan (opsional):');
                    if (reason !== null) {
                        submitScheduleOverrideViaForm({
                            type: 'day',
                            day_of_week: dayIndex,
                            is_holiday: 1,
                            keterangan: reason,
                            id: overrideId
                        });
                    } else {
                        toggle.checked = false;
                    }
                }
            } else {
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Kembalikan Menjadi Hari Kerja?',
                        text: 'Menghapus status libur untuk hari ' + dayName + '. Jadwal akan kembali ke default.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Kembalikan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (overrideId) {
                                submitDeleteScheduleOverride(overrideId);
                            } else {
                                submitScheduleOverrideViaForm({
                                    type: 'day',
                                    day_of_week: dayIndex,
                                    is_holiday: 0,
                                    jam_masuk: window.settingsConfig.globalDefaultSettings.jam_masuk,
                                    batas_keterlambatan: window.settingsConfig.globalDefaultSettings.batas_keterlambatan,
                                    jam_pulang: window.settingsConfig.globalDefaultSettings.jam_pulang,
                                    id: ''
                                });
                            }
                        } else {
                            toggle.checked = true;
                        }
                    });
                } else {
                    if (confirm('Hapus status libur untuk hari ' + dayName + '?')) {
                        if (overrideId) {
                            submitDeleteScheduleOverride(overrideId);
                        } else {
                            submitScheduleOverrideViaForm({
                                type: 'day',
                                day_of_week: dayIndex,
                                is_holiday: 0,
                                jam_masuk: window.settingsConfig.globalDefaultSettings.jam_masuk,
                                batas_keterlambatan: window.settingsConfig.globalDefaultSettings.batas_keterlambatan,
                                jam_pulang: window.settingsConfig.globalDefaultSettings.jam_pulang,
                                id: ''
                            });
                        }
                    } else {
                        toggle.checked = true;
                    }
                }
            }
        });
    });
});

// ===== Form Helper Submissions for Toggles =====
function submitScheduleOverrideViaForm(data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = data.id ? '/super-admin/schedules/' + data.id : window.settingsConfig.routes.storeSchedule;
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = window.settingsConfig.csrfToken;
    form.appendChild(csrfInput);
    
    if (data.id) {
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        form.appendChild(methodInput);
    }
    
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = data.type;
    form.appendChild(typeInput);
    
    if (data.day_of_week !== undefined && data.day_of_week !== null) {
        const dayInput = document.createElement('input');
        dayInput.type = 'hidden';
        dayInput.name = 'day_of_week';
        dayInput.value = data.day_of_week;
        form.appendChild(dayInput);
    }
    
    if (data.specific_date !== undefined && data.specific_date !== null) {
        const dateInput = document.createElement('input');
        dateInput.type = 'hidden';
        dateInput.name = 'specific_date';
        dateInput.value = data.specific_date;
        form.appendChild(dateInput);
    }
    
    const holidayInput = document.createElement('input');
    holidayInput.type = 'hidden';
    holidayInput.name = 'is_holiday';
    holidayInput.value = data.is_holiday;
    form.appendChild(holidayInput);
    
    if (data.keterangan !== undefined && data.keterangan !== null) {
        const descInput = document.createElement('input');
        descInput.type = 'hidden';
        descInput.name = 'keterangan';
        descInput.value = data.keterangan;
        form.appendChild(descInput);
    }
    
    document.body.appendChild(form);
    
    if (window.Swal) {
        window.Swal.fire({
            title: 'Menyimpan Perubahan...',
            text: 'Sedang memproses perubahan jadwal kerja.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                window.Swal.showLoading();
            }
        });
    }
    form.submit();
}

function submitDeleteScheduleOverride(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/super-admin/schedules/' + id;
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = window.settingsConfig.csrfToken;
    form.appendChild(csrfInput);
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    
    if (window.Swal) {
        window.Swal.fire({
            title: 'Menyimpan Perubahan...',
            text: 'Sedang menghapus override jadwal kerja.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                window.Swal.showLoading();
            }
        });
    }
    form.submit();
}

// ===== Toggle helpers =====
window.toggleDayTimeInputs = function () {
    const isHoliday = document.getElementById('day-form-holiday').checked;
    document.getElementById('day-time-inputs').style.display = isHoliday ? 'none' : 'block';
}

window.toggleDateTimeInputs = function () {
    const isHoliday = document.getElementById('date-form-holiday').checked;
    document.getElementById('date-time-inputs').style.display = isHoliday ? 'none' : 'block';
}

// ===== Delete confirmation with SweetAlert2 =====
window.handleDayDelete = async function (event) {
    event.preventDefault();
    if (window.confirmDangerAction) {
        const confirmed = await window.confirmDangerAction({
            title: 'Reset Jadwal Hari Ini?',
            text: 'Override akan dihapus dan hari ini akan kembali mengikuti jadwal default.',
            confirmButtonText: 'Ya, Reset',
        });
        if (confirmed) {
            showDeleteLoader();
            event.target.submit();
        }
    } else {
        showDeleteLoader();
        event.target.submit();
    }
    return false;
}

window.handleDateDelete = async function (event) {
    event.preventDefault();
    if (window.confirmDangerAction) {
        const confirmed = await window.confirmDangerAction({
            title: 'Hapus Tanggal Khusus?',
            text: 'Override untuk tanggal ini akan dihapus permanen.',
            confirmButtonText: 'Ya, Hapus',
        });
        if (confirmed) {
            showDeleteLoader();
            event.target.submit();
        }
    } else {
        showDeleteLoader();
        event.target.submit();
    }
    return false;
}

function showDeleteLoader() {
    if (window.Swal) {
        window.Swal.fire({
            title: 'Menghapus Data...',
            text: 'Sedang memproses penghapusan jadwal kerja dari database.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                window.Swal.showLoading();
            }
        });
    }
}
