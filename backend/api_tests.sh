#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'  # No Color

# Base URL
BASE_URL="http://localhost:8000/api"
TOKEN=""
TIMESTAMP=$(date +%s)

# Test data
QUIZ_DATA='{
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
}'

# Global test counters
TOTAL_TESTS=0
PASSED_TESTS=0
TOTAL_CHECKS=0
PASSED_CHECKS=0

# Helper Functions
verify_match() {
    local expected="$1"
    local actual="$2"
    local description="$3"
    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    if [ "$expected" = "$actual" ]; then
        PASSED_CHECKS=$((PASSED_CHECKS + 1))
        PASSED_TESTS=$((PASSED_TESTS + 1))
        echo -e "${GREEN}✓${NC} $description ($actual)"
    else
        echo -e "${RED}✗${NC} $description - Expected: $expected, Got: $actual"
    fi
}

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
    
    # Debug output to stderr
    echo "→ ${method} ${endpoint}" >&2
    
    # Execute request
    eval $curl_cmd "'${BASE_URL}${endpoint}'"
}

summarize_quiz_response() {
    local response="$1"
    echo "Quiz Results Summary:"
    echo "  Recommendations:"
    
    local species=$(echo $response | jq -r '.data.recommendations.species // "Not specified"')
    echo "    Species: $species"
    
    echo -n "    Traits: "
    local traits=$(echo $response | jq -r '.data.recommendations.traits[]?.trait // empty' 2>/dev/null | tr '\n' ', ' || echo "None")
    echo "${traits%,}"  # Remove trailing comma
    
    local matching_pets=$(echo $response | jq -r '.data.matching_pets | length // 0')
    echo "  Matching Pets: $matching_pets"
    
    local confidence=$(echo $response | jq -r '.data.confidence_score // 0')
    echo "  Confidence Score: ${confidence}%"
}

# Test Functions
test_auth() {
    TOTAL_CHECKS=0
    PASSED_CHECKS=0
    
    echo -e "\n${BLUE}Testing Authentication Endpoints${NC}"
    echo "════════════════════════════════"
    
    echo "Step 1: Testing Registration..."
    local register_data='{
        "username": "testuser_'${TIMESTAMP}'",
        "email": "test_'${TIMESTAMP}'@example.com",
        "password": "password123"
    }'
    
    local register_response=$(make_request "POST" "/auth/register" "$register_data")
    verify_match "true" "$(echo $register_response | jq 'has("token")')" "Registration successful"
    
    echo -e "\nStep 2: Testing Login..."
    local login_data='{
        "email": "test@example.com",
        "password": "password123"
    }'
    
    local login_response=$(make_request "POST" "/auth/login" "$login_data")
    TOKEN=$(echo $login_response | jq -r '.token')
    
    if [ "$TOKEN" != "null" ] && [ "$TOKEN" != "" ]; then
        echo -e "${GREEN}✓${NC} Authentication token obtained"
        export TOKEN
    else
        echo -e "${RED}✗${NC} Failed to obtain authentication token"
        exit 1
    fi

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    [ $PASSED_CHECKS -eq $TOTAL_CHECKS ] && PASSED_TESTS=$((PASSED_TESTS + 1))
}

test_shelters() {
    TOTAL_CHECKS=0
    PASSED_CHECKS=0
    
    echo -e "\n${BLUE}Testing Shelter Endpoints${NC}"
    echo "════════════════════════════════"
    
    echo "Step 1: Creating shelter..."
    local shelter_data='{
        "name": "Test Shelter_'${TIMESTAMP}'",
        "address": "123 Test St",
        "phone": "555-0123",
        "email": "shelter_'${TIMESTAMP}'@test.com",
        "is_no_kill": true
    }'
    
    local shelter_response=$(make_request "POST" "/shelters" "$shelter_data")
    local SHELTER_ID=$(echo $shelter_response | jq -r '.shelter_id')
    verify_match "true" "$(echo $shelter_response | jq 'has("shelter_id")')" "Shelter created"
    
    echo -e "\nStep 2: Verifying shelter details..."
    local get_response=$(make_request "GET" "/shelters/$SHELTER_ID")
    verify_match "Test Shelter_${TIMESTAMP}" "$(echo $get_response | jq -r '.name')" "Shelter name"
    verify_match "123 Test St" "$(echo $get_response | jq -r '.address')" "Shelter address"
    
    echo -e "\nStep 3: Checking shelter list..."
    local list_response=$(make_request "GET" "/shelters")
    local shelter_count=$(echo $list_response | jq '. | length')
    echo "Total shelters: $shelter_count"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    [ $PASSED_CHECKS -eq $TOTAL_CHECKS ] && PASSED_TESTS=$((PASSED_TESTS + 1))
}

