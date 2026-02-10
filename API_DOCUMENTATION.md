# Expense Tracking API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {access_token}
```

---

## Authentication Endpoints

### Register
**POST** `/register`

Create a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:** `201 Created`
```json
{
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

---

### Login
**POST** `/login`

Authenticate and receive access token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:** `200 OK`
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "access_token": "1|abc123...",
  "token_type": "Bearer"
}
```

---

### Logout
**POST** `/logout`

Revoke current access token. (Requires authentication)

**Response:** `200 OK`
```json
{
  "message": "Logout successful"
}
```

---

## Dashboard Endpoints

### Get Dashboard Statistics
**GET** `/dashboard?month=12&year=2025`

Get comprehensive dashboard statistics for a specific month.

**Query Parameters:**
- `month` (optional): Month (1-12). Default: current month
- `year` (optional): Year. Default: current year

**Response:** `200 OK`
```json
{
  "data": {
    "total_expenses": 1250.50,
    "total_budget": 2000.00,
    "remaining_budget": 749.50,
    "budget_percentage": 62.5,
    "category_expenses": [...],
    "recent_expenses": [...],
    "budgets": [...],
    "daily_expenses": {...},
    "trends": [...],
    "month": 12,
    "year": 2025
  }
}
```

---

## User Profile Endpoints

### Get Profile
**GET** `/profile`

Get authenticated user profile.

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

---

### Update Profile
**PUT** `/profile`

Update user profile information.

**Request Body:**
```json
{
  "name": "John Smith",
  "email": "john.smith@example.com"
}
```

**Response:** `200 OK`
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Smith",
    "email": "john.smith@example.com"
  }
}
```

---

### Update Password
**PUT** `/password`

Update user password.

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response:** `200 OK`
```json
{
  "message": "Password updated successfully"
}
```

---

## Category Endpoints

### List Categories
**GET** `/categories`

Get all user categories with expense counts.

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "name": "Food & Dining",
      "color": "#EF4444",
      "icon": "utensils",
      "expenses_count": 15,
      "created_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### Create Category
**POST** `/categories`

Create a new category.

**Request Body:**
```json
{
  "name": "Food & Dining",
  "color": "#EF4444",
  "icon": "utensils"
}
```

**Response:** `201 Created`
```json
{
  "message": "Category created successfully",
  "data": {
    "id": 1,
    "name": "Food & Dining",
    "color": "#EF4444",
    "icon": "utensils"
  }
}
```

---

### Get Category
**GET** `/categories/{id}`

Get a specific category.

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "name": "Food & Dining",
    "color": "#EF4444",
    "icon": "utensils",
    "expenses_count": 15
  }
}
```

---

### Update Category
**PUT** `/categories/{id}`

Update a category.

**Request Body:**
```json
{
  "name": "Food & Restaurants",
  "color": "#F97316",
  "icon": "pizza"
}
```

**Response:** `200 OK`
```json
{
  "message": "Category updated successfully",
  "data": {
    "id": 1,
    "name": "Food & Restaurants",
    "color": "#F97316",
    "icon": "pizza"
  }
}
```

---

### Delete Category
**DELETE** `/categories/{id}`

Delete a category (only if no expenses are associated).

**Response:** `200 OK`
```json
{
  "message": "Category deleted successfully"
}
```

---

## Budget Endpoints

### List Budgets
**GET** `/budgets?month=12&year=2025`

Get all budgets for a specific month with spending details.

**Query Parameters:**
- `month` (optional): Month (1-12). Default: current month
- `year` (optional): Year. Default: current year

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "amount": 500.00,
      "month": 12,
      "year": 2025,
      "category": {
        "id": 1,
        "name": "Food & Dining"
      },
      "spent": 325.50,
      "remaining": 174.50,
      "percentage": 65.1,
      "is_over": false
    }
  ],
  "summary": {
    "total_budget": 2000.00,
    "total_spent": 1250.50,
    "total_remaining": 749.50,
    "overall_percentage": 62.5,
    "month": 12,
    "year": 2025
  }
}
```

---

### Create Budget
**POST** `/budgets`

Create a new budget.

**Request Body:**
```json
{
  "amount": 500.00,
  "month": 12,
  "year": 2025,
  "category_id": 1
}
```

**Response:** `201 Created`
```json
{
  "message": "Budget created successfully",
  "data": {
    "id": 1,
    "amount": 500.00,
    "month": 12,
    "year": 2025,
    "category": {
      "id": 1,
      "name": "Food & Dining"
    },
    "spent": 0,
    "remaining": 500.00,
    "percentage": 0,
    "is_over": false
  }
}
```

---

### Get Budget
**GET** `/budgets/{id}`

