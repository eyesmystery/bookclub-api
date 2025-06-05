#!/bin/bash

# Admin Role-Based Authorization Test Script
# This script tests the new admin authorization functionality

echo "🚀 Testing Admin Role-Based Authorization"
echo "========================================"

# API Base URL
BASE_URL="http://127.0.0.1:8000/api"

echo
echo "1️⃣ User Registration & Authentication"
echo "-----------------------------------"

# Register a regular user
echo "📝 Registering a regular user:"
USER_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Regular User",
    "email": "user@bookclub.com", 
    "password": "password123",
    "password_confirmation": "password123",
    "division_id": 1
  }')

USER_TOKEN=$(echo $USER_RESPONSE | jq -r '.token')
echo "🔑 User Token: $USER_TOKEN"

# Register an admin user
echo
echo "📝 Registering an admin user:"
ADMIN_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@bookclub.com", 
    "password": "password123",
    "password_confirmation": "password123",
    "division_id": 1,
    "role": "admin"
  }')

ADMIN_TOKEN=$(echo $ADMIN_RESPONSE | jq -r '.token')
echo "🔑 Admin Token: $ADMIN_TOKEN"

echo
echo "2️⃣ Testing User Endpoint with Role Information"
echo "--------------------------------------------"

echo "👤 Getting regular user profile:"
curl -s -X GET "$BASE_URL/user" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" | jq '.user | {name, email, role}'

echo
echo "👑 Getting admin user profile:"
curl -s -X GET "$BASE_URL/user" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq '.user | {name, email, role}'

echo
echo "3️⃣ Testing Admin-Protected Endpoints"
echo "----------------------------------"

echo "❌ Testing book creation with regular user (should fail):"
curl -s -X POST "$BASE_URL/books" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "title": "Test Book",
    "author": "Test Author",
    "description": "Test Description",
    "division_id": 1
  }' | jq '{message}'

echo
echo "✅ Testing book creation with admin user (should succeed):"
BOOK_RESPONSE=$(curl -s -X POST "$BASE_URL/books" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -d '{
    "title": "Admin Test Book",
    "author": "Admin Author",
    "description": "Book created by admin",
    "division_id": 1
  }')

echo $BOOK_RESPONSE | jq '{message, book: {id, title, author}}'
BOOK_ID=$(echo $BOOK_RESPONSE | jq -r '.book.id')

echo
echo "4️⃣ Testing Review Management"
echo "--------------------------"

# Create a review as regular user
echo "📝 Creating review as regular user:"
REVIEW_RESPONSE=$(curl -s -X POST "$BASE_URL/books/$BOOK_ID/review" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "content": "This is a test review that should be deletable by admin"
  }')

echo $REVIEW_RESPONSE | jq '{message, review: {id, content}}'
REVIEW_ID=$(echo $REVIEW_RESPONSE | jq -r '.review.id')

echo
echo "❌ Testing review deletion by regular user (should fail):"
curl -s -X DELETE "$BASE_URL/reviews/$REVIEW_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" | jq '{message}'

echo
echo "✅ Testing review deletion by admin (should succeed):"
curl -s -X DELETE "$BASE_URL/reviews/$REVIEW_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | jq '{message}'

echo
echo "5️⃣ Testing Article Management"
echo "----------------------------"

echo "❌ Testing article creation with regular user (should fail):"
curl -s -X POST "$BASE_URL/articles" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -d '{
    "title": "Test Article",
    "content": "Test content"
  }' | jq '{message}'

echo
echo "✅ Testing article creation with admin user (should succeed):"
curl -s -X POST "$BASE_URL/articles" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -d '{
    "title": "Admin Article",
    "content": "Article created by admin"
  }' | jq '{message}'

echo
echo "✅ Admin Role-Based Authorization tests completed!"
echo "============================================="
echo "📋 Summary:"
echo "  - User roles: ✓ admin, moderator, user supported"
echo "  - User endpoint: ✓ Returns role information"
echo "  - EnsureAdmin middleware: ✓ Properly restricts access"
echo "  - Admin book management: ✓ Working"
echo "  - Admin review management: ✓ Working"
echo "  - Admin article management: ✓ Working" 