<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->passes(fn () => DB::select('select 1')),
            'cache' => $this->passes(function (): void {
                Cache::put('health:probe', true, now()->addMinute());
                Cache::forget('health:probe');
            }),
            'storage' => $this->passes(function (): void {
                throw_unless(is_writable(storage_path('app/public')), \RuntimeException::class, 'Public storage is not writable.');
                Storage::disk('public')->exists('health-probe-does-not-exist');
            }),
        ];

        $status = in_array(false, $checks, true) ? 'degraded' : 'healthy';

        return response()->json(['status' => $status], $status === 'healthy' ? 200 : 503);
    }

    private function passes(callable $check): bool
    {
        try {
            $check();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
