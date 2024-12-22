# Gemini API Integration for Document Summarization

This Laravel project integrates the Gemini API to summarize documents uploaded by users. The application allows users to upload various types of files (such as PDF, TXT, HTML, etc.) and provides a summarized version of the document using the Gemini AI model from Google. 

## Development Process

### 1. **GeminiController Development**

The `GeminiController` handles the document summarization functionality. It includes two main functions:

#### `summarizeDocument(Request $request)`
This method handles the document summarization process:
- **File Validation:** It ensures the uploaded file meets the allowed types and size restrictions (max 10MB, excluding `.xlsx`).
- **Prompt Validation:** It validates that a `prompt` string is provided to guide the summarization (default is "Summarize this document").
- **API Interaction:** The method calls `GeminiApi::summarizeDocument()` to communicate with the Gemini API, which processes the file and returns a summary.
- **Error Handling:** The method handles errors both from file validation and API communication, returning appropriate error messages.

### Form Data

The `/summarize` endpoint expects the following form data:

1. **File**: The document to be summarized.
   - **Field Name**: `file`
   - **Type**: `file`
   - **Allowed Types**: PDF, TXT, HTML, CSS, CSV, XML, RTF
   - **Max File Size**: 10MB

2. **Prompt**: The prompt to guide the summarization.
   - **Field Name**: `prompt`
   - **Type**: `string`
   - **


### route
```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/summarize', [App\Http\Controllers\GeminiController::class, 'summarizeDocument']);
```
## Confiq setup
 config/gemini.php:

```php
return [
    'api_key' => env('GOOGLE_API_KEY', 'laravel'),
];
```
## Update env 
GOOGLE_API_KEY=your_key


##### Example of the summarizeDocument method:
```php
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Imtiaz\LaravelGemini\Gemini\GeminiApi;

public function summarizeDocument(Request $request)
{
    try {
        // Validate file and prompt
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:pdf,txt,html,css,csv,xml,rtf|max:10240', // max 10MB
            'prompt' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $prompt = $request->input('prompt', 'Summarize this document');

        // Call Gemini API to summarize the document
        $summary = GeminiApi::summarizeDocument($file, $prompt);
        
        return response()->json(['summary' => $summary]);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to generate summary. ' . $e->getMessage()], 400);
    }
}


