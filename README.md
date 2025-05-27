# PawPath Backend

A RESTful API for a pet adoption platform built with PHP and Slim Framework. Features intelligent pet matching, shelter management, and user authentication.

## Features

- **Pet Matching Algorithm** - Quiz-based system to match users with compatible pets
- **Multi-role Authentication** - Support for adopters, shelter staff, and administrators
- **Shelter Management** - Complete CRUD operations for shelters and their pets
- **Adoption Applications** - Application submission and tracking system
- **Content Management** - Blog posts and affiliate product integration
- **Email Notifications** - Automated verification and notification emails

## Tech Stack

- **Backend**: PHP 8.1+, Slim Framework 4
- **Database**: MySQL 5.7+
- **Authentication**: JWT (Firebase JWT)
- **Email**: PHPMailer
- **Dependencies**: Composer

## Installation

### Prerequisites

- PHP 8.1+ with PDO, MySQL, cURL extensions
- MySQL 5.7+ or MariaDB 10.3+
- Composer

### Setup

1. **Install dependencies**
   ```bash
   composer install
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your database and email credentials
   ```

3. **Set up database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE pawpath"
   # Import your database schema
   ```

4. **Start development server**
   ```bash
   php -S localhost:8000 -t public/
   ```

5. **Test the API**
   ```bash
   curl http://localhost:8000/api/test
   ```

## API Documentation

### Authentication

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
  "username": "johndoe",
  "email": "john@example.com",
  "password": "SecurePassword123"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePassword123"
}
```

### Pets

#### List Pets
```http
GET /api/pets?page=1&perPage=12&species=Dog&sortBy=newest
Authorization: Bearer {token}
```

#### Create Pet
```http
POST /api/pets
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Buddy",
  "species": "Dog",
  "breed": "Golden Retriever",
  "age": 3,
  "shelter_id": 1
}
```

### Quiz System

#### Start Quiz
```http
GET /api/quiz/start
Authorization: Bearer {token}
```

#### Submit Quiz
```http
POST /api/quiz/submit
Authorization: Bearer {token}
Content-Type: application/json

{
  "answers": {
    "living_situation": {
      "living_space": "house_large"
    },
    "lifestyle": {
      "activity_level": "very_active"
    }
  }
}
```

## Project Structure

```
backend/
├── public/
│   ├── index.php          # Application entry point
│   └── .htaccess         # URL rewriting
├── src/
│   ├── api/              # Controllers
│   ├── models/           # Data models
│   ├── services/         # Business logic
│   ├── middleware/       # Authentication & authorization
│   └── config/           # Configuration
├── database/
│   └── migrations/       # Database migrations
├── tests/                # Test files
└── composer.json         # Dependencies
```

## Environment Configuration

Create a `.env` file with the following variables:

```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pawpath
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application
APP_ENV=development
APP_DEBUG=true
JWT_SECRET=your-jwt-secret-key

# Email
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
```

## Testing

Run the API test suite:

```bash
bash api_tests.sh
```

Test individual components:

```bash
php tests/test_pets.php
php tests/test_email.php
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
