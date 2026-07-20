<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UniqueCodeGenerator
{
    /**
     * Characters allowed in random segments (A-Z, 0-9).
     */
    private const CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Generate a unique USER_CODE.
     * Format: USR{YY}-{ID}{RAND:6char}-{SEQ}
     * Example: USR26-125A7K9P2-001
     *
     * @param  int  $userId  The actual auto-increment `id` from the users table.
     * @return string
     */
    public function generateUserCode(int $userId): string
    {
        $yy   = now()->format('y');
        $seq  = $this->nextSequence('user_code_seq');

        $maxAttempts = 10;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $rand = $this->randomString(6);
            $seqFormatted = $this->formatSeq($seq);
            $code = "USR{$yy}-{$userId}{$rand}-{$seqFormatted}";

            if (! $this->userCodeExists($code)) {
                return $code;
            }
        }

        // Fallback: append extra random to guarantee uniqueness
        return "USR{$yy}-{$userId}" . $this->randomString(10) . "-{$this->formatSeq($seq)}";
    }

    /**
     * Generate a unique LOGBOOK_CODE.
     * Format: LOG{YY}-{DD}{MM}{SS}{RAND:4char}-{SEQ}
     * Example: LOG26-190745A9K2-001
     *
     * @return string
     */
    public function generateLogbookCode(): string
    {
        $now  = now();
        $yy   = $now->format('y');
        $dd   = $now->format('d');
        $mm   = $now->format('m');
        $ss   = $now->format('s');
        $seq  = $this->nextSequence('logbook_code_seq');

        $maxAttempts = 10;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $rand = $this->randomString(4);
            $seqFormatted = $this->formatSeq($seq);
            $code = "LOG{$yy}-{$dd}{$mm}{$ss}{$rand}-{$seqFormatted}";

            if (! $this->logbookCodeExists($code)) {
                return $code;
            }
        }

        // Fallback: append extra random
        return "LOG{$yy}-{$dd}{$mm}{$ss}" . $this->randomString(8) . "-{$this->formatSeq($seq)}";
    }

    /**
     * Get next SEQ value for the given counter key (thread-safe using DB lock).
     *
     * @param  string  $key  'user_code_seq' or 'logbook_code_seq'
     * @return int
     */
    private function nextSequence(string $key): int
    {
        return DB::transaction(function () use ($key) {
            // Lock the row for update to prevent race conditions
            $row = DB::table('code_sequences')
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if ($row) {
                $next = $row->value + 1;
                DB::table('code_sequences')
                    ->where('key', $key)
                    ->update(['value' => $next, 'updated_at' => now()]);
            } else {
                $next = 1;
                DB::table('code_sequences')->insert([
                    'key'        => $key,
                    'value'      => $next,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $next;
        });
    }

    /**
     * Format SEQ: minimum 3 digits with leading zeros (001, 002, ..., 999, 1000, ...).
     */
    private function formatSeq(int $seq): string
    {
        return str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a random uppercase alphanumeric string of given length.
     */
    private function randomString(int $length): string
    {
        $chars  = self::CHARSET;
        $max    = strlen($chars) - 1;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }

    /**
     * Check if a user_code already exists in the database.
     */
    private function userCodeExists(string $code): bool
    {
        return DB::table('users')->where('user_code', $code)->exists();
    }

    /**
     * Check if a logbook_code already exists in the database.
     */
    private function logbookCodeExists(string $code): bool
    {
        return DB::table('logbooks')->where('logbook_code', $code)->exists();
    }
}
