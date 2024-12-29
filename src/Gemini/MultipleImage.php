<?php
namespace Imtiaz\LaravelGemini\Gemini;


use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MultipleImage
{


public static function handleImageUpload($files,$prompt,$model)
{
    $fileUris = [];
    $tempPaths = [];

    try {
        // Save files temporarily and upload them
        foreach ($files as $index => $image) {
            $tempPath = $image->store('temp');
            $tempPaths[] = $tempPath;
            $fullPath = Storage::path($tempPath);

            // Get file metadata
            $fileData = [
                'mime_type' => mime_content_type($fullPath),
                'file_size' => filesize($fullPath),
            ];

            // Upload to the external API
            $displayName = "Uploaded_Image_" . ($index + 1);
            $fileUri = self::uploadToApi($fullPath, $displayName, $fileData);

            if (!$fileUri) {
                Log::error('File upload failed for image: ' . $displayName);
                return response()->json(['error' => 'File upload failed for one or more images.'], 500);
            }

            // Append file data to the $fileUris array
            $fileUris[] = [
                'file_uri' => $fileUri,
                'mime_type' => $fileData['mime_type'],
            ];
        }

        // Generate content based on all uploaded images
        $response = self::generateContent($fileUris, $prompt ,$model);

        if (!$response) {
            Log::error('Content generation failed.');
            return response()->json(['error' => 'Content generation failed.'], 500);
        }

        return response()->json($response);

    } catch (\Exception $e) {
        Log::error('An error occurred during file handling or upload.', [
            'exception' => $e->getMessage(),
        ]);
        return response()->json(['error' => 'An unexpected error occurred.'], 500);

    } finally {
        // Clean up temp files
        Storage::delete($tempPaths);
    }
}


private static function uploadToApi($filePath, $displayName, $fileData)
{
    $baseUrl = env('API_BASE_URL');
    $apiKey = env('GOOGLE_API_KEY');

    try {
        // Step 1: Initialize upload
        $initResponse = Http::withHeaders([
            'X-Goog-Upload-Protocol' => 'resumable',
            'X-Goog-Upload-Command' => 'start',
            'X-Goog-Upload-Header-Content-Length' => $fileData['file_size'],
            'X-Goog-Upload-Header-Content-Type' => $fileData['mime_type'],
        ])->post("$baseUrl/upload/v1beta/files?key=$apiKey", [
            'file' => ['display_name' => $displayName],
        ]);

        if ($initResponse->failed()) {
            Log::error('Initialization failed', ['response' => $initResponse->body()]);
            return null;
        }

        $uploadUrl = $initResponse->header('X-Goog-Upload-URL');
        if (!$uploadUrl) {
            Log::error('Failed to retrieve upload URL', ['response' => $initResponse->body()]);
            return null;
        }

        // Step 2: Upload the file
        $fileContent = file_get_contents($filePath);
        $uploadResponse = Http::withHeaders([
            'Content-Length' => $fileData['file_size'],
            'X-Goog-Upload-Offset' => 0,
            'X-Goog-Upload-Command' => 'upload, finalize',
        ])->withBody($fileContent, $fileData['mime_type'])->post($uploadUrl);

        if ($uploadResponse->failed()) {
            Log::error('Upload failed', ['response' => $uploadResponse->body()]);
            return null;
        }

        return $uploadResponse->json()['file']['uri'] ?? null;
    } catch (\Exception $e) {
        Log::error('An error occurred during file upload.', [
            'exception' => $e->getMessage(),
            'file_path' => $filePath,
        ]);
        return null;
    }
}

private static function generateContent(array $fileUris, $prompt, $model)
{
    $baseUrl = env('API_BASE_URL');
    $apiKey = env('GOOGLE_API_KEY');

    // Create the file data array for API request
    $fileDataParts = array_map(function ($fileUri) {
        return ['file_data' => ['mime_type' => $fileUri['mime_type'], 'file_uri' => $fileUri['file_uri']]];
    }, $fileUris);

    // Log the request data for debugging
    Log::info('Request File Data Parts:', $fileDataParts);

    try {
        $response = Http::post("$baseUrl/v1beta/models/$model:generateContent?key=$apiKey", [
            'contents' => [
                'parts' => array_merge($fileDataParts, [['text' => $prompt]]),
            ],
        ]);
      

        // Log the API response for debugging
        Log::info('Full API Response:', $response->json());

        return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
    } catch (\Exception $e) {
        Log::error('Error during content generation:', [
            'exception' => $e->getMessage(),
        ]);
        return null;
    }
}

}
