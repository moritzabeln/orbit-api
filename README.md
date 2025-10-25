# Orbit API Documentation

A PHP-based REST API for managing user file uploads and retrieval for the Orbit App.

**Base URL:** `https://your-domain.com`

---

## 🔐 Authentication

All API requests require authentication via an API key passed in the request header.

**Header:**
```
X-API-KEY: your_api_key_here
```

**Error Response (401 Unauthorized):**
```json
{
  "error": "Unauthorized"
}
```

---

## 📋 API Endpoints

### 1. Upload File

Upload a file for a specific user with a custom filename identifier.

**Endpoint:** `POST /api/v1/files/upload`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `userId` | integer | Yes | The ID of the user uploading the file |
| `filename` | string | Yes | A custom identifier/key for the file (e.g., "profile_picture", "document_1") |
| `file` | file | Yes | The file to upload (multipart/form-data) |

**Headers:**
```
X-API-KEY: your_api_key_here
Content-Type: multipart/form-data
```

**Example Request (cURL):**
```bash
curl -X POST https://your-domain.com/api/v1/files/upload \
  -H "X-API-KEY: your_api_key_here" \
  -F "userId=123" \
  -F "filename=profile_picture" \
  -F "file=@/path/to/your/image.jpg"
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "file_url": "/uploads/123_profile_picture_64a5f9e8b1c2d_image.jpg"
}
```

**Error Responses:**

*400 Bad Request:*
```json
{
  "error": "Missing userId, filename, or file"
}
```

*500 Internal Server Error:*
```json
{
  "error": "File upload failed"
}
```
or
```json
{
  "error": "DB insert failed"
}
```

**Important Notes:**
- If a file with the same `userId` and `filename` combination already exists, it will be **overwritten**.
- The stored filename format is: `{userId}_{filename}_{uniqid}_{original_filename}`

---

### 2. Retrieve File

Download a previously uploaded file for a specific user.

**Endpoint:** `GET /api/v1/files/get`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `userId` | integer | Yes | The ID of the user who owns the file |
| `filename` | string | Yes | The custom identifier/key used during upload |

**Headers:**
```
X-API-KEY: your_api_key_here
```

**Example Request (cURL):**
```bash
curl -X GET "https://your-domain.com/api/v1/files/get?userId=123&filename=profile_picture" \
  -H "X-API-KEY: your_api_key_here" \
  -O -J
```

**Example Request (Browser/Direct Link):**
```
https://your-domain.com/api/v1/files/get?userId=123&filename=profile_picture
```
*Note: Browser requests must include the API key header, which may require a proxy or browser extension.*

**Success Response (200 OK):**
- Returns the file with appropriate `Content-Type` header
- File is served with original filename
- Headers include:
  - `Content-Type`: Detected MIME type (e.g., `image/jpeg`, `application/pdf`)
  - `Content-Disposition`: `attachment; filename="original_filename.ext"`
  - `Content-Length`: File size in bytes

**Error Responses:**

*400 Bad Request:*
```json
{
  "error": "Missing userId or filename"
}
```

*404 Not Found:*
```json
{
  "error": "File not found"
}
```
or
```json
{
  "error": "File not found on server"
}
```

*500 Internal Server Error:*
```json
{
  "error": "DB query failed"
}
```

---

## 📝 Code Examples

### JavaScript (Fetch API)

**Upload File:**
```javascript
const formData = new FormData();
formData.append('userId', '123');
formData.append('filename', 'profile_picture');
formData.append('file', fileInput.files[0]);

fetch('https://your-domain.com/api/v1/files/upload', {
  method: 'POST',
  headers: {
    'X-API-KEY': 'your_api_key_here'
  },
  body: formData
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

**Download File:**
```javascript
fetch('https://your-domain.com/api/v1/files/get?userId=123&filename=profile_picture', {
  headers: {
    'X-API-KEY': 'your_api_key_here'
  }
})
.then(response => response.blob())
.then(blob => {
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'downloaded_file';
  a.click();
});
```

---

## ⚠️ Error Handling

All errors return appropriate HTTP status codes:

| Status Code | Meaning |
|-------------|---------|
| 200 | Success |
| 400 | Bad Request - Missing or invalid parameters |
| 401 | Unauthorized - Invalid or missing API key |
| 404 | Not Found - File or route doesn't exist |
| 500 | Internal Server Error - Database or file system error |

All error responses follow this format:
```json
{
  "error": "Error message description"
}
```

---

## 🔄 Versioning

The API is versioned with the path `/api/v1/`. Future versions will be available at `/api/v2/`, `/api/v3/`, etc.

---