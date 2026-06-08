<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    protected $supabaseUrl;
    protected $anonKey;
    protected $bucket;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->anonKey = env('SUPABASE_ANON_KEY');
        $this->bucket = env('SUPABASE_BUCKET', 'studypilot-bucket');
    }

    /**
     * Upload a file to Supabase Storage.
     * Falls back to local storage if credentials are missing or default.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder Destination folder (e.g. 'materials' or 'avatars')
     * @return string URL of the uploaded file
     */
    public function upload($file, string $folder): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        // Check if Supabase URL is valid or default placeholder
        if (empty($this->supabaseUrl) || str_contains($this->supabaseUrl, 'your-project') || empty($this->anonKey)) {
            Log::info("Supabase credentials not set or placeholder. Saving file locally.");
            return $this->saveLocal($file, $folder, $filename);
        }

        try {
            // Upload to Supabase Storage via REST API
            // Endpoint: POST {supabaseUrl}/storage/v1/object/{bucket}/{path}
            $uploadUrl = rtrim($this->supabaseUrl, '/') . '/storage/v1/object/' . $this->bucket . '/' . $path;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->anonKey,
                'apiKey' => $this->anonKey,
                'Content-Type' => $file->getMimeType(),
            ])->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
              ->post($uploadUrl);

            if ($response->successful()) {
                Log::info("File successfully uploaded to Supabase Storage: $path");
                // Public URL
                return rtrim($this->supabaseUrl, '/') . '/storage/v1/object/public/' . $this->bucket . '/' . $path;
            } else {
                Log::error("Supabase Storage Upload Fail: " . $response->body());
                return $this->saveLocal($file, $folder, $filename);
            }
        } catch (\Exception $e) {
            Log::error("Supabase Storage Exception: " . $e->getMessage());
            return $this->saveLocal($file, $folder, $filename);
        }
    }

    /**
     * Save file locally as a fallback.
     */
    protected function saveLocal($file, string $folder, string $filename): string
    {
        $destinationPath = public_path('uploads/' . $folder);
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true, true);
        }

        $file->move($destinationPath, $filename);
        return 'uploads/' . $folder . '/' . $filename;
    }
}
