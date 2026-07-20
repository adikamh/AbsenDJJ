<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $approvers['laporan_title'] }} - {{ $user->nama_lengkap }}</title>
    <style>
        @if($showBrowserHeader)
            @page {
                size: A4 landscape;
                margin: 15mm;
            }
        @else
            @page {
                size: A4 landscape;
                margin: 0;
            }
        @endif

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: {{ !$showBrowserHeader ? '0 15mm 15mm 15mm !important' : '0' }};
            color: #000;
            background-color: #fff;
            line-height: 1.3;
            font-size: 10pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @if(!$showBrowserHeader)
            .absensi-table {
                margin-top: -12mm !important;
            }
        @endif

        .header-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .header-title h1 {
            font-size: 16pt;
            margin: 0;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-title h2 {
            font-size: 13pt;
            margin: 5px 0 0 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .personal-info {
            margin-bottom: 15px;
            font-size: 11pt;
            width: 100%;
        }

        .personal-info table {
            border: none;
            width: auto;
        }

        .personal-info td {
            border: none;
            padding: 2px 0;
            vertical-align: top;
        }

        .personal-info td.label {
            width: 80px;
        }

        .personal-info td.colon {
            width: 15px;
            text-align: center;
        }

        .absensi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }

        .absensi-table th {
            background-color: {{ $headerBg ?? '#0c2340' }} !important;
            color: {{ $headerText ?? '#ffffff' }} !important;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000000;
            padding: 8px 6px;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .absensi-table td {
            border: 1px solid #000000;
            padding: 8px 6px;
            vertical-align: middle;
        }

        /* Alignment styles */
        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        /* Row background formatting */
        .row-workday {
            background-color: #ffffff;
        }

        .row-holiday {
            background-color: #ff0000;
            color: #000000;
        }

        .row-holiday td {
            background-color: #ff0000 !important;
            color: #000000 !important;
            font-weight: bold;
        }

        .row-cuti {
            background-color: #ffc000;
            color: #000000;
        }

        .row-cuti td {
            background-color: #ffc000 !important;
            color: #000000 !important;
            font-weight: bold;
        }

        /* Activities bullet list spacing */
        .activities-cell {
            white-space: pre-line;
            line-height: 1.4;
        }

        /* Photo container styling */
        .photo-container {
            width: 80px;
            height: 100px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ccc;
            background-color: #f8f9fa;
            margin: 0 auto;
        }

        .photo-container img {
            max-width: 80px;
            max-height: 100px;
            object-fit: cover;
            display: block;
        }

        /* Signature layout in table */
        .signature-cell-img {
            max-height: 45px;
            max-width: 90px;
            display: block;
            margin: 0 auto;
        }

        /* Footer section signatures */
        .footer-signatures {
            margin-top: 35px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signatures-table {
            width: 100%;
            border: none;
        }

        .signatures-table td {
            border: none;
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 11pt;
            padding-top: 10px;
        }

        .signature-image-block {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px auto;
        }

        .signature-image-block img {
            max-height: 75px;
            width: auto;
            display: block;
        }

        .signature-placeholder {
            height: 80px;
        }

        .print-actions {
            margin-bottom: 20px;
            text-align: right;
        }

        .btn-print-action {
            padding: 8px 18px;
            font-size: 11pt;
            font-weight: 600;
            color: #fff;
            background-color: #2e4085;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-print-action:hover {
            background-color: #1d2b5c;
        }

        @media print {
            .print-actions {
                display: none;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>

    @if(($exportFormat ?? 'pdf') !== 'word')
        <div class="print-actions">
            <button onclick="window.print()" class="btn-print-action">Cetak / Simpan PDF</button>
        </div>
    @endif

    <!-- Top Margin Spacer Wrapper for Page 1 -->
    <div style="{{ !$showBrowserHeader ? 'margin-top: 15mm;' : '' }}">
        @if(!empty($laporanKop))
            <div class="report-kop-container" style="width: 100%; text-align: center; margin-bottom: 15px;">
                <img src="{{ $laporanKop }}" alt="Kop Laporan" style="max-width: 100%; max-height: 110px; display: block; margin: 0 auto; object-fit: contain;">
            </div>
        @endif

        <!-- Header Section -->
        <div class="header-title">
            <h1>{{ $approvers['laporan_title'] }}</h1>
            <h2>{{ $approvers['laporan_subtitle'] }}</h2>
        </div>

        <!-- Personal Info Section -->
        <div class="personal-info">
            <table>
                <tr>
                    <td class="label">Nama</td>
                    <td class="colon">:</td>
                    <td>{{ $user->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td class="label">Jabatan</td>
                    <td class="colon">:</td>
                    <td>{{ $user->jabatan ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">Periode</td>
                    <td class="colon">:</td>
                    <td>{{ $startDate->translatedFormat('d F') }} - {{ $endDate->translatedFormat('d F Y') }}</td>
                </tr>
            </table>
        </div>
    </div>

    @php
        // Prepare dynamic column widths
        $cols = [];
        if ($fotoMode === 'both') {
            $cols = ['12%', '7%', '7%', '32%', '13%', '13%', '10%', '6%'];
        } elseif ($fotoMode === 'masuk' || $fotoMode === 'pulang') {
            $cols = ['15%', '8%', '8%', '35%', '16%', '10%', '8%'];
        } else {
            $cols = ['15%', '9%', '9%', '49%', '10%', '8%'];
        }
    @endphp

    <!-- Attendance Grid Table -->
    <table class="absensi-table" style="margin-bottom: 20px;">
        <colgroup>
            @foreach($cols as $colWidth)
                <col style="width: {{ $colWidth }};">
            @endforeach
        </colgroup>
        <thead>
            @if(!$showBrowserHeader)
                <tr class="print-page-spacer" style="border: none !important; background: transparent !important;">
                    <td colspan="{{ $fotoMode === 'both' ? 8 : ($fotoMode === 'none' ? 6 : 7) }}" style="border: none !important; height: 12mm !important; padding: 0 !important; background: transparent !important; line-height: 0; font-size: 0;"></td>
                </tr>
            @endif
            @if($fotoMode === 'both')
                <tr>
                    <th rowspan="2">Tanggal</th>
                    <th colspan="2">Jam</th>
                    <th rowspan="2">Rincian Kegiatan</th>
                    <th colspan="2">Dokumentasi Harian</th>
                    <th rowspan="2">Tanda Tangan</th>
                    <th rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    <th>Masuk</th>
                    <th>Pulang</th>
                    <th>Masuk</th>
                    <th>Pulang</th>
                </tr>
            @elseif($fotoMode === 'masuk' || $fotoMode === 'pulang')
                <tr>
                    <th rowspan="2">Tanggal</th>
                    <th colspan="2">Jam</th>
                    <th rowspan="2">Rincian Kegiatan</th>
                    <th rowspan="2">Dokumentasi ({{ $fotoMode === 'masuk' ? 'Masuk' : 'Pulang' }})</th>
                    <th rowspan="2">Tanda Tangan</th>
                    <th rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    <th>Masuk</th>
                    <th>Pulang</th>
                </tr>
            @else
                <tr>
                    <th rowspan="2">Tanggal</th>
                    <th colspan="2">Jam</th>
                    <th rowspan="2">Rincian Kegiatan</th>
                    <th rowspan="2">Tanda Tangan</th>
                    <th rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    <th>Masuk</th>
                    <th>Pulang</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach($records as $record)
                @php
                    $rowClass = 'row-workday';
                    if ($record['is_holiday']) {
                        if (str_contains(strtolower($record['holiday_name']), 'cuti bersama')) {
                            $rowClass = 'row-cuti';
                        } else {
                            $rowClass = 'row-holiday';
                        }
                    }
                @endphp
                <tr class="{{ $rowClass }}" @if($loop->last) style="page-break-after: avoid; break-after: avoid;" @endif>
                    <td class="text-center" style="vertical-align: middle;">
                        <div style="display: inline-block; text-align: left; white-space: nowrap;">
                            {{ $record['hari'] }},<br>
                            {{ $record['tanggal_indo'] }}
                        </div>
                    </td>
                    
                    @if($record['is_holiday'])
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">{{ $record['kegiatan'] }}</td>
                        @if($fotoMode === 'both')
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        @elseif($fotoMode === 'masuk' || $fotoMode === 'pulang')
                            <td class="text-center">-</td>
                        @endif
                        <td class="text-center">-</td>
                        <td class="text-center">LIBUR</td>
                    @else
                        <td class="text-center">{{ $record['masuk'] }}</td>
                        <td class="text-center">{{ $record['pulang'] }}</td>
                        <td class="activities-cell text-left">{{ $record['kegiatan'] ?: '-' }}</td>
                        
                        @if($fotoMode === 'both')
                            <td class="text-center">
                                @if($record['foto_masuk'])
                                    <div class="photo-container"><img src="{{ str_starts_with($record['foto_masuk'], 'data:') ? $record['foto_masuk'] : asset('storage/' . $record['foto_masuk']) }}" alt="Selfie Masuk"></div>
                                @else - @endif
                            </td>
                            <td class="text-center">
                                @if($record['foto_pulang'])
                                    <div class="photo-container"><img src="{{ str_starts_with($record['foto_pulang'], 'data:') ? $record['foto_pulang'] : asset('storage/' . $record['foto_pulang']) }}" alt="Selfie Pulang"></div>
                                @else - @endif
                            </td>
                        @elseif($fotoMode === 'masuk')
                            <td class="text-center">
                                @if($record['foto_masuk'])
                                    <div class="photo-container"><img src="{{ str_starts_with($record['foto_masuk'], 'data:') ? $record['foto_masuk'] : asset('storage/' . $record['foto_masuk']) }}" alt="Selfie Masuk"></div>
                                @else - @endif
                            </td>
                        @elseif($fotoMode === 'pulang')
                            <td class="text-center">
                                @if($record['foto_pulang'])
                                    <div class="photo-container"><img src="{{ str_starts_with($record['foto_pulang'], 'data:') ? $record['foto_pulang'] : asset('storage/' . $record['foto_pulang']) }}" alt="Selfie Pulang"></div>
                                @else - @endif
                            </td>
                        @endif

                        <td class="text-center">
                            @if($user->signature_path)
                                <img src="{{ str_starts_with($user->signature_path, 'data:') ? $user->signature_path : asset($user->signature_path) }}" class="signature-cell-img" alt="TTD">
                            @else - @endif
                        </td>

                        <td class="text-center">{{ $record['keterangan'] }}</td>
                    @endif
                </tr>
            @endforeach
            <!-- Signatures Row inside the same table -->
            <tr class="signatures-row" style="page-break-inside: avoid !important; break-inside: avoid !important; border: none !important; background: transparent !important;">
                <td colspan="{{ $fotoMode === 'both' ? 8 : ($fotoMode === 'none' ? 6 : 7) }}" style="border: none !important; padding: 25px 0 0 0 !important; background: transparent !important;">
                    <div class="footer-signatures" style="page-break-inside: avoid !important; break-inside: avoid !important; display: block !important;">
                        @php
                            // Group signatures by their selected row value
                            $groupedSignatures = [];
                            foreach ($signatures as $sig) {
                                $rowVal = intval($sig['row'] ?? 1);
                                $groupedSignatures[$rowVal][] = $sig;
                            }
                            ksort($groupedSignatures);
                        @endphp

                        @foreach($groupedSignatures as $rowVal => $chunk)
                            <table class="signatures-table" style="width: 100%; border: none !important; margin-top: {{ !$loop->first ? '25px' : '0' }}; page-break-inside: avoid !important; break-inside: avoid !important; background: transparent !important;">
                                <tr style="page-break-inside: avoid !important; break-inside: avoid !important; border: none !important; background: transparent !important;">
                                    @foreach($chunk as $sig)
                                        <td style="border: none !important; width: {{ 100 / count($chunk) }}%; text-align: center; vertical-align: top; font-size: 11pt; padding: 10px 5px !important; background: transparent !important; color: #000 !important;">
                                            <div>
                                                {!! nl2br($sig['title']) !!}
                                            </div>
                                            @if(!empty($sig['instansi']))
                                                <div>{!! nl2br($sig['instansi']) !!}</div>
                                            @endif
                                            @if(!empty($sig['divisi']))
                                                <div>{!! nl2br($sig['divisi']) !!}</div>
                                            @endif

                                            <div class="signature-image-block" style="height: 80px; display: flex; align-items: center; justify-content: center; margin: 5px auto;">
                                                @if(!empty($sig['ttd']))
                                                    <img src="{{ $sig['ttd'] }}" style="max-height: 75px; width: auto; display: block;" alt="TTD">
                                                @else
                                                    <div class="signature-placeholder" style="height: 70px;"></div>
                                                @endif
                                            </div>

                                            <strong style="text-decoration: underline;">{!! nl2br($sig['nama']) !!}</strong>
                                            @if(!empty($sig['nip']))
                                                <br>NIP. {{ $sig['nip'] }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        @endforeach
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    @if(($exportFormat ?? 'pdf') !== 'word')
        <script>
            // Trigger print setup once document is fully loaded
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 600);
            });
        </script>
    @endif
</body>
</html>
