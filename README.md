# PawPath Backend - Pet Adoption Platform

A comprehensive PHP backend API for a pet adoption platform built with Slim Framework, featuring advanced pet matching algorithms, user management, shelter operations, and blog/affiliate systems.

## 🏗️ Architecture Overview

### Tech Stack
- **Framework**: Slim 4 (PHP 8.1+)
- **Database**: MySQL with PDO
- **Authentication**: JWT (Firebase JWT)
- **Email**: PHPMailer
- **Architecture**: MVC with Service Layer
- **Dependency Injection**: PHP-DI

### Project Structure
```
backend/
├── public/
│   ├── index.php           # Application entry point
│   ├── .htaccess          # URL rewriting rules
│   └── uploads/           # File upload directory
├── src/
│   ├── api/               # Controller layer
│   ├── models/            # Data access layer
│   ├── services/          # Business logic layer
│   ├── middleware/        # Authentication & authorization
│   ├── config/            # Configuration files
│   └── utils/             # Helper utilities
├── database/
│   ├── schema.sql         # Database schema
│   └── migrations/        # Database migrations
├── tests/                 # Test files and utilities
├── composer.json          # Dependencies
└── .env.example          # Environment configuration
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+ with extensions: PDO, MySQL, cURL, JSON
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache/Nginx)

### Installation

1. **Clone and Install Dependencies**
```bash
git clone <repository-url>
cd backend
composer install
```

2. **Environment Setup**
```bash
cp .env.example .env
# Edit .env with your database and email credentials
```

3. **Database Setup**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE pawpath"

# Import schema (you'll need to create this from your existing structure)
mysql -u root -p pawpath < database/schema.sql
```

4. **Start Development Server**
```bash
php -S localhost:8000 -t public/
```

5. **Test API**
```bash
curl http://localhost:8000/api/test
```

## 🗄️ Database Schema

### Core Tables

#### Users & Authentication
- `User` - User accounts with roles (adopter, shelter_staff, admin)
- `UserProfile` - Extended user information
- `PasswordReset` - Password reset tokens

#### Pet Management
- `Pet` - Pet records with species, breed, age, etc.
- `Pet_Image` - Pet photos with primary image designation
- `Pet_Trait` - Available traits (High Energy, Good with Kids, etc.)
- `Pet_Trait_Relation` - Many-to-many pet-trait relationships
- `Trait_Category` - Trait categorization

#### Shelter Operations
- `Shelter` - Shelter information and contact details
- `Adoption_Application` - Adoption applications with status tracking

#### Quiz & Matching System
- `Starting_Quiz` - Quiz sessions
- `Quiz_Result` - Quiz results with recommendations

#### Content Management
- `Blog_Post` - Blog posts for content marketing
- `Product` - Affiliate products
- `Blog_Product_Relation` - Blog-product associations

#### User Features
- `Pet_Favorite` - User's favorited pets

## 🔐 Authentication & Authorization

### JWT Authentication
- Tokens expire after 24 hours
- Middleware validates tokens on protected routes
- User roles: `adopter`, `shelter_staff`, `admin`

### Role-Based Access Control
```php
// Example protected route
$group->get('/admin/stats', [AdminController::class, 'getStats'])
    ->add(new RoleMiddleware('admin'));
```

### Email Verification
- Required for new accounts
- Configurable SMTP or development email testing

## 📡 API Documentation

### Authentication Endpoints

#### POST `/api/auth/register`
Register a new user account.

**Request Body:**
```json
{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "SecurePassword123"
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

#### POST `/api/auth/login`
Authenticate user and receive JWT token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "SecurePassword123"
}
```

### Pet Management Endpoints

#### GET `/api/pets`
List pets with filtering and pagination.

