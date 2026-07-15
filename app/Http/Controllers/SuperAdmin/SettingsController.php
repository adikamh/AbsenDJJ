<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    /**
     * Show general settings form.
     */
    public function editSettings()
    {
        $settings = app(\App\Settings\GeneralSettings::class);

        // Auto sync current and next year holidays if missing
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        $hasHolidaysCurrent = WorkSchedule::where('type', 'date')
            ->where('is_holiday', true)
            ->whereYear('specific_date', $currentYear)
            ->exists();

        $hasHolidaysNext = WorkSchedule::where('type', 'date')
            ->where('is_holiday', true)
            ->whereYear('specific_date', $nextYear)
            ->exists();

        if (!$hasHolidaysCurrent) {
            $this->performSyncHolidaysForYear($currentYear);
        }
        if (!$hasHolidaysNext) {
            $this->performSyncHolidaysForYear($nextYear);
        }

        $dayOverrides = WorkSchedule::where('type', 'day')->get()->keyBy('day_of_week');
        $dateOverrides = WorkSchedule::where('type', 'date')->orderBy('specific_date')->get();

        return view('dashboard.super_admin.settings', compact('settings', 'dayOverrides', 'dateOverrides'));
    }

    /**
     * Helper to sync holidays for a given year silently.
     */
    private function performSyncHolidaysForYear($year)
    {
        try {
            $response = Http::timeout(3)->get("https://api-hari-libur.vercel.app/api?year={$year}");

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['status']) && $body['status'] === 'success' && isset($body['data'])) {
                    $holidays = $body['data'];
                    foreach ($holidays as $holiday) {
                        $dateStr = $holiday['date'];
                        $desc = $holiday['description'];

                        $exists = WorkSchedule::where('type', 'date')
                            ->where('specific_date', $dateStr)
                            ->exists();

                        if (!$exists) {
                            WorkSchedule::create([
                                'type' => 'date',
                                'specific_date' => $dateStr,
                                'is_holiday' => true,
                                'keterangan' => $desc,
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore API failures to ensure settings page loads
        }
    }

    /**
     * Update general settings.
     */
    public function updateSettings(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('updateSettings called', $request->all());
        $validated = $request->validate([
            'jam_masuk' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'jam_pulang' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'batas_keterlambatan' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'latitude_kantor' => ['required', 'numeric', 'between:-90,90'],
            'longitude_kantor' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:1'],
        ]);

        $settings = app(\App\Settings\GeneralSettings::class);
        
        $settings->jam_masuk = strlen($validated['jam_masuk']) === 5 ? $validated['jam_masuk'] . ':00' : $validated['jam_masuk'];
        $settings->jam_pulang = strlen($validated['jam_pulang']) === 5 ? $validated['jam_pulang'] . ':00' : $validated['jam_pulang'];
        $settings->batas_keterlambatan = strlen($validated['batas_keterlambatan']) === 5 ? $validated['batas_keterlambatan'] . ':00' : $validated['batas_keterlambatan'];
        
        $settings->latitude_kantor = (string) $validated['latitude_kantor'];
        $settings->longitude_kantor = (string) $validated['longitude_kantor'];
        $settings->radius_meter = (int) $validated['radius_meter'];

        $settings->save();

        return redirect()
            ->route('super-admin.settings')
            ->with('success', 'Pengaturan parameter global berhasil diperbarui.');
    }

    /**
     * Store a new work schedule override (per-day or per-date).
     */
    public function storeScheduleOverride(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:day,date'],
            'day_of_week' => ['required_if:type,day', 'nullable', 'integer', 'between:0,6'],
            'specific_date' => ['required_if:type,date', 'nullable', 'date'],
            'jam_masuk' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'batas_keterlambatan' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'jam_pulang' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'is_holiday' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $isHoliday = $request->boolean('is_holiday');

        // Check for duplicates
        if ($validated['type'] === 'day') {
            $exists = WorkSchedule::where('type', 'day')
                ->where('day_of_week', $validated['day_of_week'])
                ->exists();
            if ($exists) {
                return redirect()->route('super-admin.settings')
                    ->with('error', 'Override untuk hari tersebut sudah ada. Silakan edit yang sudah ada.');
            }
        } else {
            $exists = WorkSchedule::where('type', 'date')
                ->where('specific_date', $validated['specific_date'])
                ->exists();
            if ($exists) {
                return redirect()->route('super-admin.settings')
                    ->with('error', 'Override untuk tanggal tersebut sudah ada. Silakan edit yang sudah ada.');
            }
        }

        $data = [
            'type' => $validated['type'],
            'day_of_week' => $validated['type'] === 'day' ? $validated['day_of_week'] : null,
            'specific_date' => $validated['type'] === 'date' ? $validated['specific_date'] : null,
            'is_holiday' => $isHoliday,
            'keterangan' => $validated['keterangan'] ?? null,
        ];

        if (!$isHoliday) {
            $data['jam_masuk'] = isset($validated['jam_masuk']) ? (strlen($validated['jam_masuk']) === 5 ? $validated['jam_masuk'] . ':00' : $validated['jam_masuk']) : null;
            $data['batas_keterlambatan'] = isset($validated['batas_keterlambatan']) ? (strlen($validated['batas_keterlambatan']) === 5 ? $validated['batas_keterlambatan'] . ':00' : $validated['batas_keterlambatan']) : null;
            $data['jam_pulang'] = isset($validated['jam_pulang']) ? (strlen($validated['jam_pulang']) === 5 ? $validated['jam_pulang'] . ':00' : $validated['jam_pulang']) : null;
        }

        WorkSchedule::create($data);

        return redirect()
            ->route('super-admin.settings')
            ->with('success', 'Jadwal override berhasil ditambahkan.');
    }

    /**
     * Update an existing work schedule override.
     */
    public function updateScheduleOverride(Request $request, WorkSchedule $schedule)
    {
        $validated = $request->validate([
            'jam_masuk' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'batas_keterlambatan' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'jam_pulang' => ['nullable', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'is_holiday' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $isHoliday = $request->boolean('is_holiday');

        $data = [
            'is_holiday' => $isHoliday,
            'keterangan' => $validated['keterangan'] ?? $schedule->keterangan,
        ];

        if ($isHoliday) {
            $data['jam_masuk'] = null;
            $data['batas_keterlambatan'] = null;
            $data['jam_pulang'] = null;
        } else {
            $data['jam_masuk'] = isset($validated['jam_masuk']) ? (strlen($validated['jam_masuk']) === 5 ? $validated['jam_masuk'] . ':00' : $validated['jam_masuk']) : $schedule->jam_masuk;
            $data['batas_keterlambatan'] = isset($validated['batas_keterlambatan']) ? (strlen($validated['batas_keterlambatan']) === 5 ? $validated['batas_keterlambatan'] . ':00' : $validated['batas_keterlambatan']) : $schedule->batas_keterlambatan;
            $data['jam_pulang'] = isset($validated['jam_pulang']) ? (strlen($validated['jam_pulang']) === 5 ? $validated['jam_pulang'] . ':00' : $validated['jam_pulang']) : $schedule->jam_pulang;
        }

        $schedule->update($data);

        return redirect()
            ->route('super-admin.settings')
            ->with('success', 'Jadwal override berhasil diperbarui.');
    }

    /**
     * Delete a work schedule override (reverts to default).
     */
    public function destroyScheduleOverride(WorkSchedule $schedule)
    {
        $schedule->delete();

        return redirect()
            ->route('super-admin.settings')
            ->with('success', 'Jadwal override berhasil dihapus, kembali to default.');
    }

    /**
     * Sync Indonesian national holidays from public API for a given year.
     */
    public function syncHolidays(Request $request)
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'between:2020,2035'],
        ]);

        $startYear = $validated['year'] ?? now()->year;
        $yearsToSync = [$startYear, $startYear + 1];
        
        $importedCount = 0;
        $successYears = [];
        $failedYears = [];

        foreach ($yearsToSync as $year) {
            try {
                $response = Http::get("https://api-hari-libur.vercel.app/api?year={$year}");

                if ($response->failed()) {
                    $failedYears[] = $year;
                    continue;
                }

                $body = $response->json();
                if (!isset($body['status']) || $body['status'] !== 'success' || !isset($body['data'])) {
                    $failedYears[] = $year;
                    continue;
                }

                $holidays = $body['data'];
                foreach ($holidays as $holiday) {
                    $dateStr = $holiday['date'];
                    $desc = $holiday['description'];

                    // Check duplicate
                    $exists = WorkSchedule::where('type', 'date')
                        ->where('specific_date', $dateStr)
                        ->exists();

                    if (!$exists) {
                        WorkSchedule::create([
                            'type' => 'date',
                            'specific_date' => $dateStr,
                            'is_holiday' => true,
                            'keterangan' => $desc,
                        ]);
                        $importedCount++;
                    }
                }
                $successYears[] = $year;
            } catch (\Exception $e) {
                $failedYears[] = $year;
            }
        }

        if (count($successYears) === 0) {
            return redirect()->route('super-admin.settings')
                ->with('error', 'Gagal mengimpor hari libur nasional untuk tahun ' . implode(', ', $failedYears) . '.');
        }

        $successMsg = "Berhasil mengimpor {$importedCount} hari libur nasional untuk tahun " . implode(' & ', $successYears) . ".";
        if (count($failedYears) > 0) {
            $successMsg .= " Namun, gagal untuk tahun " . implode(', ', $failedYears) . ".";
        }

        return redirect()->route('super-admin.settings')
            ->with('success', $successMsg);
    }
}
