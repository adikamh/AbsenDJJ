<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    /**
     * Show default attendance settings form.
     */
    public function editDefaultSettings()
    {
        $settings = app(\App\Settings\GeneralSettings::class);
        $dayOverrides = WorkSchedule::where('type', 'day')->get()->keyBy('day_of_week');
        $dateOverrides = WorkSchedule::where('type', 'date')->orderBy('specific_date')->get();
        return view('dashboard.super_admin.settings.default', compact('settings', 'dayOverrides', 'dateOverrides'));
    }

    /**
     * Show calendar settings.
     */
    public function editCalendarSettings()
    {
        $settings = app(\App\Settings\GeneralSettings::class);
        $dayOverrides = WorkSchedule::where('type', 'day')->get()->keyBy('day_of_week');
        $dateOverrides = WorkSchedule::where('type', 'date')->orderBy('specific_date')->get();
        return view('dashboard.super_admin.settings.calendar', compact('settings', 'dayOverrides', 'dateOverrides'));
    }

    /**
     * Show day overrides settings.
     */
    public function editDayOverrides()
    {
        return redirect()->route('super-admin.settings.default');
    }

    /**
     * Show date overrides settings (calendar & national holiday sync).
     */
    public function editDateOverrides()
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

        return view('dashboard.super_admin.settings.date_overrides', compact('settings', 'dayOverrides', 'dateOverrides'));
    }

    /**
     * Show geofencing settings.
     */
    public function editGeofencingSettings()
    {
        $settings = app(\App\Settings\GeneralSettings::class);
        $dayOverrides = WorkSchedule::where('type', 'day')->get()->keyBy('day_of_week');
        $dateOverrides = WorkSchedule::where('type', 'date')->orderBy('specific_date')->get();
        $locations = \App\Models\OfficeLocation::orderBy('created_at', 'desc')->get();
        return view('dashboard.super_admin.settings.geofencing', compact('settings', 'dayOverrides', 'dateOverrides', 'locations'));
    }

    /**
     * Store a new office location coordinate.
     */
    public function storeOfficeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:1'],
        ]);

        \App\Models\OfficeLocation::create([
            'name' => $validated['name'],
            'latitude' => (string)$validated['latitude'],
            'longitude' => (string)$validated['longitude'],
            'radius' => (int)$validated['radius'],
        ]);

        return redirect()
            ->route('super-admin.settings.geofencing')
            ->with('success', 'Lokasi kantor baru berhasil ditambahkan.');
    }

    /**
     * Delete an office location coordinate.
     */
    public function destroyOfficeLocation($id)
    {
        $location = \App\Models\OfficeLocation::findOrFail($id);
        $location->delete();

        return redirect()
            ->route('super-admin.settings.geofencing')
            ->with('success', 'Lokasi kantor berhasil dihapus.');
    }

    /**
     * Update an office location coordinate.
     */
    public function updateOfficeLocation(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:1'],
        ]);

        $location = \App\Models\OfficeLocation::findOrFail($id);
        $location->update([
            'name' => $validated['name'],
            'latitude' => (string)$validated['latitude'],
            'longitude' => (string)$validated['longitude'],
            'radius' => (int)$validated['radius'],
        ]);

        return redirect()
            ->route('super-admin.settings.geofencing')
            ->with('success', 'Lokasi kantor berhasil diperbarui.');
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
     * Update default attendance settings.
     */
    public function updateDefaultSettings(Request $request)
    {
        $validated = $request->validate([
            'jam_masuk' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'jam_pulang' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
            'batas_keterlambatan' => ['required', 'string', 'regex:/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/'],
        ]);

        $settings = app(\App\Settings\GeneralSettings::class);
        
        $settings->jam_masuk = strlen($validated['jam_masuk']) === 5 ? $validated['jam_masuk'] . ':00' : $validated['jam_masuk'];
        $settings->jam_pulang = strlen($validated['jam_pulang']) === 5 ? $validated['jam_pulang'] . ':00' : $validated['jam_pulang'];
        $settings->batas_keterlambatan = strlen($validated['batas_keterlambatan']) === 5 ? $validated['batas_keterlambatan'] . ':00' : $validated['batas_keterlambatan'];

        $settings->save();
        WorkSchedule::updateTodayHolidayCacheFile();

        return redirect()
            ->route('super-admin.settings.default')
            ->with('success', 'Pengaturan waktu kehadiran default berhasil diperbarui.');
    }

    /**
     * Update geofencing settings.
     */
    public function updateGeofencingSettings(Request $request)
    {
        $validated = $request->validate([
            'latitude_kantor' => ['required', 'numeric', 'between:-90,90'],
            'longitude_kantor' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:1'],
        ]);

        $settings = app(\App\Settings\GeneralSettings::class);
        
        $settings->latitude_kantor = (string) $validated['latitude_kantor'];
        $settings->longitude_kantor = (string) $validated['longitude_kantor'];
        $settings->radius_meter = (int) $validated['radius_meter'];

        $settings->save();

        return redirect()
            ->route('super-admin.settings.geofencing')
            ->with('success', 'Pengaturan lokasi & geofencing berhasil diperbarui.');
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
            'keterangan' => [
                'nullable',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
        ], [
            'keterangan.regex' => 'Kolom keterangan hanya boleh diisi huruf, angka, spasi, dan karakter khusus berikut: / \ " \' & ( ) . , -',
            'keterangan.max' => 'Kolom keterangan tidak boleh lebih dari 170 karakter.',
        ]);

        $isHoliday = $request->boolean('is_holiday');

        // Check for duplicates
        if ($validated['type'] === 'day') {
            $exists = WorkSchedule::where('type', 'day')
                ->where('day_of_week', $validated['day_of_week'])
                ->exists();
            if ($exists) {
                return redirect()->route('super-admin.settings.default')
                    ->with('error', 'Override untuk hari tersebut sudah ada. Silakan edit yang sudah ada.');
            }
        } else {
            $exists = WorkSchedule::where('type', 'date')
                ->where('specific_date', $validated['specific_date'])
                ->exists();
            if ($exists) {
                return redirect()->route('super-admin.settings.date-overrides')
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
        WorkSchedule::updateTodayHolidayCacheFile();

        // Dispatch notifications if marked as holiday
        if ($isHoliday) {
            if ($validated['type'] === 'day') {
                WorkSchedule::sendWeeklyHolidayNotification((int) $validated['day_of_week'], $validated['keterangan'] ?? null);
            } else {
                WorkSchedule::sendHolidayNotification($validated['specific_date'], $validated['keterangan'] ?? null);
            }
        }

        $redirectUrl = route($validated['type'] === 'day' ? 'super-admin.settings.default' : 'super-admin.settings.date-overrides');
        if (str_contains(request()->headers->get('referer') ?? '', '/settings/calendar')) {
            $redirectUrl = route('super-admin.settings.calendar');
        }

        return redirect()->to($redirectUrl)
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
            'keterangan' => [
                'nullable',
                'string',
                'max:170',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\.,\'\"\\\\&]*$/'
            ],
        ], [
            'keterangan.regex' => 'Kolom keterangan hanya boleh diisi huruf, angka, spasi, dan karakter khusus berikut: / \ " \' & ( ) . , -',
            'keterangan.max' => 'Kolom keterangan tidak boleh lebih dari 170 karakter.',
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
        WorkSchedule::updateTodayHolidayCacheFile();

        // Dispatch notifications if marked as holiday
        if ($isHoliday) {
            if ($schedule->type === 'day') {
                WorkSchedule::sendWeeklyHolidayNotification((int) $schedule->day_of_week, $data['keterangan'] ?? null);
            } else {
                WorkSchedule::sendHolidayNotification($schedule->specific_date, $data['keterangan'] ?? null);
            }
        }

        $redirectUrl = route($schedule->type === 'day' ? 'super-admin.settings.default' : 'super-admin.settings.date-overrides');
        if (str_contains(request()->headers->get('referer') ?? '', '/settings/calendar')) {
            $redirectUrl = route('super-admin.settings.calendar');
        }

        return redirect()->to($redirectUrl)
            ->with('success', 'Jadwal override berhasil diperbarui.');
    }

    /**
     * Delete a work schedule override (reverts to default).
     */
    public function destroyScheduleOverride(WorkSchedule $schedule)
    {
        $type = $schedule->type;
        $schedule->delete();
        WorkSchedule::updateTodayHolidayCacheFile();

        $redirectUrl = route($type === 'day' ? 'super-admin.settings.default' : 'super-admin.settings.date-overrides');
        if (str_contains(request()->headers->get('referer') ?? '', '/settings/calendar')) {
            $redirectUrl = route('super-admin.settings.calendar');
        }

        return redirect()->to($redirectUrl)
            ->with('success', 'Jadwal override berhasil dihapus, kembali ke default.');
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
                        WorkSchedule::sendHolidayNotification($dateStr, $desc);
                    }
                }
                $successYears[] = $year;
            } catch (\Exception $e) {
                $failedYears[] = $year;
            }
        }

        if (count($successYears) === 0) {
            return redirect()->route('super-admin.settings.date-overrides')
                ->with('error', 'Gagal mengimpor hari libur nasional untuk tahun ' . implode(', ', $failedYears) . '.');
        }

        $successMsg = "Berhasil mengimpor {$importedCount} hari libur nasional untuk tahun " . implode(' & ', $successYears) . ".";
        if (count($failedYears) > 0) {
            $successMsg .= " Namun, gagal untuk tahun " . implode(', ', $failedYears) . ".";
        }
        WorkSchedule::updateTodayHolidayCacheFile();

        return redirect()->route('super-admin.settings.date-overrides')
            ->with('success', $successMsg);
    }
}
