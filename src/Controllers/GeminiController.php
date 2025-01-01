<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Imtiaz\LaravelGemini\Gemini\GeminiApi;
use Imtiaz\LaravelGemini\Gemini\MultiPdfUpload;
use Imtiaz\LaravelGemini\Gemini\MultipleImage;


use App\Models\Chat;
use Illuminate\Support\Facades\Storage;

class GeminiController extends Controller
{

    public function view(){
        return view("gemini-file");
    }
    public function summarizeSingleDocument(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:pdf,txt,text/plain,html,css,csv,xml,rtf|max:10240',
            'prompt' => 'required|string',
            'model' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . json_encode($validator->errors()));
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if (!$request->hasFile('file')) {
            Log::error('No file was uploaded.');
            return response()->json(['error' => 'No file was uploaded'], 400);
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            Log::error('Uploaded file is not valid: ' . $file->getErrorMessage());
            return response()->json(['error' => 'Uploaded file is not valid'], 400);
        }

        Log::info('Uploaded file name: ' . $file->getClientOriginalName());
        Log::info('Uploaded file size: ' . $file->getSize());
        Log::info('Uploaded file MIME type: ' . $file->getMimeType());

        $prompt = $request->input('prompt', 'Summarize this document');
        $model = $request->input('model', 'gemini-1.5-flash');

        try {
            $summary = GeminiApi::summarizeDocument($file, $prompt, $model);

            $this->storeResponse($summary, $prompt, 'file_url', 1);

            return response()->json([
                'status' => 'success',
                'data' => $summary,
                'status_code' => 200
            ]);

        } catch (\Exception $e) {
            Log::error('Error summarizing document: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate summary. ' . $e->getMessage()], 400);
        }

    } catch (\Exception $e) {
        Log::error('General error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 400);
    }
}



