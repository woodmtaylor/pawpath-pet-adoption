# PawPath API Documentation

### Table of Contents

1. [Base URL](#base-url)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [Status Codes](#status-codes)
5. [Authentication Endpoints](#authentication-endpoints)
   - [Register User](#register-user)
   - [Login](#login)
   - [Get Current User](#get-current-user)
   - [Verify Email](#verify-email)
   - [Resend Verification Email](#resend-verification-email)
6. [Pet Endpoints](#pet-endpoints)
   - [List Pets](#list-pets)
   - [Get Pet Details](#get-pet-details)
   - [Create Pet](#create-pet)
   - [Update Pet](#update-pet)
   - [Delete Pet](#delete-pet)
7. [Shelter Endpoints](#shelter-endpoints)
   - [List Shelters](#list-shelters)
   - [Get Shelter Details](#get-shelter-details)
   - [Create Shelter](#create-shelter)
8. [Quiz Endpoints](#quiz-endpoints)
   - [Start Quiz](#start-quiz)
   - [Submit Quiz](#submit-quiz)
   - [Get Quiz History](#get-quiz-history)
9. [Adoption Application Endpoints](#adoption-application-endpoints)
   - [Submit Application](#submit-application)
   - [Get User Applications](#get-user-applications)
   - [Get Application Details](#get-application-details)
10. [Favorites Endpoints](#favorites-endpoints)
    - [Add Pet to Favorites](#add-pet-to-favorites)
    - [Remove from Favorites](#remove-from-favorites)
    - [Get User Favorites](#get-user-favorites)
11. [User Profile Endpoints](#user-profile-endpoints)
    - [Get Profile](#get-profile)
    - [Update Profile](#update-profile)
    - [Upload Profile Image](#upload-profile-image)
12. [Admin Endpoints](#admin-endpoints)
    - [Get Statistics](#get-statistics)
    - [Manage Users](#manage-users)
13. [Error Handling](#error-handling)
    - [Common Error Responses](#common-error-responses)
14. [Rate Limiting](#rate-limiting)
15. [Testing](#testing)

## Base URL

```
http://localhost:8000/api
```

## Authentication

PawPath uses JWT (JSON Web Tokens) for authentication. Include the token in the Authorization header:

```
Authorization: Bearer <your-jwt-token>
```

## Response Format

All API responses follow this standard format:

```json
{
  "success": true,
  "data": {
    // Response data
  }
}
```

Error responses:

```json
{
  "success": false,
  "error": "Error message"
}
```

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `500` - Internal Server Error

---

## Authentication Endpoints

### Register User

Create a new user account.

```http
POST /auth/register
```

**Request Body:**
```json
{
  "username": "string",
  "email": "string",
  "password": "string"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "user_id": 1,
      "username": "johndoe",
      "email": "john@example.com",
      "role": "adopter",
      "account_status": "pending"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

### Login

Authenticate user and receive JWT token.

```http
POST /auth/login
```

**Request Body:**
```json
{
  "email": "string",
  "password": "string"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "user_id": 1,
      "username": "johndoe",
      "email": "john@example.com",
      "role": "adopter"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

### Get Current User

Get authenticated user information.

```http
GET /auth/me
Authorization: Bearer <token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "user_id": 1,
      "username": "johndoe",
      "email": "john@example.com",
      "role": "adopter",
      "account_status": "active"
    }
  }
}
```

### Verify Email

Verify user email address.

```http
POST /auth/verify-email
```

**Request Body:**
```json
{
  "token": "verification_token_here"
}
```

### Resend Verification Email

Resend email verification.

```http
POST /auth/resend-verification
Authorization: Bearer <token>
```

---

## Pet Endpoints

### List Pets

Get a paginated list of pets with optional filtering.

```http
GET /pets?page=1&perPage=12&species=Dog&sortBy=newest
Authorization: Bearer <token>
```

**Query Parameters:**
- `page` (integer, optional) - Page number (default: 1)
- `perPage` (integer, optional) - Items per page (default: 12)
- `species` (string, optional) - Filter by species
- `breed` (string, optional) - Filter by breed
- `age_min` (integer, optional) - Minimum age
- `age_max` (integer, optional) - Maximum age
- `gender` (string, optional) - Filter by gender
- `sortBy` (string, optional) - Sort order: newest, oldest, name_asc, name_desc

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "pet_id": 1,
        "name": "Buddy",
        "species": "Dog",
        "breed": "Golden Retriever",
        "age": 3,
        "gender": "Male",
        "description": "Friendly and energetic",
        "shelter_name": "Happy Paws Shelter",
        "images": [
          {
            "image_id": 1,
            "url": "/uploads/images/buddy.jpg",
            "is_primary": true
          }
        ],
        "traits": {
          "Energy Level": ["High Energy"],
          "Social": ["Good with Kids"]
        }
      }
    ],
    "total": 25,
    "page": 1,
    "perPage": 12
  }
}
```

### Get Pet Details

Get detailed information about a specific pet.

```http
GET /pets/{id}
Authorization: Bearer <token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "pet_id": 1,
    "name": "Buddy",
    "species": "Dog",
    "breed": "Golden Retriever",
    "age": 3,
    "gender": "Male",
    "description": "Friendly and energetic dog",
    "shelter_id": 1,
    "shelter_name": "Happy Paws Shelter",
    "images": [
      {
        "image_id": 1,
        "url": "/uploads/images/buddy.jpg",
        "is_primary": true
      }
    ],
    "traits": {
      "Energy Level": ["High Energy"],
      "Social": ["Good with Kids"],
      "Training": ["Easily Trained"]
    }
  }
}
```

### Create Pet

Create a new pet listing.

```http
POST /pets
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Buddy",
  "species": "Dog",
  "breed": "Golden Retriever",
  "age": 3,
  "gender": "Male",
  "description": "Friendly and energetic",
  "shelter_id": 1,
  "traits": [1, 2, 3]
}
```

### Update Pet

Update pet information.

```http
PUT /pets/{id}
Authorization: Bearer <token>
```

### Delete Pet

Remove a pet listing.

```http
DELETE /pets/{id}
Authorization: Bearer <token>
```

---

## Shelter Endpoints

### List Shelters

Get all registered shelters.

```http
GET /shelters?search=happy&is_no_kill=true
Authorization: Bearer <token>
```

**Query Parameters:**
- `search` (string, optional) - Search by name or address
- `is_no_kill` (boolean, optional) - Filter no-kill shelters

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "shelter_id": 1,
      "name": "Happy Paws Shelter",
      "address": "123 Pet Street, Anytown, ST 12345",
      "phone": "555-123-4567",
      "email": "contact@happypaws.com",
      "is_no_kill": true,
      "total_pets": 15,
      "active_applications": 8
    }
  ]
}
```

### Get Shelter Details

Get detailed shelter information.

```http
GET /shelters/{id}
Authorization: Bearer <token>
```

### Create Shelter

Create a new shelter (Admin only).

```http
POST /shelters
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "name": "New Shelter",
  "address": "456 Animal Ave",
  "phone": "555-987-6543",
  "email": "info@newshelter.com",
  "is_no_kill": true
}
```

---

## Quiz Endpoints

### Start Quiz

Initialize a new pet matching quiz.

```http
GET /quiz/start
Authorization: Bearer <token>
```

**Response:**
```json
{
  "success": true,
  "data": {
    "questions": {
      "sections": [
        {
          "id": "living_situation",
          "title": "Living Situation",
          "questions": [
            {
              "id": "living_space",
              "text": "What type of home do you live in?",
              "type": "single_choice",
              "options": {
                "apartment_small": "Small Apartment",
                "apartment_large": "Large Apartment",
                "house_small": "Small House",
                "house_large": "Large House with Yard"
              }
            }
          ]
        }
      ]
    },
    "total_sections": 6,
    "estimated_time": "5-10 minutes"
  }
}
```

### Submit Quiz

Submit quiz answers and get pet recommendations.

```http
POST /quiz/submit
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "answers": {
    "living_situation": {
      "living_space": "house_large",
      "outdoor_access": ["private_yard"],
      "rental_restrictions": ["no_restrictions"]
    },
    "lifestyle": {
      "activity_level": "very_active",
      "time_available": "extensive",
      "work_schedule": "regular_hours"
    }
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "quiz_id": 1,
    "recommendations": {
      "species": "dog",
      "breed": null,
      "traits": [
        {
          "trait": "High Energy",
          "value": "binary"
        }
      ]
    },
    "confidence_score": 85,
    "matching_pets": [
      {
        "pet_id": 1,
        "name": "Buddy",
        "breed": "Golden Retriever",
        "traits": {
          "Energy Level": ["High Energy"]
        }
      }
    ]
  }
}
```

### Get Quiz History

Get user's quiz history.

```http
GET /quiz/history
Authorization: Bearer <token>
```

---

## Adoption Application Endpoints

### Submit Application

Submit an adoption application for a pet.

```http
POST /adoptions
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "pet_id": 1,
  "reason": "Looking for a family companion",
  "experience": "Previous dog owner",
  "living_situation": "House with large yard",
  "has_other_pets": false,
  "daily_schedule": "Work from home",
  "veterinarian": "Dr. Smith Animal Clinic"
}
```

### Get User Applications

Get all applications submitted by the current user.

```http
GET /adoptions/user
Authorization: Bearer <token>
```

### Get Application Details

Get detailed application information.

```http
GET /adoptions/{id}
Authorization: Bearer <token>
```

---

## Favorites Endpoints

### Add Pet to Favorites

Add a pet to user's favorites list.

```http
POST /pets/{id}/favorite
Authorization: Bearer <token>
```

### Remove from Favorites

Remove a pet from favorites.

```http
DELETE /pets/{id}/favorite
Authorization: Bearer <token>
```

### Get User Favorites

Get all favorited pets for the current user.

```http
GET /favorites
Authorization: Bearer <token>
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "pet_id": 1,
      "name": "Buddy",
      "species": "Dog",
      "breed": "Golden Retriever",
      "shelter_name": "Happy Paws Shelter",
      "favorited_at": "2024-01-15 10:30:00",
      "images": [],
      "traits": {}
    }
  ]
}
```

---

## User Profile Endpoints

### Get Profile

Get current user's profile information.

```http
GET /profile
Authorization: Bearer <token>
```

### Update Profile

Update user profile information.

```http
PUT /profile
Authorization: Bearer <token>
```

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "555-123-4567",
  "address": "123 Main St",
  "city": "Anytown",
  "state": "ST",
  "zip_code": "12345",
  "housing_type": "house",
  "has_yard": true,
  "household_members": 2
}
```

### Upload Profile Image

Upload a profile picture.

```http
POST /profile/image
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

---

## Admin Endpoints

### Get Statistics

Get platform statistics (Admin only).

```http
GET /admin/stats
Authorization: Bearer <token>
```

### Manage Users

List and manage users (Admin only).

```http
GET /admin/users?search=john&role=adopter&status=active
PUT /admin/users/{id}/role
PUT /admin/users/{id}/status
```

---

## Error Handling

### Common Error Responses

**Validation Error (400):**
```json
{
  "success": false,
  "error": "Missing required field: email"
}
```

**Authentication Error (401):**
```json
{
  "success": false,
  "error": "No token provided"
}
```

**Authorization Error (403):**
```json
{
  "success": false,
  "error": "Insufficient permissions"
}
```

**Not Found Error (404):**
```json
{
  "success": false,
  "error": "Pet not found"
}
```

---

## Rate Limiting

API requests are limited to prevent abuse:
- **General endpoints**: 100 requests per minute
- **Authentication endpoints**: 10 requests per minute
- **File upload endpoints**: 5 requests per minute

---

## Testing

Use the provided test script to verify API functionality:

```bash
cd backend
bash api_tests.sh
```

This will run comprehensive tests against all endpoints and provide detailed output about the API's functionality.
