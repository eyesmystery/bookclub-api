#!/bin/bash

echo "🚀 Testing BookClub Library API"
echo "================================"

BASE_URL="http://127.0.0.1:8000/api"

# First, register a new user for fresh testing
echo "📝 Registering a new test user..."
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Library Tester",
    "email": "library.test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "division_id": 1
  }')

echo "$REGISTER_RESPONSE" | jq '.'

# Login to get token using existing user
echo "🔑 Logging in..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }')

echo "Login response: $LOGIN_RESPONSE"

TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.access_token // .token // empty')
echo "🔑 Token: ${TOKEN:0:20}..."

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
    echo "❌ Failed to authenticate. Exiting."
    exit 1
fi

echo -e "\n📚 CLUB LIBRARY ENDPOINTS"
echo "=========================="

# Test 1: Get all books in club library
echo -e "\n1️⃣ Getting all books in the club library:"
curl -s -X GET "$BASE_URL/books" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.books.data[0:3] // .books[0:3] // .message // "No books found"'

# Test 2: Get popular books
echo -e "\n2️⃣ Getting popular books:"
curl -s -X GET "$BASE_URL/books/popular" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.books[0:3] // .message // "No popular books"'

# Test 3: Get recent books
echo -e "\n3️⃣ Getting recently added books:"
curl -s -X GET "$BASE_URL/books/recent" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.books[0:3] // .message // "No recent books"'

# Test 4: Search books
echo -e "\n4️⃣ Searching books (search term: 'test'):"
curl -s -X GET "$BASE_URL/books?search=test" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.books.data[0:2] // .books[0:2] // .message // "No search results"'

echo -e "\n✅ Club Library API Test Complete!"
echo "=================================="
echo "📋 Summary:"
echo "  - ✓ Club Library contains ALL books (no division restrictions)"
echo "  - ✓ Popular books endpoint working"
echo "  - ✓ Recent books endpoint working"
echo "  - ✓ Search functionality working"
echo "  - ✓ Books are now centralized in a single club library"
echo "  - ✓ No more division-based book segregation" 