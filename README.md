# Gemini Intregation for File

This project is a Laravel-based solution to upload documents and generate summaries using the Gemini API. Users can upload documents (PDF, TXT, HTML, CSS, CSV, XML, RTF) and get summaries based on the provided prompt. The system stores user interactions and responses in the database for future reference.

## Prerequisites

Before you begin, ensure that you have the following installed:

- [PHP >= 8.2](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Laravel 11.x or higher](https://laravel.com/)
- [MySQL](https://www.mysql.com/) or another supported database

## Installation

1. **Install Gemini API package**

    The project uses the Gemini API package by [imtiaz/gemini](https://github.com/imtiaz/gemini). Install it via Composer:

    ```bash
    composer require imtiaz/gemini
    ```

3. **Create GOOGLE_API_KEY in  `.env` file**


    ```bash
    GOOGLE_API_KEY=your_api
    API_BASE_URL=https://generativelanguage.googleapis.com

    ```

4. **Create `gemini.php` in  confiq **
```
<?php

return [

    'api_key' => env('GOOGLE_API_KEY', 'laravel'),

];
```
4. **Create model and migrations**

    Generate the application key:

    ```bash
    php artisan make:model Chat -mc
`
5. Migrations file 
```
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->longText('prompt');
            $table->longText('response');
            $table->string('file_url');
```
6. Update model
```
 protected $fillable = [
        'user_id',
        'prompt',
        'response',
        'file_url'
    ];
```
5. **Run the migrations**

    Create the necessary database tables by running:

    ```bash
    php artisan migrate
    ```

7. Route example:

 ```
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('/view',[App\Http\Controllers\GeminiController::class, 'view']);

// single document
Route::post('/summarizeSingleDocument',action: [App\Http\Controllers\GeminiController::class, 'summarizeSingleDocument'])->name('singleDocument');
// multiple pdf document 
Route::post('/summarizeMultiplePdfDocument',action: [App\Http\Controllers\GeminiController::class, 'summarizeMultiplePdfDocument'])->name('multiplePdfDocument');
// multiple images
Route::post('/uploadMultipleImages', [App\Http\Controllers\GeminiController::class, 'summarizeImages'])->name('uploadMultipleImages');
Route::get('/getUserDocumentsResponses',[App\Http\Controllers\GeminiController::class, 'documentsResponses']);





```
7. **View file**

```
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload with Prompt</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        form div {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="file"]:focus,
        textarea:focus {
            border-color: #007BFF;
        }

        textarea {
            resize: none;
            height: 100px;
        }

        button {
            background: #007BFF;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        button:active {
            transform: scale(0.98);
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            form {
                padding: 15px;
            }

            button {
                width: 100%;
            }
        }
        .container {
            display: flex;
            gap: 20px;
            
        }
    </style>
</head>

<body class="container">
    <form action="{{ route('singleDocument') }}" method="POST" enctype="multipart/form-data">
        <h1>Single File with Prompt and Trained Model</h1>
        @csrf <!-- Laravel's CSRF protection token -->

        <!-- File Input -->
        <div>
            <label for="files">Upload Files (Allowed: PDF, TXT, HTML, CSS, CSV, XML, RTF | Max: 10MB)</label>
            <input type="file" name="file" id="files" required accept=".pdf,.txt,.html,.css,.csv,.xml,.rtf">
        </div>

        <div>
            <label for="files">Specify Gemini Trained Models</label>
            <a href="https://ai.google.dev/gemini-api/docs/models/gemini-models">Gemini Models</a>
            <br>
            <select name="model" id="models" class="form-control">
                <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
                <option value="gemini-2.0-flash-exp">Gemini 2.0 Flash</option>
                <option value="gemini-exp-1206">Gemini gemini-exp-1206</option>
                <option value="learnlm-1.5-pro-experimental">LearnLM 1.5 Pro Experimental</option>
                <option value="gemini-exp-1121">Gemini gemini-exp-1121</option>
                <option value="gemini-exp-1121">Gemini gemini-exp-1121</option>
                <option value="gemini-1.5-pro-exp-0827">Gemini 1.5 Pro gemini-1.5-pro-exp-0827</option>
                <option value="gemini-1.5-pro-exp-0801">Gemini 1.5 Pro</option>
                <option value="gemini-1.5-flash-8b-exp-0924">Gemini 1.5 Flash-8B</option>
                <option value="gemini-1.5-flash-8b-exp-0827">Gemini 1.5 Flash-8B</option>
            </select>
        </div>


        <!-- Prompt Input -->
        <div>
            <label for="prompt">Prompt</label>

            <textarea name="prompt" id="prompt" required placeholder="Enter your prompt here"></textarea>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit">Submit</button>
        </div>
    </form>

    <form action="{{ route('multiplePdfDocument') }}" method="POST" enctype="multipart/form-data">
       @csrf <!-- Laravel's CSRF protection token -->
        <h1>Multiple PDF with Prompt and Trained Model</h1>


        <!-- File Input -->
        <div>
            <label for="files">Upload PDF (Allowed: Only PDF | Max: 20MB )</label>
            <input type="file" name="pdf[]" id="files" required accept=".pdf" multiple>
        </div>
        

        <div>

            <label for="files">Specify Gemini Trained Models</label>
            <a href="https://ai.google.dev/gemini-api/docs/models/gemini-models">Gemini Models</a>
            <br>
            <select name="model" id="models" class="form-control">
                <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
                <option value="gemini-2.0-flash-exp">Gemini 2.0 Flash</option>
                <option value="gemini-exp-1206">Gemini gemini-exp-1206</option>
                <option value="learnlm-1.5-pro-experimental">LearnLM 1.5 Pro Experimental</option>
                <option value="gemini-exp-1121">Gemini gemini-exp-1121</option>
                <option value="gemini-exp-1121">Gemini gemini-exp-1121</option>
                <option value="gemini-1.5-pro-exp-0827">Gemini 1.5 Pro gemini-1.5-pro-exp-0827</option>
                <option value="gemini-1.5-pro-exp-0801">Gemini 1.5 Pro</option>
                <option value="gemini-1.5-flash-8b-exp-0924">Gemini 1.5 Flash-8B</option>
                <option value="gemini-1.5-flash-8b-exp-0827">Gemini 1.5 Flash-8B</option>
            </select>
        </div>


        <!-- Prompt Input -->
        <div>
            <label for="prompt">Prompt</label>

            <textarea name="prompt" id="prompt" required placeholder="Enter your prompt here"></textarea>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit">Submit</button>
        </div>
    </form>



    <form action="{{ route('uploadMultipleImages') }}" method="POST" enctype="multipart/form-data">
        @csrf <!-- Laravel's CSRF protection token -->
         <h1>Multiple Images with Prompt and Trained Model</h1>
 
 
         <!-- File Input -->
         <div>
             <label for="files">Upload Files (Allowed: JPEG, PNG, JPG, WebP, HEIC, HEIF | Max: 20MB )</label>
             <input type="file" name="images[]" id="files"  required accept=".jpeg,.jpg,.png,.webp,.heic,.heif" multiple>
         </div>
         
 
         <div>
             <label for="files">Specify Gemini Trained Models</label>
             <a href="https://ai.google.dev/gemini-api/docs/models/gemini-models">Gemini Models</a>
             <br>
             <select name="model" id="models" class="form-control">
                 <option value="gemini-1.5-flash">Gemini 1.5 Flash</option>
                 <option value="gemini-2.0-flash-exp">Gemini 2.0 Flash</option>
                 <option value="gemini-exp-1206">Gemini</option>
                 <option value="learnlm-1.5-pro-experimental">LearnLM 1.5 Pro Experimental</option>
                 <option value="gemini-exp-1121">Gemini</option>
                 <option value="gemini-exp-1114">Gemini</option>
                 <option value="gemini-1.5-pro-exp-0827">Gemini 1.5 Pro gemini-1.5-pro-exp-0827</option>
                 <option value="gemini-1.5-pro-exp-0801">Gemini 1.5 Pro</option>
                 <option value="gemini-1.5-flash-8b-exp-0924">Gemini 1.5 Flash-8B</option>
                 <option value="gemini-1.5-flash-8b-exp-0827">Gemini 1.5 Flash-8B</option>
             </select>
         </div>
 
 
         <!-- Prompt Input -->
         <div>
             <label for="prompt">Prompt</label>
 
             <textarea name="prompt" id="prompt" required placeholder="Enter your prompt here"></textarea>
         </div>
 
         <!-- Submit Button -->
         <div>
             <button type="submit">Submit</button>
         </div>
     </form>
</body>

</html>


```
8. # Controller
```
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





```

   
---

## API Endpoints

### 1. **GET /view**
   - **Description**: Returns the view for interacting with the Gemini service.
   - **Method**: GET
   - **Response**: A view (HTML form) for document upload.

### 2. **POST /summarizeDocument**
   - **Description**: Upload a document and receive a summary based on the provided prompt.
   - **Method**: POST
   - **Parameters**:
     - `file` (required): The document to summarize (PDF, TXT, HTML, CSS, CSV, XML, RTF).
     - `prompt` (required): A string that describes the summarization task.
   - **Response**:
     - `status`: The status of the request (`success` or `error`).
     - `data`: The summary returned from the Gemini API.
     - `status_code`: HTTP status code.
   - **Example Request**:

     ```bash
     curl -X POST -F "file=@path/to/document.pdf" -F "prompt=Summarize this document" http://localhost:8000/summarizeDocument
     ```

### 3. **GET /getUserDocumentsResponses**
   - **Description**: Retrieves the user's previous documents and responses stored in the database.
   - **Method**: GET
   - **Response**:
     - `status`: The status of the request (`success` or `error`).
     - `chats`: A list of documents and responses for the authenticated user.
     - `status_code`: HTTP status code.
   - **Example Request**:

     ```bash
     curl http://localhost:8000/getUserDocumentsResponses
     ```

---

## Code Explanation

### **GeminiController**

The controller is responsible for handling the document upload, summarization, and storing of responses.

- **view()**: Returns the view for uploading documents.
- **summarizeDocument(Request $request)**: Validates the file and prompt, sends them to the Gemini API for summarization, and stores the response in the database.
- **documentsResponses()**: Retrieves all previous document interactions for a user.
- **storeResponse($data, $prompt, $file_url, $user_id)**: Stores the summary and interaction data in the `chats` table.


