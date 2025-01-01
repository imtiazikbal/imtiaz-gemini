<?php
namespace Imtiaz\LaravelGemini\Gemini;


use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MultiPdfUpload
{

    public static function handleUpload($files, $prompt, $model)
    {
        foreach ($files as $file) {
            // Check if the file is valid
            if (!$file->isValid()) {
                Log::error('Invalid file uploaded');
                return response()->json(['error' => 'Invalid file uploaded.'], 400);
            }
        
            // Validate that the file is a PDF
            if ($file->getMimeType() !== 'application/pdf') {
                Log::error('Invalid file type uploaded: ' . $file->getClientOriginalName());
                return response()->json(['error' => 'Only PDF files are allowed.'], 400);
            }
        }
        
        $fileUris = [];
        $tempPaths = [];

        try {
            // Save files temporarily and upload them
            foreach ($files as $index => $pdf) {
                $tempPath = $pdf->store('temp');
                $tempPaths[] = $tempPath;
                $fullPath = Storage::path($tempPath);

                // Get file metadata
                $fileData = [
                    'mime_type' => mime_content_type($fullPath),
                    'file_size' => filesize($fullPath),
                ];

                // Upload to the external API
                $displayName = "Uploaded_File_" . ($index + 1);
                $fileUri = self::uploadToApi($fullPath, $displayName, $fileData);

                if (!$fileUri) {
                    Log::error('File upload failed for file: ' . $displayName);
                    return response()->json(['error' => 'File upload failed for one or more files.'], 500);
                }

                $fileUris[] = $fileUri;
            }

            // Generate content based on all uploaded files
            $response = self::generateContent($fileUris, $prompt,$model);

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

    private static function generateContent(array $fileUris, $prompt,$model)
    {
        $baseUrl = env('API_BASE_URL');
        $apiKey = env('GOOGLE_API_KEY');

        // Create the file data array for API request
        $fileDataParts = array_map(function ($fileUri) {
            return ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $fileUri]];
        }, $fileUris);

        $response = Http::post("$baseUrl/v1beta/models/$model:generateContent?key=$apiKey", [
            'contents' => [
                'parts' => array_merge($fileDataParts, [['text' => $prompt]]),
            ],
        ]);



        return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }



}