//  summarize multiple documents
public function summarizeMultiplePdfDocument(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'pdf' => 'required|array', // Expect an array of files
            'pdf.*' => 'required|mimes:pdf|max:10240', // max 20MB, excluding xlsx
            'prompt' => 'required|string',
            'model' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . json_encode($validator->errors()));
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $file = $request->file('pdf');
        $prompt = $request->input('prompt' );
        $model = $request->input('model');

        try {
              $summary =  MultiPdfUpload::handleUpload($file, $prompt,$model);

                   // Store the response
                $this->storeResponse($summary->getOriginalContent(), $prompt,'file_url',1);
                $response   = [
                    'status' => 'success',
                    'data'=> $summary->getOriginalContent(),
                    'status_code' => 200
                ];
                return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error summarizing document: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate summary. ' . $e->getMessage()], 400);
        }

    } catch (\Exception $e) {
        Log::error('General error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 400);
    }
}

    // get user documents and responses
    public function documentsResponses(){
        try {
            $user_id = 1; // Replace with the authenticated user's ID 
            $chats = Chat::where('user_id', $user_id)->get();
            $response   = [
                'status' => 'success',
                'chats'=> $chats,
                'status_code' => 200
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    // store the respone in the database
    private function storeResponse($data,$prompt,$file_url,$user_id){
        try{

            $chat = Chat::create([
                'prompt' => $prompt,
                'response' => $data,
                'user_id'=> $user_id,
                'file_url'=> $file_url
            ]);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 400);
        }

    }
   
    
    public function summarizeImages(Request $request){
        try {
            
            // Validate that the file is one of the accepted types (excluding xlsx)
            $validator = Validator::make($request->all(), [
                'images' => 'required|array', // Expect an array of files
                'images.*' => 'required|file|image|mimes:jpeg,png,jpg,webp,heic,heif|max:5120', // Limit to 5MB per image
                'prompt' => 'nullable|string',
                'model' => 'nullable|string'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
             // Store the uploaded file locally
            $files = $request->file('images');
            // Retrieve the uploaded file
            $prompt = $request->input('prompt');
            $model = $request->input('model');
            // Call the service to get the document summary
            try {
                  $summary =  MultipleImage::handleImageUpload($files, $prompt,$model);
            
                   // Store the response
                $this->storeResponse($summary->getOriginalContent(), $prompt,'file_url',1);
                $response   = [
                    'status' => 'success',
                    'data'=> $summary->getOriginalContent(),
                    'status_code' => 200
                ];
                return response()->json($response);

                
               
               
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to generate summary. ' . $e->getMessage()], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

    }  


    public function handleVideoUploadForGemini(Request $request)
    {
    
        // Validate uploaded video
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,mkv,avi|max:102400', // Limit to 100MB per video
        ]);

       
        
        // Get the uploaded video file
        $video = $request->file('video');
        $videoPath = $video->getRealPath();
        $fileName = $video->getClientOriginalName();
        $mimeType = mime_content_type($videoPath);
        $fileSize = filesize($videoPath);

        // Define the Google API details
        $googleApiKey = env('GOOGLE_API_KEY');
        $baseUrl = env('GOOGLE_API_BASE_URL');
        
        // Step 1: Start the resumable upload request
        $response = Http::withHeaders([
            'X-Goog-Upload-Protocol' => 'resumable',
            'X-Goog-Upload-Command' => 'start',
            'X-Goog-Upload-Header-Content-Length' => $fileSize,
            'X-Goog-Upload-Header-Content-Type' => $mimeType,
            'Content-Type' => 'application/json',
        ])
        ->post("{$baseUrl}/upload/v1beta/files?key={$googleApiKey}", [
            'file' => [
                'display_name' => $fileName,
            ],
        ]);

        // Parse the upload URL from the response headers
        $uploadUrl = $response->header('X-Goog-Upload-Url');
        
        if (!$uploadUrl) {
            return response()->json(['error' => 'Failed to initiate upload'], 500);
        }

        // Step 2: Upload the video to Google Cloud in chunks
        $fileHandle = fopen($videoPath, 'rb');
        $chunkSize = 5 * 1024 * 1024; // 5MB per chunk

        $offset = 0;
        while (!feof($fileHandle)) {
            $chunk = fread($fileHandle, $chunkSize);
            
            $uploadResponse = Http::withHeaders([
                'Content-Length' => strlen($chunk),
                'X-Goog-Upload-Offset' => $offset,
                'X-Goog-Upload-Command' => 'upload, finalize',
            ])
            ->withBody($chunk, 'application/octet-stream')
            ->post($uploadUrl);
            
            // Increment the offset by the size of the chunk
            $offset += strlen($chunk);

            if ($uploadResponse->failed()) {
                fclose($fileHandle);
                return response()->json(['error' => 'Failed to upload video chunk'], 500);
            }
        }
        fclose($fileHandle);

        // Step 3: Check if the video processing is complete
        $fileInfoResponse = Http::get("https://generativelanguage.googleapis.com/v1beta/files/{$fileName}?key={$googleApiKey}");
        $fileInfo = $fileInfoResponse->json();
        
        $state = $fileInfo['file']['state'] ?? null;
        while ($state == 'PROCESSING') {
            Log::info('Processing video...');
            sleep(5); // Wait for 5 seconds before checking the status again
            
            $fileInfoResponse = Http::get("https://generativelanguage.googleapis.com/v1beta/files/{$fileName}?key={$googleApiKey}");
            $fileInfo = $fileInfoResponse->json();
            $state = $fileInfo['file']['state'] ?? null;
        }

        // Step 4: Generate content based on the uploaded video
        $fileUri = $fileInfo['file']['uri'] ?? null;

        if (!$fileUri) {
            return response()->json(['error' => 'Failed to get file URI for content generation'], 500);
        }

        $generateContentResponse = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$googleApiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Describe this video clip'],
                        ['file_data' => ['mime_type' => $mimeType, 'file_uri' => $fileUri]],
                    ],
                ],
            ],
        ]);

        // Step 5: Parse and return the generated content
        $responseJson = $generateContentResponse->json();
        $description = $responseJson['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$description) {
            return response()->json(['error' => 'Content generation failed'], 500);
        }

        return response()->json(['description' => $description]);
    }

  
}




