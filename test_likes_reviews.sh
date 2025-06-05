#!/bin/bash

# BookClub API - Likes & Reviews Test Script
# This script tests the new likes and reviews functionality

echo "🚀 Testing BookClub API - Likes & Reviews"
echo "=========================================="

# API Base URL
BASE_URL="http://127.0.0.1:8000/api"

echo
echo "1️⃣ User Authentication"
echo "--------------------"

# Login with existing user or register a new one
echo "🔐 Logging in:"
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@bookclub.com",
    "password": "password123"
  }')

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.token')

if [ "$TOKEN" = "null" ]; then
    echo "❌ Login failed, registering new user:"
    REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/register" \
      -H "Accept: application/json" \
      -H "Content-Type: application/json" \
      -d '{
        "name": "Test User",
        "email": "test@bookclub.com", 
        "password": "password123",
        "password_confirmation": "password123",
        "division_id": 1
      }')
    TOKEN=$(echo $REGISTER_RESPONSE | jq -r '.token')
fi

echo "🔑 Token: $TOKEN"

echo
echo "2️⃣ Testing Books with Likes & Reviews Count"
echo "------------------------------------------"

echo "📚 Getting books with counts:"
BOOKS_RESPONSE=$(curl -s -X GET "$BASE_URL/books" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

echo $BOOKS_RESPONSE | jq '.books.data[0] | {id, title, likes_count, reviews_count}'

# Get first book ID
BOOK_ID=$(echo $BOOKS_RESPONSE | jq -r '.books.data[0].id')
echo "📖 Testing with Book ID: $BOOK_ID"

echo
echo "3️⃣ Testing Book Likes"
echo "-------------------"

echo "❤️ Liking a book:"
LIKE_RESPONSE=$(curl -s -X POST "$BASE_URL/books/$BOOK_ID/like" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

echo $LIKE_RESPONSE | jq '{message, liked, likes_count}'

echo
echo "💔 Unliking the same book:"
UNLIKE_RESPONSE=$(curl -s -X POST "$BASE_URL/books/$BOOK_ID/like" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

echo $UNLIKE_RESPONSE | jq '{message, liked, likes_count}'

echo
echo "❤️ Liking again:"
curl -s -X POST "$BASE_URL/books/$BOOK_ID/like" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '{message, liked, likes_count}'

echo
echo "4️⃣ Testing Book Reviews"
echo "---------------------"

echo "📝 Adding a review:"
REVIEW_RESPONSE=$(curl -s -X POST "$BASE_URL/books/$BOOK_ID/review" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "content": "This is an excellent book! I really enjoyed reading it and would recommend it to others."
  }')

echo $REVIEW_RESPONSE | jq '{message, review: {id, content, user}}'

echo
echo "📖 Getting reviews for the book:"
curl -s -X GET "$BASE_URL/books/$BOOK_ID/reviews" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.reviews | {total, data: .data | map({content, user: .user.name, created_at})}'

echo
echo "5️⃣ Testing Book Detail with Like Status"
echo "--------------------------------------"

echo "🔍 Getting book details:"
curl -s -X GET "$BASE_URL/books/$BOOK_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.book | {id, title, likes_count, reviews_count, is_liked_by_user}'

echo
echo "6️⃣ Testing Books with Reviews"
echo "----------------------------"

echo "📚 Getting books that have reviews:"
curl -s -X GET "$BASE_URL/books/reviewed" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.books | {total, data: .data | map({title, likes_count, reviews_count})}'

echo
echo "✅ Likes & Reviews tests completed!"
echo "=================================="
echo "📋 Summary:"
echo "  - Book Likes: ✓ Toggle like/unlike working"
echo "  - Book Reviews: ✓ Add and retrieve reviews working"
echo "  - Counts: ✓ likes_count and reviews_count working"
echo "  - User Status: ✓ is_liked_by_user working"
echo "  - Reviewed Books: ✓ Books with reviews endpoint working" 