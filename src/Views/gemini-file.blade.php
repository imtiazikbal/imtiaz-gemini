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
