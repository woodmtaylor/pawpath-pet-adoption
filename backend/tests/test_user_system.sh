#!/bin/bash

# Store the base URL
BASE_URL="http://localhost:8000/api"
TOKEN=""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

# Test registration with new fields
echo "Testing registration..."
REGISTER_RESPONSE=$(curl -s -X POST "${BASE_URL}/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser_'$(date +%s)'",
    "email": "test_'$(date +%s)'@example.com",
    "password": "Password123!"
  }')

echo $REGISTER_RESPONSE | jq .

# Extract token from registration response
TOKEN=$(echo $REGISTER_RESPONSE | jq -r '.data.token')

# Test profile creation
echo -e "\nTesting profile creation..."
PROFILE_RESPONSE=$(curl -s -X PUT "${BASE_URL}/profile" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ${TOKEN}" \
  -d '{
    "first_name": "Test",
    "last_name": "User",
    "phone": "1234567890",
    "housing_type": "house",
    "has_yard": true,
    "household_members": 2
  }')

echo $PROFILE_RESPONSE | jq .

# Test profile retrieval
echo -e "\nTesting profile retrieval..."
curl -s -X GET "${BASE_URL}/profile" \
  -H "Authorization: Bearer ${TOKEN}" | jq .

# Test email verification resend
echo -e "\nTesting verification email resend..."
curl -s -X POST "${BASE_URL}/auth/resend-verification" \
  -H "Authorization: Bearer ${TOKEN}" | jq .
