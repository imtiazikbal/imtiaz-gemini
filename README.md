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
    ```

4. **Create gemini.php in  congig **
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
    </style>
</head>
<body>
    <form action="{{route('summarizeDocument')}}" method="POST" enctype="multipart/form-data">
        <h1>Upload File with Prompt</h1>
        @csrf <!-- Laravel's CSRF protection token -->
        
        <!-- File Input -->
        <div>
            <label for="file">Upload File (Allowed: PDF, TXT, HTML, CSS, CSV, XML, RTF | Max: 10MB)</label>
            <input type="file" name="file" id="file" required accept=".pdf,.txt,.html,.css,.csv,.xml,.rtf">
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
use Imtiaz\LaravelGemini\Gemini\GeminiApi;
use App\Models\Chat;


class GeminiController extends Controller
{

    public function view(){
        return view("gemini-file");
    }
    public function summarizeDocument(Request $request)
    {

        try {
            
            // Validate that the file is one of the accepted types (excluding xlsx)
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:pdf,txt,html,css,csv,xml,rtf|max:10240', // max 20MB, excluding xlsx
                'prompt' => 'required|string'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
             // Store the uploaded file locally
            $file = $request->file('file');
            // Retrieve the uploaded file
            $prompt = $request->input('prompt', 'Summarize this document');
        
            // Call the service to get the document summary
            try {
                $summary = GeminiApi::summarizeDocument($file, $prompt);
                // Store the response
                $this->storeResponse($summary, $prompt,'file_url',1);
                $response   = [
                    'status' => 'success',
                    'data'=> $summary,
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

### **Chat Model**

The `Chat` model represents the `chats` table where the data for user prompts, responses, and file URLs are stored.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'prompt',
        'response',
        'file_url'
    ];
}