**Query Parameters:**
- `page` - Page number (default: 1)
- `perPage` - Items per page (default: 12)
- `species` - Filter by species
- `breed` - Filter by breed
- `age_min` / `age_max` - Age range
- `gender` - Filter by gender
- `search` - Text search
- `sortBy` - Sort order (newest, oldest, name_asc, name_desc)

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
        "description": "Friendly dog looking for a home",
        "shelter_name": "Happy Paws Shelter",
        "images": [
          {
            "url": "/uploads/images/pet1_primary.jpg",
            "is_primary": true
          }
        ],
        "traits": {
          "energy_level": ["High Energy"],
          "social": ["Good with Kids", "Friendly"]
        }
      }
    ],
    "total": 25,
    "page": 1,
    "perPage": 12
  }
}
```

#### POST `/api/pets`
Create a new pet (shelter staff/admin only).

**Request Body:**
```json
{
  "name": "Luna",
  "species": "Dog",
  "breed": "Labrador Mix",
  "age": 2,
  "gender": "Female",
  "description": "Sweet and energetic dog",
  "shelter_id": 1,
  "traits": [1, 2, 3]
}
```

### Quiz System Endpoints

#### GET `/api/quiz/start`
Initialize a new pet matching quiz.

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

#### POST `/api/quiz/submit`
Submit quiz answers and receive pet recommendations.

**Request Body:**
```json
{
  "answers": {
    "living_situation": {
      "living_space": "house_large",
      "outdoor_access": ["private_yard"]
    },
    "lifestyle": {
      "activity_level": "very_active",
      "time_available": "extensive"
    }
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "quiz_id": 123,
    "recommendations": {
      "species": "dog",
      "traits": [
        {"trait": "High Energy", "value": "binary"},
        {"trait": "Good with Kids", "value": "binary"}
      ]
    },
    "confidence_score": 85,
    "matching_pets": [
      {
        "pet_id": 1,
        "name": "Max",
        "breed": "Golden Retriever",
        "matching_trait_count": 2
      }
    ]
  }
}
```

### Adoption System

#### POST `/api/adoptions`
Submit adoption application.

**Request Body:**
```json
{
  "pet_id": 1,
  "reason": "I want to provide a loving home",
  "experience": "I've had dogs before",
  "living_situation": "Large house with fenced yard",
  "has_other_pets": false,
  "daily_schedule": "Work from home, very flexible",
  "veterinarian": "Dr. Smith at Local Vet Clinic"
}
```

#### GET `/api/adoptions/user`
Get current user's adoption applications.

### Shelter Management

#### GET `/api/shelters`
List all shelters with optional filtering.

#### GET `/api/shelters/{id}`
Get detailed shelter information.

### Admin Endpoints

#### GET `/api/admin/stats`
Get platform statistics (admin only).

#### GET `/api/admin/users`
List users with filtering (admin only).

#### PUT `/api/admin/users/{id}/role`
Change user role (admin only).

## 🧠 Pet Matching Algorithm

### Quiz Processing System

The quiz system uses a weighted scoring algorithm to match users with suitable pets:

#### Question Weights
```php
private const QUESTION_WEIGHTS = [
    'living_space' => 2.0,    // High impact
    'activity_level' => 1.5,  // Important for energy matching
    'time_available' => 1.5,  // Critical for care requirements
    'children' => 1.8,        // Important for safety
    'allergies' => 2.0,       // Critical health factor
];
```

#### Matching Process
1. **Answer Analysis** - Process user responses with weighted scoring
2. **Species Recommendation** - Calculate scores for each species
3. **Trait Preference Generation** - Extract behavioral preferences
4. **Pet Matching** - Find pets matching species and traits
5. **Confidence Calculation** - Based on completion and consistency

#### Example Analysis
```php
// For a user who selects "house_large" + "very_active"
$speciesScores['dog'] += 2 * 2.0; // living_space weight
$speciesScores['dog'] += 2 * 1.5; // activity_level weight
// Results in strong dog recommendation
```

## 🔧 Services Architecture

### Service Layer Pattern
Each major feature has a dedicated service class handling business logic:

- **AuthService** - Registration, login, JWT management
- **PetService** - Pet CRUD, filtering, trait management
- **QuizService** - Quiz processing and pet matching
- **ShelterService** - Shelter operations and statistics
- **EmailService** - Email notifications and verification
- **ImageUploadService** - File upload handling

### Example Service Usage
```php
class PetController {
    private PetService $petService;
    
    public function createPet(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $result = $this->petService->createPet($data);
        return ResponseHelper::sendResponse($response, $result);
    }
}
```

## 🛡️ Security Features

### Input Validation
- JSON input validation with proper error handling
- SQL injection prevention using prepared statements
- Password hashing with PHP's `password_hash()`

### File Upload Security
- File type validation
- Secure filename generation
- Directory traversal prevention

### Rate Limiting Considerations
- JWT expiration (24 hours)
- Email verification token expiration (24 hours)
- Database query optimization to prevent DoS

## 📧 Email System

### Email Types
- **Verification Email** - Account activation
- **Password Reset** - Secure password recovery
- **Welcome Email** - New user onboarding

### Email Configuration
```php
// Development (Mailtrap)
'host' => 'sandbox.smtp.mailtrap.io',
'port' => 2525,

// Production (Gmail/SMTP)
'host' => $_ENV['MAIL_HOST'],
'encryption' => 'tls',
```

## 🧪 Testing

### Test Files Included
- `test_pets.php` - Pet model functionality
- `test_email.php` - Email service testing
- `test_quiz_controller.php` - Quiz system end-to-end
- `api_tests.sh` - Comprehensive API testing script

### Running Tests
```bash
# Test individual components
php tests/test_pets.php

# Test email functionality
php tests/test_email.php

# Full API test suite
bash api_tests.sh
```

### API Test Script Features
- User registration and authentication
- Pet and shelter CRUD operations
- Quiz submission and retrieval
- Blog and product management
- Comprehensive error handling

## 📊 Performance Considerations

### Database Optimization
- Indexed foreign keys and search columns
- Efficient JOIN queries for pet-trait relationships
- Pagination for large datasets

### Caching Opportunities
- Quiz questions (static data)
- Shelter listings
- Pet trait categories

### File Handling
- Proper image resizing for different display sizes
- CDN integration ready for production

## 🚀 Deployment

### Production Checklist
- [ ] Update `.env` with production credentials
- [ ] Configure HTTPS
- [ ] Set up proper MySQL user permissions
- [ ] Configure production email SMTP
- [ ] Set up file upload permissions
- [ ] Enable error logging
- [ ] Configure backup strategy

### Environment Variables
```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pawpath
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=your-secret-key
JWT_SECRET=your-jwt-secret

# Email
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@pawpath.com
MAIL_FROM_NAME=PawPath
```

## 🤝 Contributing

### Code Style
- PSR-4 autoloading
- Proper namespace organization
- Comprehensive error logging
- Consistent response formatting

### Adding New Features
1. Create model class in `src/models/`
2. Add service class in `src/services/`
3. Create controller in `src/api/`
4. Add routes in `public/index.php`
5. Write tests

### Database Changes
1. Create migration file in `database/migrations/`
2. Update model classes
3. Add appropriate indexes
4. Test with existing data

This backend provides a solid foundation for a pet adoption platform with room for scaling and additional features. The architecture supports easy maintenance and feature additions while maintaining security and performance standards.