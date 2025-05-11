# Task Management API

A RESTful API built with Laravel for managing tasks efficiently. This API provides a comprehensive solution for task management with features including user authentication, task CRUD operations, task assignments, filtering, and more.

## Features

### User Authentication

-   Secure authentication using Laravel Sanctum
-   Endpoints for registration, login, and logout
-   Protected routes ensuring only authenticated users can access tasks

### Task Management

-   Complete CRUD operations for tasks
-   Task fields include title, description, due date, status, and priority
-   Ownership tracking (tasks belong to their creator)

### Task Assignment

-   Ability to assign tasks to other users
-   Many-to-many relationship between tasks and assignees
-   API endpoint for task assignment

### Advanced Filtering & Sorting

-   Filter tasks by status, priority, and due date
-   Sort tasks by various fields (e.g., due date, creation time)
-   Pagination support with 10 tasks per page

### Validation & Error Handling

-   Comprehensive form request validation
-   Consistent JSON error responses
-   Proper HTTP status codes for different scenarios

## API Endpoints

For detailed API documentation and testing, you can use our Postman collection:

📚 **[Postman Documentation](https://documenter.getpostman.com/view/40117056/2sB2jAa7Pj)**

### Authentication

-   `POST /api/v1/register` - Register a new user
-   `POST /api/v1/login` - Login a user
-   `POST /api/v1/logout` - Logout the authenticated user (requires authentication)

### Task Management

-   `GET /api/v1/tasks` - List all tasks for the authenticated user
-   `POST /api/v1/tasks` - Create a new task
-   `GET /api/v1/tasks/{id}` - View a specific task
-   `PUT /api/v1/tasks/{id}` - Update a task
-   `DELETE /api/v1/tasks/{id}` - Delete a task
-   `POST /api/v1/tasks/{id}/assign` - Assign a task to another user

## Setup and Installation

### Prerequisites

-   PHP 8.1+
-   Composer
-   MySQL or any compatible database

### Installation Steps

1. Clone the repository

    ```
    git clone https://github.com/iqbalhasandev/laravel-task-management-api-assignment.git
    cd laravel-task-management-api-assignment
    ```

2. Install dependencies

    ```
    composer install
    ```

3. Configure environment variables
    ```
    cp .env.example .env
    php artisan key:generate
    ```
4. Configure your database in the `.env` file

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=task_management
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

5. Run migrations

    ```
    php artisan migrate
    ```

6. (Optional) Seed the database with sample data

    ```
    php artisan db:seed
    ```

7. Start the development server
    ```
    php artisan serve
    ```

## Using the API

### Authentication

#### Register a new user

```
POST /api/v1/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login

```
POST /api/v1/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

Response will include an authentication token:

```
{
    "success": true,
    "message": "Logged in successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "token": "your-auth-token"
    }
}
```

### Making Authenticated Requests

Include the token in the Authorization header:

```
Authorization: Bearer your-auth-token
```

### Working with Tasks

#### Create a task

```
POST /api/v1/tasks
Authorization: Bearer your-auth-token
Content-Type: application/json

{
    "title": "Complete project documentation",
    "description": "Create detailed API documentation for the project",
    "due_date": "2025-05-20",
    "status": "Todo",
    "priority": "High"
}
```

#### List tasks with filtering and sorting

```
GET /api/v1/tasks?status=Todo&priority=High&sort=-due_date
Authorization: Bearer your-auth-token
```

#### Assign a task to another user

```
POST /api/v1/tasks/1/assign
Authorization: Bearer your-auth-token
Content-Type: application/json

{
    "user_id": 2
}
```

## Testing

Run the test suite using PHPUnit:

```
php artisan test
```

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
