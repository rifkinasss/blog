<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpenClawMediaController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $token = $request->attributes->get('apiToken');

        if ($token->service_account_id) {
            abort_unless(filter_var(SiteSetting::value('allow_upload_media', '1'), FILTER_VALIDATE_BOOLEAN), 403, 'Media upload is disabled by publishing policy.');
        }

        $request->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);
        $path = $request->file('image')->store('covers', 'public');
        AuditLog::record('media.uploaded_via_api', null, ['path' => $path, 'service_account_id' => $token->service_account_id]);

        return response()->json(['data' => ['path' => $path, 'url' => asset('storage/'.$path)]], 201);
    }
}
