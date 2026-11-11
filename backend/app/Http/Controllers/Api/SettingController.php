<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponse;

    /**
     * Get all settings as key-value pairs
     */
    public function index(): JsonResponse
    {
        $settings = Setting::all()->pluck('value', 'key');

        return $this->successResponse($settings, 'Pengaturan toko berhasil diambil');
    }

    /**
     * Update or create multiple settings
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->except(['_token']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }

        $settings = Setting::all()->pluck('value', 'key');

        return $this->successResponse($settings, 'Pengaturan toko berhasil diperbarui');
    }
}
