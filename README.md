
# PHP REST API Server

This is a simple, lightweight REST API server built with PHP. It provides a basic structure for creating a RESTful API with user authentication and management.

## Features

*   **RESTful API Structure**: Organized routing and controllers.
*   **JWT Authentication**: Secure your private endpoints using JSON Web Tokens.
*   **API Key Authentication**: Protect public endpoints with API keys.
*   **User Management**: Basic CRUD operations for users (Create, Read, Update, Delete).
*   **Automatic Table Creation**: The `users` table is created automatically if it doesn't exist.
*   **CORS Middleware**: Handles Cross-Origin Resource Sharing headers.
*   **Modular Routing**: Define routes and their authentication types in separate files for better organization.

## Requirements

*   PHP 7.4 or higher
*   MySQL or MariaDB
*   A web server like Apache or Nginx
*   [Composer](https://getcomposer.org/) (optional, but recommended for managing dependencies if you add any)

## Installation and Setup

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd <project-directory>
    ```

2.  **Configure the Database:**
    *   Open `config/Database.php`.
    *   Update the database credentials (`$host`, `$db_name`, `$username`, `$password`) to match your local environment.
    ```php
    class Database
    {
        private $host = 'localhost';
        private $db_name = 'lms'; // Your database name
        private $username = 'root';   // Your database username
        private $password = '';       // Your database password
        // ...
    }
    ```
    *   The application will automatically create the `users` table when it first connects to the database.

3.  **Configure API Keys:**
    *   Open `middleware/ApiKeyAuthMiddleware.php`.
    *   Replace `'your-secure-api-key'` with a strong, randomly generated API key.
    ```php
    private static $apiKey = 'your-secure-api-key'; // <-- CHANGE THIS
    ```

4.  **Configure JWT Secret:**
    *   Open `utils/JwtHelper.php`.
    *   Change the `$secretKey` to a long, random, and secret string. This is crucial for security.
    ```php
    private static $secretKey = 'your-super-secret-key'; // <-- CHANGE THIS
    ```

5.  **Run the server:**
    *   You can use the built-in PHP server for development:
    ```bash
    php -S localhost:8000
    ```
    *   Alternatively, configure a virtual host on your Apache or Nginx server to point to the project's root directory.

## API Endpoints

All endpoints are prefixed with `/`.

### Authentication

*   `POST /login/`
    *   **Description**: Authenticates a user and returns a JWT token.
    *   **Auth**: None
    *   **Body**:
        ```json
        {
            "email": "user@example.com",
            "password": "userpassword"
        }
        ```

### Users

*   `POST /users/`
    *   **Description**: Creates a new user (Signup).
    *   **Auth**: None
    *   **Body**:
        ```json
        {
            "f_name": "John",
            "l_name": "Doe",
            "email": "john.doe@example.com",
            "password": "strongpassword",
            "nic": "123456789V"
        }
        ```

*   `GET /users/`
    *   **Description**: Get a list of all active users.
    *   **Auth**: Private (JWT Token required in `Authorization: Bearer <token>` header).

*   `GET /users/{id}/`
    *   **Description**: Get a single user by their ID.
    *   **Auth**: Private (JWT Token required).

*   `PUT /users/{id}/`
    *   **Description**: Update a user's details.
    *   **Auth**: Private (JWT Token required).
    *   **Body**: (Include only fields to be updated)
        ```json
        {
            "f_name": "Johnathan"
        }
        ```

*   `DELETE /users/{id}/`
    *   **Description**: Soft deletes a user (sets `is_active` to `0`).
    *   **Auth**: Private (JWT Token required).

### Public

*   `GET /ping/`
    *   **Description**: A public endpoint to check if the API is running.
    *   **Auth**: Public (API Key required in `X-API-KEY` header).