Get a specific budget with spending details.

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "amount": 500.00,
    "month": 12,
    "year": 2025,
    "category": {...},
    "spent": 325.50,
    "remaining": 174.50,
    "percentage": 65.1,
    "is_over": false
  }
}
```

---

### Update Budget
**PUT** `/budgets/{id}`

Update a budget.

**Request Body:**
```json
{
  "amount": 600.00,
  "month": 12,
  "year": 2025,
  "category_id": 1
}
```

**Response:** `200 OK`
```json
{
  "message": "Budget updated successfully",
  "data": {...}
}
```

---

### Delete Budget
**DELETE** `/budgets/{id}`

Delete a budget.

**Response:** `200 OK`
```json
{
  "message": "Budget deleted successfully"
}
```

---

## Expense Endpoints

### List Expenses
**GET** `/expenses?page=1&per_page=15&search=groceries&category_id=1&start_date=2025-12-01&end_date=2025-12-31&type=one-time&sort_by=date&sort_direction=desc`

Get paginated list of expenses with filtering and sorting.

**Query Parameters:**
- `page` (optional): Page number. Default: 1
- `per_page` (optional): Items per page. Default: 15
- `search` (optional): Search in title and description
- `category_id` (optional): Filter by category
- `start_date` (optional): Filter from date (Y-m-d)
- `end_date` (optional): Filter to date (Y-m-d)
- `type` (optional): Filter by type ('one-time' or 'recurring')
- `sort_by` (optional): Sort field. Default: 'date'
- `sort_direction` (optional): 'asc' or 'desc'. Default: 'desc'

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "amount": 45.50,
      "title": "Grocery shopping",
      "description": "Weekly groceries",
      "date": "2025-12-09",
      "type": "one-time",
      "category": {
        "id": 1,
        "name": "Food & Dining"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "total_amount": 1250.50
  }
}
```

---

### Create Expense
**POST** `/expenses`

Create a new expense.

**Request Body (One-time):**
```json
{
  "amount": 45.50,
  "title": "Grocery shopping",
  "description": "Weekly groceries",
  "date": "2025-12-09",
  "category_id": 1,
  "type": "one-time"
}
```

**Request Body (Recurring):**
```json
{
  "amount": 1200.00,
  "title": "Monthly rent",
  "description": "Apartment rent",
  "date": "2025-12-01",
  "category_id": 2,
  "type": "recurring",
  "recurring_frequency": "monthly",
  "recurring_start_date": "2025-12-01",
  "recurring_end_date": "2026-12-01"
}
```

**Recurring Frequency Options:**
- `daily`
- `weekly`
- `monthly`
- `yearly`

**Response:** `201 Created`
```json
{
  "message": "Expense created successfully",
  "data": {
    "id": 1,
    "amount": 45.50,
    "title": "Grocery shopping",
    "description": "Weekly groceries",
    "date": "2025-12-09",
    "type": "one-time",
    "category": {...}
  }
}
```

---

### Get Expense
**GET** `/expenses/{id}`

Get a specific expense.

**Response:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "amount": 45.50,
    "title": "Grocery shopping",
    "description": "Weekly groceries",
    "date": "2025-12-09",
    "type": "one-time",
    "category": {...}
  }
}
```

---

### Update Expense
**PUT** `/expenses/{id}`

Update an expense.

**Request Body:**
```json
{
  "amount": 50.00,
  "title": "Grocery shopping (updated)",
  "description": "Weekly groceries",
  "date": "2025-12-09",
  "category_id": 1,
  "type": "one-time"
}
```

**Response:** `200 OK`
```json
{
  "message": "Expense updated successfully",
  "data": {...}
}
```

---

### Delete Expense
**DELETE** `/expenses/{id}`

Delete an expense.

**Response:** `200 OK`
```json
{
  "message": "Expense deleted successfully"
}
```

---

### Get Recurring Expenses
**GET** `/expenses/recurring?sort_by=created_at&sort_direction=desc`

Get all recurring expenses.

**Query Parameters:**
- `sort_by` (optional): Sort field. Default: 'created_at'
- `sort_direction` (optional): 'asc' or 'desc'. Default: 'desc'

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": 2,
      "amount": 1200.00,
      "title": "Monthly rent",
      "description": "Apartment rent",
      "date": "2025-12-01",
      "type": "recurring",
      "recurring_frequency": "monthly",
      "recurring_start_date": "2025-12-01",
      "recurring_end_date": "2026-12-01",
      "category": {...}
    }
  ]
}
```

---

## Error Responses

### Validation Error
**Response:** `422 Unprocessable Entity`
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Unauthorized
**Response:** `401 Unauthorized`
```json
{
  "message": "Unauthenticated."
}
```

### Not Found
**Response:** `404 Not Found`
```json
{
  "message": "Resource not found."
}
```

### Forbidden
**Response:** `403 Forbidden`
```json
{
  "message": "This action is unauthorized."
}
```