test_pets() {
    TOTAL_CHECKS=0
    PASSED_CHECKS=0
    
    echo -e "\n${BLUE}Testing Pet Endpoints${NC}"
    echo "════════════════════════════════"
    
    echo "Step 1: Creating test shelter..."
    local shelter_data='{
        "name": "Test Pet Shelter_'${TIMESTAMP}'",
        "address": "456 Pet St",
        "phone": "555-0124",
        "email": "pet_'${TIMESTAMP}'@shelter.com",
        "is_no_kill": true
    }'
    
    local shelter_response=$(make_request "POST" "/shelters" "$shelter_data")
    local SHELTER_ID=$(echo $shelter_response | jq -r '.shelter_id')
    echo "→ Created shelter ID: $SHELTER_ID"
    
    if [ "$SHELTER_ID" != "null" ] && [ "$SHELTER_ID" != "" ]; then
        echo -e "\nStep 2: Creating pet..."
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
        local PET_ID=$(echo $pet_response | jq -r '.data.pet_id')
        echo "→ Created pet ID: $PET_ID"
        
        if [ "$PET_ID" != "null" ] && [ "$PET_ID" != "" ]; then
            echo -e "\nStep 3: Verifying pet details..."
            local get_response=$(make_request "GET" "/pets/$PET_ID")
            
            echo "Basic Details:"
            verify_match "Max_${TIMESTAMP}" "$(echo $get_response | jq -r '.name')" "Name"
            verify_match "Dog" "$(echo $get_response | jq -r '.species')" "Species"
            verify_match "Golden Retriever" "$(echo $get_response | jq -r '.breed')" "Breed"
            verify_match "2" "$(echo $get_response | jq -r '.age')" "Age"
            verify_match "Male" "$(echo $get_response | jq -r '.gender')" "Gender"
            
            echo -e "\nShelter Relationship:"
            verify_match "$SHELTER_ID" "$(echo $get_response | jq -r '.shelter_id')" "Shelter ID"
            verify_match "Test Pet Shelter_${TIMESTAMP}" "$(echo $get_response | jq -r '.shelter_name')" "Shelter Name"
            
            echo -e "\nStep 4: Verifying pet in list..."
            local list_response=$(make_request "GET" "/pets")
            local pet_in_list=$(echo $list_response | jq --arg pid "$PET_ID" '.[] | select(.pet_id == ($pid|tonumber))')
            
            if [ ! -z "$pet_in_list" ]; then
                echo -e "${GREEN}→ Pet found in list ✓${NC}"
                verify_match "true" "$(echo $pet_in_list | jq 'has("traits")')" "Has traits array"
            else
                echo -e "${RED}→ Pet not found in list ✗${NC}"
            fi
        fi
    fi
    
    echo -e "\nTest Summary:"
    echo "════════════════"
    echo "Total Verifications: $TOTAL_CHECKS"
    echo "Passed: $PASSED_CHECKS"
    echo "Failed: $((TOTAL_CHECKS - PASSED_CHECKS))"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    [ $PASSED_CHECKS -eq $TOTAL_CHECKS ] && PASSED_TESTS=$((PASSED_TESTS + 1))
}

test_quiz() {
    TOTAL_CHECKS=0
    PASSED_CHECKS=0
    
    echo -e "\n${BLUE}Testing Quiz Endpoints${NC}"
    echo "════════════════════════════════"
    
    echo "Step 1: Starting quiz..."
    local start_response=$(make_request "GET" "/quiz/start")
    local sections_count=$(echo $start_response | jq -r '.data.total_sections')
    verify_match "6" "$sections_count" "Quiz sections loaded"
    
    echo -e "\nStep 2: Submitting quiz answers..."
    local quiz_response=$(make_request "POST" "/quiz/submit" "$QUIZ_DATA")
    summarize_quiz_response "$quiz_response"
    
    echo -e "\nStep 3: Checking quiz history..."
    local history_response=$(make_request "GET" "/quiz/history")
    local total_quizzes=$(echo $history_response | jq -r '.data.total_quizzes')
    verify_match "true" "$([ $total_quizzes -gt 0 ] && echo true || echo false)" "Quiz history exists"
    echo "Found $total_quizzes quizzes in history"

    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    [ $PASSED_CHECKS -eq $TOTAL_CHECKS ] && PASSED_TESTS=$((PASSED_TESTS + 1))
}

# Main execution
echo "Starting API Tests"
echo "=========================="

test_auth
test_shelters
test_pets
test_quiz

echo -e "\n${BLUE}Test Suite Summary${NC}"
echo "════════════════════════════════"
echo "Total Test Groups: 4"
echo "Individual Checks Run: $TOTAL_TESTS"
echo "Checks Passed: $PASSED_TESTS"
echo "Checks Failed: $((TOTAL_TESTS - PASSED_TESTS))"
echo -e "Overall Status: $([ $PASSED_TESTS -eq $TOTAL_TESTS ] && echo "${GREEN}All Tests Passed${NC}" || echo "${RED}Some Tests Failed${NC}")"
echo -e "\nAPI Tests Completed"
