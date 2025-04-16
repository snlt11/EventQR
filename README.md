# Event QR API Documentation

## Base URL

`https://localhost:8000/api`

## Authentication

### Login

-   **URL:** `/login`
-   **Method:** `POST`
-   **Default User:**
    -   Email: `support@admin.com`
    -   Password: `support`
-   **Description:** Authenticate a user

#### Request Body

```json
{
    "email": "john@example.com",
    "password": "securepassword"
}
```

#### Response

```json
{
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "user"
    }
}
```

### Generate Referral Code

-   **URL:** `/generate-referral-code`
-   **Method:** `POST`
-   **Description:** Generate a new referral code (admin only)
-   **Headers:** `Authorization: Bearer {token}`

#### Request Body

```json
{
    "expiration_datetime": "2023-12-31T23:59:59Z",
    "total_counts": 100
}
```

#### Response

```json
{
    "referral_code": "TECHCONF2023",
    "expires_at": "2023-12-31T23:59:59Z"
}
```

### Register

-   **URL:** `/register`
-   **Method:** `POST`
-   **Description:** Register a new user

#### Request Body

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword",
    "referral_code": "REF123"
}
```

#### Response

```json
{
    "user": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "user"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### Logout

-   **URL:** `/logout`
-   **Method:** `POST`
-   **Description:** Log out the authenticated user
-   **Headers:** `Authorization: Bearer {token}`

#### Response

```json
{
    "message": "Successfully logged out"
}
```

### Get User

-   **URL:** `/user`
-   **Method:** `GET`
-   **Description:** Get the authenticated user's information
-   **Headers:** `Authorization: Bearer {token}`

#### Response

```json
{
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user"
}
```

## Events

### Create Event

-   **URL:** `/event`
-   **Method:** `POST`
-   **Description:** Create a new event
-   **Headers:** `Authorization: Bearer {token}`

#### Request Body

```json
{
    "title": "Sample Event",
    "description": "This is a test event created for demonstration purposes.",
    "questions": [
        {
            "id": "name",
            "question": "Name",
            "type": "short_answer",
            "required": true,
            "options": [],
            "isDefault": true
        },
        {
            "id": "email",
            "question": "Email",
            "type": "short_answer",
            "required": true,
            "options": [],
            "isDefault": true
        },
        {
            "id": 1739868091386,
            "question": "Paragraph Format Question",
            "type": "paragraph",
            "required": true,
            "options": [],
            "isDefault": false,
            "description": "Paragraph format Description"
        }
    ],
    "start_datetime": "2025-02-17 14:30:00",
    "end_datetime": "2025-02-27 14:30:00"
}
```

#### Response

```json
{
  "success": true,
  "message": "Event created successfully",
  "data": {
    "event_id": "9e3c0216-b83a-48f5-91f6-d1e0c865ffbc",
    "title": "Sample Event",
    "description": "This is a test event created for demonstration purposes.",
    "questions": [...],
    "start_datetime": "2025-02-18T08:00:00.000000Z",
    "end_datetime": "2025-02-27T08:00:00.000000Z",
    "is_published": false
  }
}
```

### List Events

-   **URL:** `/events`
-   **Method:** `GET`
-   **Description:** Get a list of events
-   **Headers:** `Authorization: Bearer {token}`

#### Response

```json
{
    "success": true,
    "message": "Events retrieved successfully",
    "data": [
        {
            "id": "9e3c0216-b83a-48f5-91f6-d1e0c865ffbc",
            "name": "Sample Event",
            "description": "This is a test event.",
            "creator": "Admin User",
            "is_published": true,
            "start_datetime": "2025-02-18T08:00:00.000000Z",
            "end_datetime": "2025-02-27T08:00:00.000000Z"
        }
    ],
    "total_count": 1,
    "filtered_count": 1,
    "current_page": 1,
    "per_page": 10,
    "last_page": 1
}
```

### Toggle Event Publish Status

-   **URL:** `/event/{eventId}/toggle-publish`
-   **Method:** `POST`
-   **Description:** Toggle the publish status of an event
-   **Headers:** `Authorization: Bearer {token}`

#### Response

```json
{
    "success": true,
    "message": "Sample Event published successfully",
    "url": "http://127.0.0.1:8000/event/9e3c0216-b83a-48f5-91f6-d1e0c865ffbc"
}
```

### Submit Event Form

-   **URL:** `/events/{eventId}`
-   **Method:** `POST`
-   **Description:** Submit a form for an event

#### Request Body

```json
{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "answers": [
        {
            "question": "What's your t-shirt size?",
            "answer": "M"
        }
    ]
}
```

#### Response

```json
{
    "success": true,
    "message": "Event form submitted successfully",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

## Participants

### Validate QR and Auth

-   **URL:** `/validate`
-   **Method:** `POST`
-   **Description:** Validate QR code and authenticate participant
-   **Headers:** `Authorization: Bearer {token}`

#### Request Body

```json
{
    "token": "550e8400-e29b-41d4-a716-446655440002"
}
```

#### Response

```json
{
    "success": true,
    "message": "Participant authenticated successfully",
    "data": {
        "participant": {
            "id": "550e8400-e29b-41d4-a716-446655440003",
            "name": "Jane Smith",
            "email": "jane@example.com"
        },
        "event": {
            "id": "550e8400-e29b-41d4-a716-446655440001",
            "title": "Tech Conference 2023"
        },
        "status": "checked_in",
        "checked_in_at": "2023-09-15T09:30:00Z"
    }
}
```

### List Participants

-   **URL:** `/participants/{eventId}`
-   **Method:** `GET`
-   **Description:** Get a list of participants for an event
-   **Headers:** `Authorization: Bearer {token}`

#### Response

```json
{
    "success": true,
    "message": "Participants retrieved successfully",
    "event_title": "Sample Event",
    "data": [
        {
            "id": "9e3c036b-10be-4f72-9ea4-4320401e7ef0",
            "name": "Jane Smith",
            "email": "aaaaa1@example.com",
            "status": "pending",
            "checked_in_at": null
        }
    ],
    "total_count": 1,
    "filtered_count": 1,
    "current_page": 1,
    "per_page": 10,
    "last_page": 1
}
```

### Update Participant Status

-   **URL:** `/participant/update-status`
-   **Method:** `POST`
-   **Description:** Update the status of one or more participants
-   **Headers:** `Authorization: Bearer {token}`
-   **Status Options:** `['approve', 'pending', 'reject', 'block']` (Default: 'pending')

#### Request Body

```json
{
    "event_id": "550e8400-e29b-41d4-a716-446655440001",
    "participant_id": ["550e8400-e29b-41d4-a716-446655440003"],
    "status": "approve"
}
```

#### Response

```json
{
    "success": true,
    "message": "1 participant status updated to approve successfully"
}
```

### Change Event Participants Status

-   **URL:** `/event/{eventId}/change-status/{status}`
-   **Method:** `POST`
-   **Description:** Change the status of all participants for an event
-   **Headers:** `Authorization: Bearer {token}`

#### Response

```json
{
    "success": true,
    "message": "All participants status updated to attended successfully"
}
```

```

```
