@extends('dashboard.layout')

@section('title', 'Izin / Sakit')
@section('header_title', 'Pengajuan Izin / Sakit')

@push('styles')
    @vite(['resources/css/peserta/dashboard.css', 'resources/css/leave.css'])
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card hover-lift">
            <div class="stat-label">Izin Disetujui</div>
            <div class="stat-value" style="color: #fbbf24;">{{ $approvedIzinCount }} Pengajuan</div>
        </div>
        <div class="stat-card hover-lift">
            <div class="stat-label">Sakit Disetujui</div>
            <div class="stat-value" style="color: #f87171;">{{ $approvedSakitCount }} Pengajuan</div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">Riwayat Pengajuan Izin & Sakit</h2>
            <button type="button" class="btn-primary" id="open-add-leave-modal">
                Buat Pengajuan
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin: 15px 20px; padding: 12px 16px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Integrated Filter Form -->
        <form action="{{ route('peserta.leave') }}" method="GET" class="filter-row">
            <div class="filter-item">
                <label for="search">Cari</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" class="filter-search" placeholder="Cari alasan... (Tekan Enter)" onchange="this.form.submit()">
            </div>
            <div class="filter-item">
                <label for="jenis">Jenis</label>
                <select id="jenis" name="jenis" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="Izin" {{ request('jenis') === 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ request('jenis') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                </select>
            </div>
            <div class="filter-item">
                <label for="status_approval">Status</label>
                <select id="status_approval" name="status_approval" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Pending" {{ request('status_approval') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status_approval') === 'Approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Rejected" {{ request('status_approval') === 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            @if(request()->anyFilled(['search', 'jenis', 'status_approval']))
                <a href="{{ route('peserta.leave') }}" class="btn-filter-reset">Reset Filter</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Jenis</th>
                        <th>Alasan</th>
                        <th>Bukti Lampiran</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $index => $leave)
                        <tr>
                            <td>{{ $leaves->firstItem() + $index }}</td>
                            <td>{{ $leave->tanggal_mulai->format('d M Y') }}</td>
                            <td>{{ $leave->tanggal_selesai->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $leave->jenis === 'Sakit' ? 'badge-danger' : 'badge-warning' }}">
                                    {{ $leave->jenis }}
                                </span>
                            </td>
                            <td>
                                <div style="max-width: 300px; white-space: normal; word-break: break-all;">
                                    {{ $leave->alasan }}
                                </div>
                            </td>
                            <td>
                                @if($leave->file_bukti)
                                    <a href="{{ asset($leave->file_bukti) }}" target="_blank" class="document-link">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                        Lihat Berkas
                                    </a>
                                @else
                                    <span style="color: var(--text-secondary); font-size: 0.85rem;">Tidak ada bukti</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $leave->status_approval === 'Approved' ? 'badge-success' : ($leave->status_approval === 'Rejected' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $leave->status_approval }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">Belum ada pengajuan izin atau sakit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaves->hasPages())
            {{ $leaves->links('partials.pagination') }}
        @endif
    </div>

    {{-- ===== Modal: Pengajuan Izin / Sakit ===== --}}
    <div class="form-modal-backdrop" id="modal-add-leave">
        <div class="form-modal">
            <div class="form-modal-header">
                <h3>Ajukan Izin / Sakit</h3>
                <button type="button" class="modal-close" id="close-add-leave-modal">&times;</button>
            </div>
            <form action="{{ route('peserta.leave.store') }}" method="POST" class="modal-form" enctype="multipart/form-data">
                @csrf
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="tanggal_mulai">Tanggal Mulai <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_selesai">Tanggal Selesai <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="modal-jenis">Jenis Pengajuan <span style="color: #ef4444;">*</span></label>
                    <select id="modal-jenis" name="jenis" required>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alasan">Alasan Pengajuan <span style="color: #ef4444;">*</span></label>
                    <textarea id="alasan" name="alasan" rows="4" placeholder="Jelaskan alasan pengajuan izin atau sakit secara rinci..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="file_bukti">Dokumen Bukti (Surat Dokter / Lampiran)</label>
                    <input type="file" id="file_bukti" name="file_bukti" accept="image/*,application/pdf">
                    <span class="muted-small" style="font-size: 0.72rem; color: var(--text-secondary); display: block; margin-top: 4px;">Format: JPG, JPEG, PNG, atau PDF (Maks. 10MB). <span id="info-file-bukti" style="color: #ef4444; font-weight: 500; display: none;">*Wajib dilampirkan jika memilih Sakit.</span></span>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-add-leave-modal">Batal</button>
                    <button type="submit" class="btn-primary">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/leave.js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectJenis = document.getElementById('modal-jenis');
            const fileBukti = document.getElementById('file_bukti');
            const labelFileBukti = document.querySelector('label[for="file_bukti"]');
            const infoFileBukti = document.getElementById('info-file-bukti');

            const textareaAlasan = document.getElementById('alasan');
            const labelAlasan = document.querySelector('label[for="alasan"]');

            const tglMulai = document.getElementById('tanggal_mulai');
            const tglSelesai = document.getElementById('tanggal_selesai');

            const form = document.querySelector('#modal-add-leave form');

            function updateFileBuktiRequired() {
                if (!selectJenis || !fileBukti) return;
                if (selectJenis.value === 'Sakit') {
                    // Sakit: file_bukti is required, alasan is optional
                    fileBukti.setAttribute('required', 'required');
                    if (labelFileBukti) {
                        labelFileBukti.innerHTML = 'Dokumen Bukti (Surat Dokter / Lampiran) <span style="color: #ef4444;">*</span>';
                    }
                    if (infoFileBukti) {
                        infoFileBukti.style.display = 'inline';
                    }

                    if (textareaAlasan) {
                        textareaAlasan.removeAttribute('required');
                    }
                    if (labelAlasan) {
                        labelAlasan.innerHTML = 'Alasan Pengajuan';
                    }
                } else {
                    // Izin: alasan is required, file_bukti is optional
                    fileBukti.removeAttribute('required');
                    if (labelFileBukti) {
                        labelFileBukti.innerHTML = 'Dokumen Bukti (Surat Dokter / Lampiran)';
                    }
                    if (infoFileBukti) {
                        infoFileBukti.style.display = 'none';
                    }

                    if (textareaAlasan) {
                        textareaAlasan.setAttribute('required', 'required');
                    }
                    if (labelAlasan) {
                        labelAlasan.innerHTML = 'Alasan Pengajuan <span style="color: #ef4444;">*</span>';
                    }
                }
            }

            if (selectJenis) {
                selectJenis.addEventListener('change', updateFileBuktiRequired);
                updateFileBuktiRequired(); // run on load
            }

            // Real-time sanitizer to prevent < and > in Alasan
            if (textareaAlasan) {
                const cleanAlasan = () => {
                    if (/[<>]/g.test(textareaAlasan.value)) {
                        textareaAlasan.value = textareaAlasan.value.replace(/[<>]/g, '');
                    }
                };
                textareaAlasan.addEventListener('input', cleanAlasan);
                textareaAlasan.addEventListener('paste', () => setTimeout(cleanAlasan, 0));
            }

            function showErrorAlert(title, message) {
                if (window.Swal) {
                    window.Swal.fire({
                        background: document.documentElement.getAttribute('data-theme') === 'light' ? '#ffffff' : '#1e293b',
                        color: document.documentElement.getAttribute('data-theme') === 'light' ? '#0f172a' : '#f8fafc',
                        confirmButtonColor: '#2e4085',
                        icon: 'warning',
                        title: title,
                        text: message,
                        confirmButtonText: 'Mengerti'
                    });
                } else {
                    alert(title + ': ' + message);
                }
            }

            // File input format validation (on change)
            if (fileBukti) {
                fileBukti.addEventListener('change', () => {
                    const file = fileBukti.files[0];
                    if (file) {
                        const fileExtension = file.name.split('.').pop().toLowerCase();
                        const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                        
                        if (!allowedExtensions.includes(fileExtension)) {
                            showErrorAlert('Format Berkas Salah', 'Dokumen bukti hanya boleh berupa Gambar (JPG, JPEG, PNG) atau PDF. File Word (.doc/.docx) dilarang.');
                            fileBukti.value = ''; // reset file input
                        }
                    }
                });
            }

            // Form validation on submit
            if (form) {
                form.noValidate = true;
                form.addEventListener('submit', (event) => {
                    // 1. Required fields check
                    const requiredFields = [...form.querySelectorAll('[required]')];
                    const emptyFields = requiredFields.filter((field) => !String(field.value || '').trim());
                    if (emptyFields.length > 0) {
                        event.preventDefault();
                        emptyFields.forEach(el => {
                            el.style.borderColor = '#f87171';
                            el.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                            el.addEventListener('input', function clearError() {
                                el.style.borderColor = '';
                                el.style.boxShadow = '';
                                el.removeEventListener('input', clearError);
                            });
                        });
                        emptyFields[0].focus();
                        showErrorAlert('Data Belum Lengkap', 'Semua field wajib diisi. Kolom yang kosong telah ditandai dengan warna merah.');
                        return;
                    }

                    // 2. Date comparison validation
                    if (tglMulai && tglSelesai) {
                        const start = new Date(tglMulai.value);
                        const end = new Date(tglSelesai.value);
                        if (end < start) {
                            event.preventDefault();
                            tglSelesai.style.borderColor = '#f87171';
                            tglSelesai.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                            tglSelesai.focus();
                            showErrorAlert('Tanggal Tidak Valid', 'Tanggal selesai tidak boleh sebelum tanggal mulai.');
                            return;
                        }
                    }

                    // 3. Reject < and > inside Alasan
                    if (textareaAlasan && /[<>]/g.test(textareaAlasan.value)) {
                        event.preventDefault();
                        textareaAlasan.style.borderColor = '#f87171';
                        textareaAlasan.style.boxShadow = '0 0 0 3px rgba(248, 113, 113, 0.2)';
                        textareaAlasan.focus();
                        showErrorAlert('Karakter Dilarang', 'Alasan pengajuan tidak boleh mengandung karakter < atau >.');
                        return;
                    }

                    // 4. File Bukti extension validation on submit
                    if (fileBukti) {
                        const file = fileBukti.files[0];
                        if (file) {
                            const fileExtension = file.name.split('.').pop().toLowerCase();
                            const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                            if (!allowedExtensions.includes(fileExtension)) {
                                event.preventDefault();
                                fileBukti.focus();
                                showErrorAlert('Format Berkas Salah', 'Dokumen bukti hanya boleh berupa Gambar (JPG, JPEG, PNG) atau PDF.');
                                return;
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
