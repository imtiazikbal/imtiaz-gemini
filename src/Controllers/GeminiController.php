<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Imtiaz\LaravelGemini\Gemini\GeminiApi;

class GeminiController extends Controller
{
    public function summarizeDocument(Request $request)
    {
        try {
            // Validate that the file is one of the accepted types (excluding xlsx)
            $validator = Validator::make($request->all(), [

                'file' => 'required|mimes:pdf,txt,html,css,csv,xml,rtf|max:10240', // max 10MB, excluding xlsx
                'prompt' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }

            // Retrieve the uploaded file
            $file = $request->file('file');
            $prompt = $request->input('prompt', 'Summarize this document');

            // Call the service to get the document summary
            try {
                $summary = GeminiApi::summarizeDocument($file, $prompt);

                // Store the response
                //$this->storeResponse($summary, $prompt);

                return response()->json(['summary' => $summary]);

            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to generate summary. ' . $e->getMessage()], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    

    /**
     * Send a request to the API with the encoded file and prompt to get the summary.
     *
     * @param  string  $encodedFile
     * @param  string  $mimeType
     * @param  string  $prompt
     * @return string|null
     */
    private function getSummaryFromApi($encodedFile, $mimeType, $prompt)
    {
        try {
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
                // Handle exception (e.g., log error)
            }

            return null;

        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // // store the response in the database
    // private function storeResponse($summary, $prompt)
    // {
    //     try {
    //         // Store the response in the database
    //         $responseModel = new GeminiFile();
    //         $responseModel->response = $summary;
    //         $responseModel->prompt = $prompt;
    //         $responseModel->save();
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }

}




