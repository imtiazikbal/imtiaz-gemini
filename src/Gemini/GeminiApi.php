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

        $fileSize = $file->getSize();

        $fileSizeInMB = $fileSize / 1024 / 1024;  // Convert size to MB


        $fileContents = $file;
        $t = time();
        $fileContentsName = $fileContents->getClientOriginalName();
        $fileName = "news-{$t}-{$fileContentsName}";
        $img_url = "uploads/files/{$fileName}";
        // Upload File
        $fileContents->move(public_path('uploads/news'), $fileName);
        $file_path = public_path('uploads/files/' . $fileName);

        // // Return the file path to the user to allow summarization
        // return response()->json([
        //     'message' => 'File uploaded successfully!',
        //     'file_path' => asset($img_url),
        // ]);

        if ($fileSizeInMB > 19) {
            return self::LargePDFsummarizeFile($file_path, $prompt);
        }


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
        $googleApiKey = env('GOOGLE_API_KEY'); // Ensure your API key is stored in .env

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

      // Summarize the uploaded PDF file
      public static function LargePDFsummarizeFile($filePath, $prompt)
      {
  
          if (!Storage::exists($filePath)) {
              return response()->json(['error' => 'File not found'], 404);
          }
  
          // Get the file content from local storage
          $fileContent = Storage::get($filePath);
  
          // Get the file's MIME type and size
          $mimeType = 'application/pdf';
          $numBytes = Storage::size($filePath);
  
          // Start resumable upload by defining metadata
          $googleApiKey = env('GOOGLE_API_KEY');
          $response = Http::withHeaders([
              'X-Goog-Upload-Protocol' => 'resumable',
              'X-Goog-Upload-Command' => 'start',
              'X-Goog-Upload-Header-Content-Length' => $numBytes,
              'X-Goog-Upload-Header-Content-Type' => $mimeType,
              'Content-Type' => 'application/json',
          ])
          ->post("https://www.googleapis.com/upload/v1beta/files?key={$googleApiKey}", [
              'file' => ['display_name' => pathinfo($filePath, PATHINFO_FILENAME)]
          ]);
  
          // Parse the upload URL from the response headers
          $uploadUrl = $response->header('X-Goog-Upload-Url');
  
          // Upload the file to Google Cloud Storage
          $uploadResponse = Http::withHeaders([
              'Content-Length' => $numBytes,
              'X-Goog-Upload-Offset' => 0,
              'X-Goog-Upload-Command' => 'upload, finalize',
          ])
          ->withBody($fileContent, $numBytes)
          ->post($uploadUrl);
  
          // Get the file URI from the response
          $fileInfo = $uploadResponse->json();
          $fileUri = $fileInfo['file']['uri'];
  
          // Use the uploaded file to generate content using Google API
          $response = Http::withHeaders([
              'Content-Type' => 'application/json'
          ])
          ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$googleApiKey}", [
              'contents' => [
                  [
                      'parts' => [
                          ['text' => $prompt],
                          ['file_data' => [
                              'mime_type' => $mimeType,
                              'file_uri' => $fileUri
                          ]],
                      ]
                  ]
              ]
          ]);
  
          // Parse and return the generated content
          $responseData = $response->json();
          $generatedContent = $responseData['candidates'][0]['content']['parts'][0]['text'];
  
          // Return the generated content as a response
          return response()->json([
              'generated_content' => $generatedContent,
          ]);
      }

}