# PawPath

**A comprehensive pet adoption platform connecting shelters, pets, and potential adopters through intelligent matching algorithms.**

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![React](https://img.shields.io/badge/React-18-blue.svg)](https://reactjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-blue.svg)](https://typescriptlang.org)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://mysql.com)

## Features

### Core Functionality
- **Intelligent Pet Matching** - AI-powered quiz system that matches users with compatible pets
- **Shelter Management** - Complete shelter administration with pet inventory and application tracking
- **Adoption Applications** - Streamlined application process with document management
- **Multi-Role System** - Role-based access control for adopters, shelter staff, and administrators
- **Responsive Design** - Mobile-first approach with modern UI/UX

### Advanced Features
- **Quiz-Based Recommendations** - Comprehensive pet compatibility assessment
- **Favorites System** - Save and track preferred pets
- **Email Notifications** - Automated verification and status updates
- **Content Management** - Blog system with affiliate product integration
- **Secure Authentication** - JWT-based authentication with email verification
- **Analytics Dashboard** - Administrative insights and reporting

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  React Frontend │    │   PHP Backend   │    │  MySQL Database │
│   (TypeScript)  │◄──►│ (Slim Framework)│◄──►│     (8.0+)      │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Backend Stack
- **Framework**: Slim Framework 4
- **Language**: PHP 8.1+
- **Database**: MySQL 8.0+
- **Authentication**: JWT (Firebase JWT)
- **Email**: PHPMailer
- **Architecture**: MVC with Service Layer

### Frontend Stack
- **Framework**: React 18
- **Language**: TypeScript 5
- **Build Tool**: Vite
- **Styling**: Tailwind CSS
- **State Management**: React Context/Hooks

## Quick Start

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Node.js 18 or higher
- Composer
- npm or yarn

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/pawpath-pet-adoption.git
   cd pawpath-pet-adoption
   ```

2. **Backend Setup**
   ```bash
   cd backend
   composer install
   cp .env.example .env
   # Configure your database and email settings in .env
   ```

3. **Database Setup**
   ```bash
   mysql -u root -p -e "CREATE DATABASE pawpath"
   mysql -u root -p pawpath < sql_dump.sql
   ```

4. **Frontend Setup**
   ```bash
   cd ../frontend
   npm install
   ```

5. **Start Development Servers**
   ```bash
   # Terminal 1 - Backend
   cd backend
   php -S localhost:8000 -t public/

   # Terminal 2 - Frontend
   cd frontend
   npm run dev
   ```

6. **Access the Application**
   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8000/api

## Documentation

- **[API Documentation](docs/API.md)** - Complete REST API reference
- **[Database Schema](docs/DATABASE.md)** - Database structure and relationships
- **[Installation Guide](docs/INSTALLATION.md)** - Detailed setup instructions
- **[Architecture Overview](docs/ARCHITECTURE.md)** - System design and patterns

## Testing

### Backend Testing
```bash
cd backend
# Run API test suite
bash api_tests.sh

# Run specific tests
php tests/test_pets.php
php tests/test_quiz_controller.php
```

### Frontend Testing
```bash
cd frontend
npm run test
```

## 🔧 Configuration

### Environment Variables

#### Backend (.env)
```env
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pawpath
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application Settings
APP_ENV=development
APP_DEBUG=true
JWT_SECRET=your-jwt-secret-key

# Email Configuration
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
```

#### Frontend (.env)
```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=PawPath
```

## 📁 Project Structure

```
pawpath-pet-adoption/
├── backend/               # PHP Backend
│   ├── public/            # Web server entry point
│   ├── src/               # Application source code
│   │   ├── api/           # API Controllers
│   │   ├── models/        # Data Models
│   │   ├── services/      # Business Logic
│   │   ├── middleware/    # Authentication & Authorization
│   │   └── config/        # Configuration
│   ├── database/          # Database migrations
│   ├── tests/             # Test suite
│   └── composer.json      # PHP dependencies
├── frontend/              # React Frontend
│   ├── src/               # Source code
│   │   ├── components/    # React Components
│   │   ├── pages/         # Page Components
│   │   ├── services/      # API Services
│   │   ├── types/         # TypeScript Definitions
│   │   └── assets/        # Static Assets
│   ├── public/            # Public assets
│   └── package.json       # Node.js dependencies
├── docs/                  # Documentation
└── README.md              # This file
```
