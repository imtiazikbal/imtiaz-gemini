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
