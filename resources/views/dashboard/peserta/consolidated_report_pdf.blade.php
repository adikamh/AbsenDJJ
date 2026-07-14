<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Laporan Keseluruhan - {{ $user->nama_lengkap }} - {{ $selectedDate->translatedFormat('F Y') }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 30px;
            color: #000;
            background-color: #fff;
            line-height: 1.4;
        }

        .header-container {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo-pu {
            height: 75px;
            width: auto;
            margin-right: 15px;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header-text h1 {
            font-size: 14pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header-text h2 {
            font-size: 12pt;
            margin: 2px 0;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header-text p {
            font-size: 9pt;
            margin: 2px 0;
            font-style: italic;
        }

        .title-report {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0 15px;
            text-decoration: underline;
        }

        .meta-info {
            margin-bottom: 25px;
            font-size: 11pt;
        }

        .meta-info table {
            width: 100%;
            border: none;
        }

        .meta-info td {
            padding: 3px 0;
            border: none;
            vertical-align: top;
        }

        .meta-info td.label {
            width: 180px;
        }

        .meta-info td.colon {
            width: 15px;
            text-align: center;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0 8px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
        }

        .data-table td.center {
            text-align: center;
        }

        .stats-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10.5pt;
        }

        .stats-summary-table th, .stats-summary-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .stats-summary-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 40px;
            width: 100%;
            font-size: 11pt;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border: none;
        }

        .signature-table td {
            border: none;
            width: 50%;
            text-align: center;
            padding-top: 10px;
        }

        .signature-space {
            height: 70px;
        }

        .print-btn-container {
            margin-bottom: 20px;
            text-align: right;
        }

        .btn-print {
            padding: 8px 16px;
            font-size: 11pt;
            font-weight: bold;
            color: #fff;
            background-color: #059669;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-print:hover {
            background-color: #047857;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            .print-btn-container {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <button onclick="window.print()" class="btn-print">Cetak Laporan / Simpan PDF</button>
    </div>

    <!-- Halaman 1: Rekap Absensi & Rincian Kehadiran -->
    <div class="header-container">
        <img src="{{ asset('images/Logo/Logo_PU.png') }}" class="logo-pu" alt="Logo PU">
        <div class="header-text">
            <h1>KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT</h1>
            <h2>DIREKTORAT JENDERAL BINA MARGA</h2>
            <h2>DIREKTORAT BINA TEKNIK JALAN DAN JEMBATAN</h2>
            <p>Jl. Timur Indah No. 2, Bandung, Telp/Fax. (022) 7802251</p>
        </div>
    </div>

    <div class="title-report">
        REKAP LAPORAN KESELURUHAN MAHASISWA / SISWA MAGANG
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td class="label">Nama Peserta</td>
                <td class="colon">:</td>
                <td><strong>{{ $user->nama_lengkap }}</strong></td>
            </tr>
            <tr>
                <td class="label">NIP / Nomor Identitas</td>
                <td class="colon">:</td>
                <td>{{ $user->nip }}</td>
            </tr>
            <tr>
                <td class="label">Instansi Asal</td>
                <td class="colon">:</td>
                <td>{{ $user->instansi?->nama_instansi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pembimbing Lapangan</td>
                <td class="colon">:</td>
                <td>{{ $user->pembimbing->nama_lengkap ?? 'Belum Ditugaskan' }}</td>
            </tr>
            <tr>
                <td class="label">Periode Laporan</td>
                <td class="colon">:</td>
                <td><strong>{{ $selectedDate->translatedFormat('F Y') }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="section-title">I. Rekapitulasi Kehadiran Bulanan</div>
    
    <table class="stats-summary-table">
        <thead>
            <tr>
                <th>Hadir Tepat Waktu</th>
                <th>Terlambat</th>
                <th>Izin / Sakit</th>
                <th>Tanpa Keterangan (Alfa)</th>
                <th>Persentase Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ $stats['hadir'] }} Hari</strong></td>
                <td><strong>{{ $stats['terlambat'] }} Hari</strong></td>
                <td><strong>{{ $stats['izin'] }} Hari</strong></td>
                <td><strong>{{ $stats['absen'] }} Hari</strong></td>
                <td><strong style="color: #059669;">{{ $attendanceRate }}%</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">II. Rincian Riwayat Kehadiran Harian</div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Tanggal</th>
                <th style="width: 15%;">Jam Masuk</th>
                <th style="width: 15%;">Jam Pulang</th>
                <th style="width: 25%;">Status</th>
                <th style="width: 20%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($attendance->tanggal)->translatedFormat('d F Y') }}</td>
                    <td class="center">{{ $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i:s') : '-' }}</td>
                    <td class="center">{{ $attendance->jam_pulang ? \Carbon\Carbon::parse($attendance->jam_pulang)->format('H:i:s') : '-' }}</td>
                    <td class="center">
                        <strong>{{ $attendance->status }}</strong>
                    </td>
                    <td>
                        @if($attendance->status === 'Terlambat')
                            Terlambat masuk
                        @elseif($attendance->status === 'Hadir')
                            Hadir tepat waktu
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; font-style: italic;">Tidak ada riwayat kehadiran pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pindah Halaman untuk Logbook Kegiatan -->
    <div class="page-break"></div>

    <div class="header-container" style="margin-top: 20px;">
        <img src="{{ asset('images/Logo/Logo_PU.png') }}" class="logo-pu" alt="Logo PU">
        <div class="header-text">
            <h1>KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT</h1>
            <h2>DIREKTORAT JENDERAL BINA MARGA</h2>
            <h2>DIREKTORAT BINA TEKNIK JALAN DAN JEMBATAN</h2>
            <p>Jl. Timur Indah No. 2, Bandung, Telp/Fax. (022) 7802251</p>
        </div>
    </div>

    <div class="title-report" style="margin-top: 15px;">
        REKAP LAPORAN KEGIATAN HARIAN (LOGBOOK)
    </div>

    <div class="section-title">III. Rincian Kegiatan Harian</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 25%;">Nama Tugas / Kegiatan</th>
                <th style="width: 45%;">Uraian / Deskripsi Kegiatan</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logbooks as $index => $logbook)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($logbook->tanggal)->translatedFormat('d/m/Y') }}</td>
                    <td><strong>{{ $logbook->kegiatan }}</strong></td>
                    <td>{!! nl2br(e($logbook->deskripsi)) !!}</td>
                    <td class="center">
                        {{ $logbook->status_approval }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic;">Belum ada entri logbook kegiatan untuk bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pindah Halaman untuk Laporan Izin/Sakit -->
    <div class="page-break"></div>

    <div class="header-container" style="margin-top: 20px;">
        <img src="{{ asset('images/Logo/Logo_PU.png') }}" class="logo-pu" alt="Logo PU">
        <div class="header-text">
            <h1>KEMENTERIAN PEKERJAAN UMUM DAN PERUMAHAN RAKYAT</h1>
            <h2>DIREKTORAT JENDERAL BINA MARGA</h2>
            <h2>DIREKTORAT BINA TEKNIK JALAN DAN JEMBATAN</h2>
            <p>Jl. Timur Indah No. 2, Bandung, Telp/Fax. (022) 7802251</p>
        </div>
    </div>

    <div class="title-report" style="margin-top: 15px;">
        REKAP LAPORAN PENGAJUAN IZIN / SAKIT
    </div>

    <div class="section-title">IV. Rincian Izin & Sakit Resmi</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Rentang Tanggal</th>
                <th style="width: 15%;">Jenis Pengajuan</th>
                <th style="width: 35%;">Alasan Pengajuan</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $index => $leave)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">
                        {{ $leave->tanggal_mulai->format('d/m/Y') }} s/d {{ $leave->tanggal_selesai->format('d/m/Y') }}
                    </td>
                    <td class="center">
                        <strong>{{ $leave->jenis }}</strong>
                    </td>
                    <td>{{ $leave->alasan }}</td>
                    <td class="center">
                        {{ $leave->status_approval }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic;">Belum ada pengajuan izin / sakit yang disetujui pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Pembimbing Lapangan
                    <div class="signature-space"></div>
                    <strong>{{ $user->pembimbing->nama_lengkap ?? '............................................' }}</strong><br>
                    NIP. {{ $user->pembimbing->nip ?? '............................................' }}
                </td>
                <td>
                    Bandung, {{ now()->translatedFormat('d F Y') }}<br>
                    Peserta Magang
                    <div class="signature-space"></div>
                    <strong>{{ $user->nama_lengkap }}</strong><br>
                    NIP. {{ $user->nip }}
                </td>
            </tr>
        </table>
    </div>

    <script>
        // Auto print when opening
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
