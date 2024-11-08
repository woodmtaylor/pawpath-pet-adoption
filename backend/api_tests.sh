#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

# Base URL
BASE_URL="http://localhost:8000/api"
TOKEN=""

# Generate unique timestamp for test data
TIMESTAMP=$(date +%s)

echo -e "${BLUE}Starting API Tests${NC}"
echo "=========================="

make_request() {
    local method=$1
    local endpoint=$2
    local data=$3
    
    local curl_cmd="curl -s -X $method"
    curl_cmd="$curl_cmd -H 'Content-Type: application/json'"
    
    if [ ! -z "$TOKEN" ]; then
        curl_cmd="$curl_cmd -H 'Authorization: Bearer ${TOKEN}'"
    fi
    
    if [ ! -z "$data" ]; then
        curl_cmd="$curl_cmd -d '${data}'"
    fi
    
    curl_cmd="$curl_cmd '${BASE_URL}${endpoint}'"
    
    local response=$(eval $curl_cmd)
    if [ -z "$response" ]; then
        echo "{'error': 'Empty response'}"
    else
        echo "$response"
    fi
}

test_auth() {
    echo -e "\n${BLUE}Testing Authentication Endpoints${NC}"
    echo "--------------------------------"
    
    local register_data='{
        "username": "testuser_'${TIMESTAMP}'",
        "email": "test_'${TIMESTAMP}'@example.com",
        "password": "password123"
    }'
    
    echo "Testing registration..."
    local register_response=$(make_request "POST" "/auth/register" "$register_data")
    echo "Registration Response: $register_response"
    
    local login_data='{
        "email": "test@example.com",
        "password": "password123"
    }'
    
    echo -e "\nTesting login..."
    local login_response=$(make_request "POST" "/auth/login" "$login_data")
    echo "Login Response: $login_response"
    
    TOKEN=$(echo $login_response | jq -r '.token')
    if [ "$TOKEN" != "null" ] && [ "$TOKEN" != "" ]; then
        echo -e "${GREEN}Successfully obtained auth token${NC}"
    else
        echo -e "${RED}Failed to obtain auth token${NC}"
    fi
}

test_shelters() {
    echo -e "\n${BLUE}Testing Shelter Endpoints${NC}"
    echo "--------------------------------"
    
    local shelter_data='{
        "name": "Test Shelter_'${TIMESTAMP}'",
        "address": "123 Test St",
        "phone": "555-0123",
        "email": "shelter_'${TIMESTAMP}'@test.com",
        "is_no_kill": true
    }'
    
    echo "Creating shelter..."
    local shelter_response=$(make_request "POST" "/shelters" "$shelter_data")
    echo "Create Shelter Response: $shelter_response"
    
    local SHELTER_ID=$(echo $shelter_response | jq -r '.shelter_id')
    
    echo -e "\nGetting shelter list..."
    local list_response=$(make_request "GET" "/shelters")
    echo "List Shelters Response: $list_response"
    
    if [ "$SHELTER_ID" != "null" ] && [ "$SHELTER_ID" != "" ]; then
        echo -e "\nGetting specific shelter..."
        local get_response=$(make_request "GET" "/shelters/$SHELTER_ID")
        echo "Get Shelter Response: $get_response"
        return $SHELTER_ID
    else
        echo -e "${RED}Failed to get shelter ID${NC}"
        return 0
    fi
}

test_pets() {
    echo -e "\n${BLUE}Testing Pet Endpoints${NC}"
    echo "--------------------------------"
    
    local shelter_data='{
        "name": "Test Pet Shelter_'${TIMESTAMP}'",
        "address": "456 Pet St",
        "phone": "555-0124",
        "email": "pet_'${TIMESTAMP}'@shelter.com",
        "is_no_kill": true
    }'
    
    echo "Creating test shelter for pets..."
    local shelter_response=$(make_request "POST" "/shelters" "$shelter_data")
    local SHELTER_ID=$(echo $shelter_response | jq -r '.shelter_id')
    
    if [ "$SHELTER_ID" != "null" ] && [ "$SHELTER_ID" != "" ]; then
        echo "Creating pet..."
        local pet_data='{
            "name": "Max_'${TIMESTAMP}'",
            "species": "Dog",
            "breed": "Golden Retriever",
            "age": 2,
            "gender": "Male",
            "description": "A friendly dog looking for a home",
            "shelter_id": '$SHELTER_ID'
        }'
        
        local pet_response=$(make_request "POST" "/pets" "$pet_data")
        echo "Create Pet Response: $pet_response"
        
        local PET_ID=$(echo $pet_response | jq -r '.pet_id')
        
        echo -e "\nGetting pet list..."
        local list_response=$(make_request "GET" "/pets")
        echo "List Pets Response: $list_response"
        
        if [ "$PET_ID" != "null" ] && [ "$PET_ID" != "" ]; then
            echo -e "\nGetting specific pet..."
            local get_response=$(make_request "GET" "/pets/$PET_ID")
            echo "Get Pet Response: $get_response"
        fi
    else
        echo -e "${RED}Failed to create shelter for pet test${NC}"
    fi
}

test_quiz() {
    echo -e "\n${BLUE}Testing Quiz Endpoints${NC}"
    echo "--------------------------------"
    
    echo "Starting quiz..."
    local start_response=$(make_request "GET" "/quiz/start")
    echo "Start Quiz Response: $start_response"
    
    echo -e "\nSubmitting quiz..."
    local quiz_data='{
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
    }'
    local submit_response=$(make_request "POST" "/quiz/submit" "$quiz_data")
    echo "Submit Quiz Response: $submit_response"
    
    echo -e "\nGetting quiz history..."
    local history_response=$(make_request "GET" "/quiz/history")
    echo "Quiz History Response: $history_response"
}

main() {
    test_auth
    test_shelters
    test_pets
    test_quiz
    echo -e "\n${GREEN}API Tests Completed${NC}"
}

main
