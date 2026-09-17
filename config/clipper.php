<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Speech-to-Text Provider
    |--------------------------------------------------------------------------
    |
    | Provider abstraction for transcription. Supported: "openai", "assemblyai".
    | Set STT_PROVIDER in .env.
    |
    */

    'stt' => [
        'provider' => env('STT_PROVIDER', 'local'),
        'api_key' => env('STT_API_KEY'),
        'local_binary' => env('STT_LOCAL_BINARY', 'whisper'),
        'local_model' => env('STT_LOCAL_MODEL', 'base'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LLM / Clip Analysis Provider
    |--------------------------------------------------------------------------
    |
    | Provider used for candidate clip analysis. Supported: "openai".
    |
    */

    'llm' => [
        'provider' => env('LLM_PROVIDER', 'openai'),
        'api_key' => env('LLM_API_KEY'),
        // Max transcript characters sent per analysis call (cost control).
        'max_transcript_chars' => env('LLM_MAX_TRANSCRIPT_CHARS', 20000),
        // Target number of clip candidates per analysis run.
        'target_candidates' => env('LLM_TARGET_CANDIDATES', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | FFmpeg / FFprobe Binaries
    |--------------------------------------------------------------------------
    */

    'ffmpeg' => env('FFMPEG_BINARY', 'ffmpeg'),
    'ffprobe' => env('FFPROBE_BINARY', 'ffprobe'),
    'ytdlp' => env('YTDLP_BINARY', '/Library/Frameworks/Python.framework/Versions/3.12/bin/yt-dlp'),

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    */

    'upload' => [
        // Maximum upload size in bytes (default: 2 GB).
        'max_bytes' => env('UPLOAD_MAX_BYTES', 2 * 1024 * 1024 * 1024),
        // Maximum source video duration in seconds (default: 3 hours).
        'max_duration_s' => env('UPLOAD_MAX_DURATION_S', 10800),
        'allowed_mimes' => ['video/mp4', 'video/quicktime', 'video/webm'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Clip Candidate Constraints
    |--------------------------------------------------------------------------
    */

    'candidates' => [
        'min_duration_s' => env('CANDIDATE_MIN_DURATION_S', 30),
        'max_duration_s' => env('CANDIDATE_MAX_DURATION_S', 90),
    ],

    'quotas' => [
        'free' => (float) env('QUOTA_FREE_MINUTES', 60),
        'beta' => null,
        'pro' => null,
        'agency' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caption Presets (system slugs)
    |--------------------------------------------------------------------------
    */

    'caption_presets' => [
        'clean',
        'bold_dynamic',
        'karaoke_highlight',
    ],

];
