<?php 
namespace Imtiaz\LaravelGemini\Gemini;


use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;


class GeminiApi
{
    /**
     * Summarize the uploaded document.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $prompt
     * @return string|null
     */
    public static function summarizeDocument($file, $prompt)
    {

        // Read the file content
        $fileData = file_get_contents($file->getRealPath());
        if (!$fileData) {
            throw new \Exception('Failed to read file.');
        }

        // Encode the file content in base64
        $encodedFile = base64_encode($fileData);
        $mimeType = $file->getMimeType();

        // Call the API to generate the summary
        return self::getSummaryFromApi($encodedFile, $mimeType, $prompt);
    }

    /**
     * Send a request to the API with the encoded file and prompt to get the summary.
     *
     * @param  string  $encodedFile
     * @param  string  $mimeType
     * @param  string  $prompt
     * @return string|null
     */
    private static function getSummaryFromApi($encodedFile, $mimeType, $prompt)
    {
        $googleApiKey = config('gemini.api_key');
   
        // API request payload
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $encodedFile]],
                        ['text' => $prompt],
                    ],
                ],
            ],
        ];

        // Send the API request
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$googleApiKey", $payload);

            if ($response->successful()) {
                $data = $response->json();
                // Extract and return the summary text from the API response
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }
        } catch (\Exception $e) {
            throw new \Exception('Error communicating with the API: ' . $e->getMessage());
        }

        return null;
    }

     

}