# Gemini Document Summarizer

This project is a Laravel-based solution to upload documents and generate summaries using the Gemini API. Users can upload documents (PDF, TXT, HTML, CSS, CSV, XML, RTF) and get summaries based on the provided prompt. The system stores user interactions and responses in the database for future reference.

## Prerequisites

Before you begin, ensure that you have the following installed:

- [PHP >= 8.0](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Laravel 8.x or higher](https://laravel.com/)
- [MySQL](https://www.mysql.com/) or another supported database

## Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/your-repo-name.git
    cd your-repo-name
    ```

2. **Install dependencies**

    Install the required PHP packages using Composer:

    ```bash
    composer install
    ```

3. **Create `.env` file**

    Copy the `.env.example` file to `.env`:

    ```bash
    cp .env.example .env
    ```

    Open the `.env` file and configure your database settings:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_username
    DB_PASSWORD=your_database_password
    ```

4. **Generate application key**

    Generate the application key:

    ```bash
    php artisan key:generate
    ```

5. **Run the migrations**

    Create the necessary database tables by running:

    ```bash
    php artisan migrate
    ```

6. **Install Gemini API package**

    The project uses the Gemini API package by [imtiaz/gemini](https://github.com/imtiaz/gemini). Install it via Composer:

    ```bash
    composer require imtiaz/gemini
    ```

7. **Start the server**

    Finally, start the development server:

    ```bash
    php artisan serve
    ```

    The application will be available at `http://localhost:8000`.

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
