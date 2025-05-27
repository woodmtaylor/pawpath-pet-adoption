# Contributing to PawPath

Thank you for your interest in contributing to PawPath! This guide will help you get started with contributing to our pet adoption platform.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Contributing Guidelines](#contributing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Documentation](#documentation)
- [Community](#community)

---

## Code of Conduct

### Our Pledge

We are committed to making participation in this project a harassment-free experience for everyone, regardless of age, body size, disability, ethnicity, gender identity and expression, level of experience, nationality, personal appearance, race, religion, or sexual identity and orientation.

### Our Standards

**Positive behavior includes:**
- Using welcoming and inclusive language
- Being respectful of differing viewpoints and experiences
- Gracefully accepting constructive criticism
- Focusing on what is best for the community
- Showing empathy towards other community members

**Unacceptable behavior includes:**
- Harassment, trolling, or discriminatory comments
- Publishing private information without permission
- Other conduct which could reasonably be considered inappropriate

### Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be reported by contacting the project team at conduct@pawpath.com. All complaints will be reviewed and investigated promptly and fairly.

---

## Getting Started

### Ways to Contribute

- 🐛 **Bug Reports** - Help us identify and fix issues
- 💡 **Feature Requests** - Suggest new functionality
- 📝 **Documentation** - Improve guides, API docs, and comments
- 🧪 **Testing** - Write tests and improve test coverage
- 🎨 **Design** - UI/UX improvements and accessibility
- 🌐 **Translation** - Help make PawPath available in more languages
- 📢 **Community** - Help others in discussions and issues

### Prerequisites

Before contributing, make sure you have:
- Read and understood this contributing guide
- Set up the development environment (see [Installation Guide](docs/INSTALLATION.md))
- Familiarized yourself with the codebase structure

---

## Development Setup

### 1. Fork and Clone

```bash
# Fork the repository on GitHub, then clone your fork
git clone https://github.com/YOUR_USERNAME/pawpath-pet-adoption.git
cd pawpath-pet-adoption

# Add the original repository as upstream
git remote add upstream https://github.com/original/pawpath-pet-adoption.git
```

### 2. Set Up Development Environment

```bash
# Backend setup
cd backend
composer install
cp .env.example .env
# Configure your .env file

# Frontend setup
cd ../frontend
npm install
cp .env.example .env
# Configure your .env file

# Database setup
mysql -u root -p -e "CREATE DATABASE pawpath_dev"
mysql -u root -p pawpath_dev < sql_dump.sql
```

### 3. Verify Setup

```bash
# Start backend
cd backend
php -S localhost:8000 -t public/

# Start frontend (in another terminal)
cd frontend
npm run dev

# Run tests
cd backend
bash api_tests.sh
```

---

## Contributing Guidelines

### Issue Reporting

#### Before Creating an Issue

1. **Search existing issues** to avoid duplicates
2. **Check the documentation** for potential solutions
3. **Test with the latest version** if possible

#### Creating a Good Issue

**For Bug Reports:**
```markdown
## Bug Description
Brief description of the issue

## Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. See error

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- OS: [e.g., Ubuntu 20.04]
- PHP Version: [e.g., 8.1.2]
- Browser: [e.g., Chrome 96]
- Node Version: [e.g., 18.0.0]

## Additional Context
Screenshots, logs, or other relevant information
```

**For Feature Requests:**
```markdown
## Feature Summary
Brief description of the proposed feature

## Problem Statement
What problem does this solve?

## Proposed Solution
How should this feature work?

## Alternatives Considered
What other approaches did you consider?

## Additional Context
Mockups, examples, or relevant information
```

### Working on Issues

1. **Comment on the issue** to let others know you're working on it
2. **Ask questions** if anything is unclear
3. **Keep the scope focused** - one issue per pull request
4. **Update progress** if work takes longer than expected

---

## Pull Request Process

### 1. Create a Feature Branch

```bash
# Sync with upstream
git checkout main
git pull upstream main

# Create feature branch
git checkout -b feature/your-feature-name
# or
git checkout -b fix/issue-number-description
```

### 2. Make Your Changes

#### Backend Changes

```bash
cd backend

# Make your changes
# Add/modify PHP files

# Run tests
bash api_tests.sh
php tests/test_specific_feature.php

# Check code style
./vendor/bin/phpcs --standard=PSR12 src/
```

#### Frontend Changes

```bash
cd frontend

# Make your changes
# Add/modify TypeScript/React files

# Run tests
npm test

# Check linting
npm run lint

# Build to ensure no errors
npm run build
```

### 3. Commit Your Changes

Follow our commit message convention:

```bash
# Format: type(scope): description
git commit -m "feat(auth): add email verification system"
git commit -m "fix(pets): resolve image upload validation"
git commit -m "docs(api): update authentication endpoints"
git commit -m "test(quiz): add unit tests for matching algorithm"
```

**Commit Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code formatting (no logic changes)
- `refactor`: Code refactoring
- `test`: Adding/updating tests
- `chore`: Maintenance tasks

### 4. Push and Create Pull Request

```bash
# Push to your fork
git push origin feature/your-feature-name

# Create pull request on GitHub
# Use the pull request template
```

#### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Related Issue
Fixes #(issue number)

## Changes Made
- List of changes
- Another change
- etc.

## Testing
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes
- [ ] I have tested this change in a browser

## Screenshots (if applicable)
Add screenshots to help explain your changes

## Checklist
- [ ] My code follows the project's coding standards
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
```

### 5. Code Review Process

1. **Automated checks** must pass (CI/CD pipeline)
2. **At least one maintainer** must review and approve
3. **Address feedback** promptly and professionally
4. **Update documentation** if your changes affect it

---

## Coding Standards

### PHP (Backend)

#### PSR Standards
Follow PSR-12 coding standards:

```php
<?php

namespace PawPath\api;

use PawPath\services\PetService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PetController
{
    private PetService $petService;

    public function __construct()
    {
        $this->petService = new PetService();
    }

    public function listPets(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $result = $this->petService->listPets($queryParams);
            
            return $this->sendResponse($response, $result);
        } catch (\Exception $e) {
            error_log('Error in listPets: ' . $e->getMessage());
            return $this->sendError($response, $e->getMessage());
        }
    }
}
```

#### Best Practices

- **Use type hints** for all parameters and return types
- **Handle exceptions** appropriately
- **Log errors** with context
- **Validate input** thoroughly
- **Use dependency injection** where appropriate
- **Write descriptive variable names**

#### Database Queries

```php
// ✅ Good - Use prepared statements
$stmt = $this->db->prepare("
    SELECT p.*, s.name as shelter_name 
    FROM Pet p 
    LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id 
    WHERE p.species = ? AND p.age >= ?
");
$stmt->execute([$species, $minAge]);

// ❌ Bad - SQL injection risk
$query = "SELECT * FROM Pet WHERE species = '$species'";
```

### TypeScript/React (Frontend)

#### Code Style

```typescript
// interfaces/types
interface Pet {
  id: number;
  name: string;
  species: string;
  breed?: string;
  age?: number;
  images: PetImage[];
}

// Component example
interface PetCardProps {
  pet: Pet;
  onFavorite: (petId: number) => void;
  className?: string;
}

export const PetCard: React.FC<PetCardProps> = ({ 
  pet, 
  onFavorite, 
  className = '' 
}) => {
  const [isFavorited, setIsFavorited] = useState(false);

  const handleFavoriteClick = useCallback(() => {
    onFavorite(pet.id);
    setIsFavorited(!isFavorited);
  }, [pet.id, isFavorited, onFavorite]);

  return (
    <div className={`pet-card ${className}`}>
      <h3>{pet.name}</h3>
      <p>{pet.species} • {pet.breed}</p>
      <button onClick={handleFavoriteClick}>
        {isFavorited ? '❤️' : '🤍'}
      </button>
    </div>
  );
};
```

#### Best Practices

- **Use TypeScript strictly** - avoid `any` types
- **Implement proper error boundaries**
- **Use React hooks appropriately**
- **Optimize performance** with useMemo/useCallback when needed
- **Follow accessibility guidelines**
- **Use semantic HTML elements**

### CSS/Styling

Using Tailwind CSS:

```tsx
// ✅ Good - Responsive, accessible, semantic
<button 
  className="
    bg-blue-600 hover:bg-blue-700 
    text-white font-medium py-2 px-4 rounded-md
    focus:outline-none focus:ring-2 focus:ring-blue-500
    disabled:opacity-50 disabled:cursor-not-allowed
    transition-colors duration-200
  "
  disabled={loading}
  aria-label="Add pet to favorites"
>
  {loading ? 'Adding...' : 'Add to Favorites'}
</button>
```

---

## Testing

### Backend Testing

#### Unit Tests

```php
<?php
// tests/services/PetServiceTest.php

use PHPUnit\Framework\TestCase;
use PawPath\services\PetService;

class PetServiceTest extends TestCase
{
    private PetService $petService;

    protected function setUp(): void
    {
        $this->petService = new PetService();
    }

    public function testCreatePetWithValidData(): void
    {
        $petData = [
            'name' => 'Test Pet',
            'species' => 'Dog',
            'shelter_id' => 1
        ];

        $result = $this->petService->createPet($petData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('pet_id', $result);
        $this->assertEquals('Test Pet', $result['name']);
    }

    public function testCreatePetWithInvalidData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $this->petService->createPet([]);
    }
}
```

#### API Tests

```bash
# Run all API tests
cd backend
bash api_tests.sh

# Run specific test
php tests/test_pets.php
```

### Frontend Testing

#### Component Tests

```typescript
// __tests__/components/PetCard.test.tsx

import { render, screen, fireEvent } from '@testing-library/react';
import { PetCard } from '../PetCard';

const mockPet = {
  id: 1,
  name: 'Buddy',
  species: 'Dog',
  breed: 'Golden Retriever',
  images: []
};

describe('PetCard', () => {
  it('renders pet information correctly', () => {
    const onFavorite = jest.fn();
    
    render(<PetCard pet={mockPet} onFavorite={onFavorite} />);
    
    expect(screen.getByText('Buddy')).toBeInTheDocument();
    expect(screen.getByText(/Dog.*Golden Retriever/)).toBeInTheDocument();
  });

  it('calls onFavorite when favorite button is clicked', () => {
    const onFavorite = jest.fn();
    
    render(<PetCard pet={mockPet} onFavorite={onFavorite} />);
    
    const favoriteButton = screen.getByLabelText(/add.*favorites/i);
    fireEvent.click(favoriteButton);
    
    expect(onFavorite).toHaveBeenCalledWith(1);
  });
});
```

### Test Coverage

Maintain good test coverage:

```bash
# Backend coverage
cd backend
vendor/bin/phpunit --coverage-html coverage/

# Frontend coverage
cd frontend
npm test -- --coverage
```

**Coverage Goals:**
- **Critical paths**: 90%+ coverage
- **Business logic**: 80%+ coverage
- **Overall project**: 70%+ coverage

---

## Documentation

### Code Documentation

#### PHP Documentation

```php
/**
 * Create a new pet listing with validation
 *
 * @param array $data Pet data including name, species, shelter_id
 * @return array Created pet with pet_id and formatted data
 * @throws \InvalidArgumentException When required fields are missing
 * @throws \RuntimeException When database operation fails
 */
public function createPet(array $data): array
{
    // Implementation
}
```

#### TypeScript Documentation

```typescript
/**
 * Custom hook for managing pet favorites
 * 
 * @param userId - The ID of the current user
 * @returns Object containing favorites state and management functions
 */
export const usePetFavorites = (userId: number) => {
  // Implementation
};
```

### API Documentation

Update API documentation when making changes:

```bash
# Update docs/API.md with new endpoints
# Include request/response examples
# Document any breaking changes
```

### README Updates

Keep documentation current:
- Update feature lists
- Modify installation instructions if needed
- Add new configuration options
- Update troubleshooting section

---

## Community

### Communication Channels

- **GitHub Discussions**: General questions and community chat
- **GitHub Issues**: Bug reports and feature requests
- **Pull Requests**: Code review and technical discussions
- **Email**: conduct@pawpath.com for Code of Conduct issues

### Getting Help

1. **Search existing issues** and discussions first
2. **Ask in GitHub Discussions** for general questions
3. **Create an issue** for bugs or feature requests
4. **Be patient and respectful** - maintainers are volunteers

### Mentoring

New contributors are welcome! Look for issues labeled:
- `good first issue` - Perfect for newcomers
- `help wanted` - Community input needed
- `documentation` - Non-code contributions

### Recognition

Contributors will be recognized in:
- CONTRIBUTORS.md file
- Release notes for significant contributions
- Annual contributor appreciation posts

---

## Release Process

### Semantic Versioning

We follow [SemVer](https://semver.org/):
- **MAJOR** (1.0.0): Breaking changes
- **MINOR** (0.1.0): New features, backwards compatible
- **PATCH** (0.0.1): Bug fixes, backwards compatible

### Release Schedule

- **Patch releases**: As needed for critical fixes
- **Minor releases**: Monthly for new features
- **Major releases**: Annually or for significant changes

### Changelog

All notable changes are documented in CHANGELOG.md following [Keep a Changelog](https://keepachangelog.com/) format.

---

## License

By contributing to PawPath, you agree that your contributions will be licensed under the same [MIT License](LICENSE) that covers the project.

---

## Questions?

If you have questions about contributing:
- 💬 Start a [GitHub Discussion](https://github.com/yourusername/pawpath-pet-adoption/discussions)
- 📧 Email the maintainers: contributors@pawpath.com
- 📖 Check our [documentation](docs/)

Thank you for helping make PawPath better for pets and their future families! 🐾

---

*Last updated: December 2024*