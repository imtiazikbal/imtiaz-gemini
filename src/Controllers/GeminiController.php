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




