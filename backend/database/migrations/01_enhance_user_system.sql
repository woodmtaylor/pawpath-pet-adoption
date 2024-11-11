-- Enhance User table
ALTER TABLE User 
ADD COLUMN role ENUM('adopter', 'shelter_staff', 'admin') NOT NULL DEFAULT 'adopter',
ADD COLUMN email_verified_at TIMESTAMP NULL,
ADD COLUMN account_status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
ADD COLUMN last_login TIMESTAMP NULL,
ADD COLUMN email_verification_token VARCHAR(100) NULL,
ADD COLUMN email_token_expires_at TIMESTAMP NULL;

-- Create UserProfile table
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    INDEX idx_user_profile (user_id)
);

-- Create PasswordReset table
CREATE TABLE PasswordReset (
    reset_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_reset (user_id, used)
);

-- Add indexes for performance
ALTER TABLE User
ADD INDEX idx_email (email),
ADD INDEX idx_status (account_status),
ADD INDEX idx_role (role);
