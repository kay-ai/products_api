```markdown
# Symfony API Application Setup Guide
```

This guide will walk you through setting up and running a Symfony API application, including database seeding for testing purposes.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- PHP (>= 7.4 recommended)
- Composer (Dependency Manager for PHP)
- MySQL or MariaDB (or another supported database)
- Symfony CLI (optional but recommended for Symfony projects)

## Installation Steps

1. **Clone the repository:**

2. **Install dependencies:**

   ```bash
   composer install
   ```

3. **Setup Environment Variables:**

   Copy the `.env` file and configure your database and JWT keys:

   ```bash
   cp .env.example .env
   ```

   Update `.env` with your database credentials.

4. **Generate JWT Token:**

   ```bash
   php bin/console lexik:jwt:generate-keypair
   ```
   
5. **Create the Database:**

   ```bash
   php bin/console doctrine:database:create
   ```

6. **Run Migrations:**

   ```bash
   php bin/console doctrine:migrations:migrate
   ```

   This will create necessary database tables based on your entities.

7. **Seed the Database (Optional):**

   If you have seed data to populate the database for testing, create a seeder class or use fixtures:

   ```bash
   php bin/console doctrine:fixtures:load
   ```

   Modify and add fixtures as needed in `src/DataFixtures`.

## Running the Application

8. **Start the Symfony Server:**

   ```bash
   symfony serve
   ```

   This will start a local server. By default, the application will be accessible at `http://localhost:8000`.

9. **Accessing API Endpoints:**

   Use tools like Postman or curl to interact with the API endpoints.

   ```bash
     - `GET /api/products` - List all products.
    - `POST /api/products` - Create a new product.
    - `GET /api/products/{id}` - Get details of a single product.
    - `PUT /api/products/{id}` - Update an existing product.
    - `DELETE /api/products/{id}` - Delete a product.
   ```

## Running Tests

10. **Run PHPUnit Tests:**

   ```bash
   php bin/phpunit
   ```
