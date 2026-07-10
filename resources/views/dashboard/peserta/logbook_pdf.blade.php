<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook - {{ $user->nama_lengkap }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 30px;
            color: #000;
            background-color: #fff;
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
            margin: 25px 0 15px;
            text-decoration: underline;
        }

        .meta-info {
            margin-bottom: 20px;
            font-size: 11pt;
            line-height: 1.5;
        }

        .meta-info table {
            width: 100%;
            border: none;
        }

        .meta-info td {
            padding: 2px 0;
            border: none;
        }

        .meta-info td.label {
            width: 180px;
        }

        .meta-info td.colon {
            width: 15px;
            text-align: center;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10.5pt;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .data-table td.center {
            text-align: center;
        }

        .signature-section {
            margin-top: 50px;
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
            height: 75px;
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
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-print:hover {
            background-color: #0056b3;
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
        <button onclick="window.print()" class="btn-print">Cetak Dokumen / Simpan PDF</button>
    </div>

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
        LAPORAN KEGIATAN HARIAN (LOGBOOK) PESERTA MAGANG
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
        </table>
    </div>

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
                    <td class="center">{{ $logbook->tanggal->format('d/m/Y') }}</td>
                    <td><strong>{{ $logbook->kegiatan }}</strong></td>
                    <td>{!! nl2br(e($logbook->deskripsi)) !!}</td>
                    <td class="center">
                        {{ $logbook->status_approval }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic;">Belum ada entri logbook kegiatan.</td>
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
                    <br>
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
            }, 500);
        });
    </script>
</body>
</html>
