# PawPath Database Schema

This document describes the database structure for the PawPath pet adoption platform.

## Overview

PawPath uses MySQL 8.0+ with a normalized relational database design. The schema supports multi-role user management, pet listings with traits, adoption applications, and content management.

## Entity Relationship Diagram

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    User     │    │   Shelter   │    │     Pet     │
│             │    │             │    │             │
├─────────────┤    ├─────────────┤    ├─────────────┤
│ user_id (PK)│    │shelter_id(PK│    │ pet_id (PK) │
│ username    │    │ name        │    │ name        │
│ email       │    │ address     │    │ species     │
│ role        │    │ phone       │    │ breed       │
│ ...         │    │ ...         │    │ shelter_id  │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       │                   └───────────────────┘
       │
       └─────────────────┐
                         │
                ┌─────────────┐
                │Adoption_App │
                │             │
                ├─────────────┤
                │app_id (PK)  │
                │ user_id     │
                │ pet_id      │
                │ status      │
                │ ...         │
                └─────────────┘
```

---

## Core Tables

### User
Stores user account information with role-based access control.

```sql
CREATE TABLE User (
  user_id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  registration_date DATE NOT NULL,
  role ENUM('adopter', 'shelter_staff', 'admin') DEFAULT 'adopter',
  email_verified_at TIMESTAMP NULL,
  email_verification_token VARCHAR(100),
  email_token_expires_at TIMESTAMP NULL,
  account_status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
  last_login TIMESTAMP NULL
);
```

**Key Fields:**
- `role`: Determines user permissions (adopter, shelter_staff, admin)
- `account_status`: Account verification status
- `email_verification_token`: Token for email verification process

**Indexes:**
- `idx_email` on `email`
- `idx_status` on `account_status`
- `idx_role` on `role`

### UserProfile
Extended user information for adopters.

```sql
CREATE TABLE UserProfile (
  profile_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  phone VARCHAR(20),
  address TEXT,
  city VARCHAR(100),
  state VARCHAR(50),
  zip_code VARCHAR(20),
  housing_type ENUM('house', 'apartment', 'condo', 'other'),
  has_yard BOOLEAN,
  other_pets TEXT,
  household_members INT,
  profile_image VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);
```

### Shelter
Animal shelter information and contact details.

```sql
CREATE TABLE Shelter (
  shelter_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  address VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  is_no_kill BOOLEAN NOT NULL,
  manager_id INT,
  
  FOREIGN KEY (manager_id) REFERENCES User(user_id) ON DELETE SET NULL
);
```

### Pet
Individual pet listings with adoption information.

```sql
CREATE TABLE Pet (
  pet_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  species VARCHAR(50) NOT NULL,
  breed VARCHAR(50),
  age INT,
  gender VARCHAR(10),
  description TEXT,
  shelter_id INT NOT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  submitted_by_user_id INT,
  approval_date TIMESTAMP NULL,
  approved_by_user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (shelter_id) REFERENCES Shelter(shelter_id),
  FOREIGN KEY (submitted_by_user_id) REFERENCES User(user_id),
  FOREIGN KEY (approved_by_user_id) REFERENCES User(user_id)
);
```

**Key Fields:**
- `status`: Approval status for user-submitted pets
- `submitted_by_user_id`: User who submitted the pet (if applicable)

**Indexes:**
- `idx_pet_created_at` on `created_at`
- `idx_pet_updated_at` on `updated_at`

---

## Pet Management Tables

### Pet_Trait
Defines available personality and behavioral traits.

```sql
CREATE TABLE Pet_Trait (
  trait_id INT PRIMARY KEY AUTO_INCREMENT,
  trait_name VARCHAR(50) UNIQUE NOT NULL,
  category_id INT,
  value_type ENUM('binary', 'scale', 'enum') DEFAULT 'binary',
  possible_values JSON,
  
  FOREIGN KEY (category_id) REFERENCES Trait_Category(category_id)
);
```

### Trait_Category
Groups related traits together.

```sql
CREATE TABLE Trait_Category (
  category_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) UNIQUE NOT NULL,
  description TEXT
);
```

**Default Categories:**
- Energy Level (High Energy, Calm)
- Social Needs (Good with Kids, Pet Friendly)
- Training (Easily Trained)
- Maintenance (High Maintenance, Easy to Groom)

### Pet_Trait_Relation
Many-to-many relationship between pets and traits.

```sql
CREATE TABLE Pet_Trait_Relation (
  pet_id INT NOT NULL,
  trait_id INT NOT NULL,
  PRIMARY KEY (pet_id, trait_id),
  
  FOREIGN KEY (pet_id) REFERENCES Pet(pet_id),
  FOREIGN KEY (trait_id) REFERENCES Pet_Trait(trait_id)
);
```

### Pet_Image
Stores multiple images for each pet.

```sql
CREATE TABLE Pet_Image (
  image_id INT PRIMARY KEY AUTO_INCREMENT,
  pet_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  is_primary BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (pet_id) REFERENCES Pet(pet_id) ON DELETE CASCADE
);
```

---

## Adoption System Tables

### Adoption_Application
Tracks adoption applications and their status.

```sql
CREATE TABLE Adoption_Application (
  application_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  pet_id INT NOT NULL,
  application_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL,
  status_history JSON,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  reviewed_by INT,
  reason TEXT,
  experience TEXT,
  living_situation TEXT,
  has_other_pets BOOLEAN DEFAULT FALSE,
  other_pets_details TEXT,
  daily_schedule TEXT,
  veterinarian VARCHAR(255),
  
  FOREIGN KEY (user_id) REFERENCES User(user_id),
  FOREIGN KEY (pet_id) REFERENCES Pet(pet_id),
  FOREIGN KEY (reviewed_by) REFERENCES User(user_id)
);
```

**Status Values:**
- `pending`: Application submitted, awaiting review
- `under_review`: Being actively reviewed
- `approved`: Application accepted
- `rejected`: Application declined
- `withdrawn`: User withdrew application

### Application_Document
Supporting documents for adoption applications.

```sql
CREATE TABLE Application_Document (
  document_id INT PRIMARY KEY AUTO_INCREMENT,
  application_id INT NOT NULL,
  document_type ENUM('id', 'proof_of_residence', 'reference', 'other'),
  file_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  verified BOOLEAN DEFAULT FALSE,
  
  FOREIGN KEY (application_id) REFERENCES Adoption_Application(application_id) ON DELETE CASCADE
);
```

---

## Quiz System Tables

### Starting_Quiz
Records when users take the pet matching quiz.

```sql
CREATE TABLE Starting_Quiz (
  quiz_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  quiz_date DATE NOT NULL,
  
  FOREIGN KEY (user_id) REFERENCES User(user_id)
);
```

### Quiz_Result
Stores quiz results and pet recommendations.

```sql
CREATE TABLE Quiz_Result (
  result_id INT PRIMARY KEY AUTO_INCREMENT,
  quiz_id INT NOT NULL,
  recommended_species VARCHAR(50),
  recommended_breed VARCHAR(50),
  confidence_score DECIMAL(5,2),
  trait_preferences JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (quiz_id) REFERENCES Starting_Quiz(quiz_id)
);
```

**Key Fields:**
- `trait_preferences`: JSON array of recommended traits
- `confidence_score`: Algorithm confidence (0-100)

---

## Favorites System

### Pet_Favorite
Tracks user's favorite pets for later viewing.

```sql
CREATE TABLE Pet_Favorite (
  favorite_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  pet_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY unique_favorite (user_id, pet_id),
  FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
  FOREIGN KEY (pet_id) REFERENCES Pet(pet_id) ON DELETE CASCADE
);
```

**Indexes:**
- `idx_user_favorites` on `user_id`
- `idx_pet_favorites` on `pet_id`

---

## Content Management Tables

### Blog_Post
Blog articles and educational content.

```sql
CREATE TABLE Blog_Post (
  post_id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  publication_date DATE NOT NULL,
  author_id INT NOT NULL,
  
  FOREIGN KEY (author_id) REFERENCES User(user_id)
);
```

### Product
Affiliate products mentioned in blog posts.

```sql
CREATE TABLE Product (
  product_id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  affiliate_link VARCHAR(255)
);
```

### Blog_Product_Relation
Links blog posts to relevant products.

```sql
CREATE TABLE Blog_Product_Relation (
  post_id INT NOT NULL,
  product_id INT NOT NULL,
  PRIMARY KEY (post_id, product_id),
  
  FOREIGN KEY (post_id) REFERENCES Blog_Post(post_id),
  FOREIGN KEY (product_id) REFERENCES Product(product_id)
);
```

---

## Administrative Tables

### ShelterStaff
Maps shelter staff users to their shelters.

```sql
CREATE TABLE ShelterStaff (
  shelter_id INT NOT NULL,
  user_id INT NOT NULL,
  position VARCHAR(50) DEFAULT 'staff',
  added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (shelter_id, user_id),
  
  FOREIGN KEY (shelter_id) REFERENCES Shelter(shelter_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);
```

### RoleChangeLog
Audit trail for user role modifications.

```sql
CREATE TABLE RoleChangeLog (
  log_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  old_role VARCHAR(20) NOT NULL,
  new_role VARCHAR(20) NOT NULL,
  changed_by INT NOT NULL,
  change_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reason TEXT,
  
  FOREIGN KEY (user_id) REFERENCES User(user_id),
  FOREIGN KEY (changed_by) REFERENCES User(user_id)
);
```

### PasswordReset
Manages password reset tokens.

```sql
CREATE TABLE PasswordReset (
  reset_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  token VARCHAR(100) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  used BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);
```

**Indexes:**
- `idx_token` on `token`
- `idx_user_reset` on `user_id, used`

---

## Views

### vw_shelter_staff
Convenient view for shelter staff management.

```sql
CREATE VIEW vw_shelter_staff AS
SELECT 
  s.shelter_id,
  s.name AS shelter_name,
  u.user_id,
  u.username,
  u.email,
  ss.position,
  ss.added_date
FROM Shelter s
JOIN ShelterStaff ss ON s.shelter_id = ss.shelter_id
JOIN User u ON ss.user_id = u.user_id
WHERE u.role = 'shelter_staff' 
  AND u.account_status = 'active';
```

---

## Triggers

### before_user_role_update
Prevents unauthorized role escalations.

```sql
DELIMITER ;;
CREATE TRIGGER before_user_role_update 
BEFORE UPDATE ON User 
FOR EACH ROW 
BEGIN
  IF NEW.role = 'admin' AND OLD.role != 'admin' THEN
    SET @current_user_role = (SELECT role FROM User WHERE user_id = @current_user_id);
    IF @current_user_role != 'admin' THEN
      SIGNAL SQLSTATE '45000' 
      SET MESSAGE_TEXT = 'Only administrators can promote users to admin role';
    END IF;
  END IF;
END;;
DELIMITER ;
```

---

## Sample Data Queries

### Get Pet with All Related Information

```sql
SELECT 
  p.*,
  s.name AS shelter_name,
  GROUP_CONCAT(
    DISTINCT CONCAT(tc.name, ':', pt.trait_name)
  ) AS traits,
  pi.image_url AS primary_image
FROM Pet p
LEFT JOIN Shelter s ON p.shelter_id = s.shelter_id
LEFT JOIN Pet_Trait_Relation ptr ON p.pet_id = ptr.pet_id
LEFT JOIN Pet_Trait pt ON ptr.trait_id = pt.trait_id
LEFT JOIN Trait_Category tc ON pt.category_id = tc.category_id
LEFT JOIN Pet_Image pi ON p.pet_id = pi.pet_id AND pi.is_primary = TRUE
WHERE p.pet_id = ?
GROUP BY p.pet_id;
```

### Get User's Application History

```sql
SELECT 
  aa.application_id,
  aa.application_date,
  aa.status,
  p.name AS pet_name,
  p.species,
  s.name AS shelter_name
FROM Adoption_Application aa
JOIN Pet p ON aa.pet_id = p.pet_id
JOIN Shelter s ON p.shelter_id = s.shelter_id
WHERE aa.user_id = ?
ORDER BY aa.application_date DESC;
```

### Get Quiz Results with Matching Pets

```sql
SELECT 
  qr.*,
  COUNT(p.pet_id) AS matching_pets_count
FROM Quiz_Result qr
LEFT JOIN Pet p ON (
  JSON_CONTAINS(qr.trait_preferences, '"High Energy"') 
  AND EXISTS (
    SELECT 1 FROM Pet_Trait_Relation ptr
    JOIN Pet_Trait pt ON ptr.trait_id = pt.trait_id
    WHERE ptr.pet_id = p.pet_id 
      AND pt.trait_name = 'High Energy'
  )
)
WHERE qr.quiz_id = ?
GROUP BY qr.result_id;
```

---

## Database Maintenance

### Regular Maintenance Tasks

1. **Clean up expired tokens:**
```sql
DELETE FROM PasswordReset 
WHERE expires_at < NOW() AND used = FALSE;

DELETE FROM User 
WHERE email_verification_token IS NOT NULL 
  AND email_token_expires_at < NOW()
  AND email_verified_at IS NULL
  AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

2. **Archive old applications:**
```sql
-- Archive applications older than 2 years
CREATE TABLE Adoption_Application_Archive AS
SELECT * FROM Adoption_Application 
WHERE application_date < DATE_SUB(CURDATE(), INTERVAL 2 YEAR);
```

3. **Update statistics:**
```sql
-- Can be used for dashboard analytics
SELECT 
  COUNT(DISTINCT u.user_id) AS total_users,
  COUNT(DISTINCT CASE WHEN u.role = 'adopter' THEN u.user_id END) AS adopters,
  COUNT(DISTINCT p.pet_id) AS total_pets,
  COUNT(DISTINCT CASE WHEN aa.status = 'approved' THEN aa.application_id END) AS successful_adoptions
FROM User u
CROSS JOIN Pet p
CROSS JOIN Adoption_Application aa;
```

---

## Performance Considerations

### Recommended Indexes

```sql
-- For pet search performance
CREATE INDEX idx_pet_species_breed ON Pet(species, breed);
CREATE INDEX idx_pet_age ON Pet(age);
CREATE INDEX idx_pet_status ON Pet(status);

-- For application queries
CREATE INDEX idx_adoption_status_date ON Adoption_Application(status, application_date);
CREATE INDEX idx_adoption_user_pet ON Adoption_Application(user_id, pet_id);

-- For quiz performance
CREATE INDEX idx_quiz_user_date ON Starting_Quiz(user_id, quiz_date);

-- For favorites
CREATE INDEX idx_favorites_user_created ON Pet_Favorite(user_id, created_at);
```

### Query Optimization Tips

1. **Use appropriate indexes** for frequently queried columns
2. **Limit result sets** with proper pagination
3. **Use covering indexes** for read-heavy queries
4. **Consider partitioning** for large tables (applications, quiz results)
5. **Regular ANALYZE TABLE** to keep statistics current

---

## Backup Strategy

### Daily Backups
```bash
mysqldump --single-transaction --routines --triggers pawpath > pawpath_$(date +%Y%m%d).sql
```

### Point-in-time Recovery
Enable binary logging for point-in-time recovery:
```sql
SET GLOBAL log_bin = ON;
SET GLOBAL binlog_format = ROW;
```

---

## Migration Scripts

When updating the schema, always use migration scripts:

```sql
-- Example migration: Add new column
ALTER TABLE Pet ADD COLUMN microchip_id VARCHAR(20) NULL;

-- Add index
CREATE INDEX idx_pet_microchip ON Pet(microchip_id);

-- Update version
INSERT INTO schema_versions (version, applied_at) 
VALUES ('2024.01.15.001', NOW());
```