## Ticketing Platform App

A simple web application built in pure PHP using a custom MVC architecture.  
The web application is a ticketing platform with full CRUD capabilities for two types of users (event organizers and customer/buyers).  
This project demonstrates how to organize and structure a PHP application without relying on external frameworks or third-party packages, while keeping the codebase clean, modular, and easy to maintain.

---

## Project Structure

```
/app
  /Config        → Constants, base files, database connection, routing  
  /Controllers   → Handles routes and business logic  
  /Helpers       → Utility functions  
  /Models        → Database models (PDO-based)  
  /Views         → PHP views for rendering output  

/db/schema.sql   → Database schema (CREATE TABLE statements)  

/public
  /images
  /js
  /styles
  index.php      → Application entry point  
  .htaccess      → URL rewriting to route everything through index.php  

/scripts
  create_db.php  → Create database script
  seed_users.php → Create two sample users ready to use

/.env.sample     → Example environment file  
/.gitignore

```

---

## Requirements

- PHP **8.0+**
- **MySQL** or **MariaDB**
- PHP extensions: `pdo_mysql`, `mbstring`
- Local server (e.g. **XAMPP**, **WAMP** (or similar), or PHP built-in server)

---

## Installation

1. **Clone the repository**
    ```bash
    git clone https://github.com/merumeru8/tickets_project.git
    cd tickets_project
    ```
2. **Create the database**
   - Run standalone create db script  
      ```bash
      php scripts/create_db.php
      ```
   - *(Optional)* Import sample data: run the standalone seed script **(creates two demo users)**  
      ```bash
      php scripts/seed_users.php
      ```  
      *The above script inserts two sample users so you can log in and explore right away.*

3. **Set up environment variables**
   - Copy `.env.sample` to `.env`
   - Update the values according to your local setup:

4. **Run the application**
   - Using PHP’s built-in server:
       ```bash
       php -S localhost:8000 -t public
       ```
     Then open [http://localhost:8000](http://localhost:8000) in your browser.
   - Or set `/public` as your document root in XAMPP/WAMP.

---

## Database
The full database schema is provided in:
  ```
  db/schema.sql
  ```
It includes all necessary `CREATE TABLE` statements to run the application.  
Preloaded data are imported through scripts/seed_users.php.  
Otherwise any user for both roles (organizer/customer) can be created at http://localhost:8000/register

---

## Demo Credentials 
If you used the seed_users.php script, the following users are already active.
```
Organizer Email: organizer@test.com
Organizer Password: test

Customer Email: customer@test.com
Customer Password: test
```

---

## Technical Notes

- Custom **MVC** pattern:  
  - `Controller` handles requests and coordinates Models and Views.  
  - `Model` interacts with the database using PDO.  
  - `View` renders HTML templates.

- URL routing managed via `.htaccess` and a single entry point (`public/index.php`).

- Environment variables are loaded from `.env` using a simple custom loader located in `public/index.php`.

- Autoloading mechanism loads classes automatically from the `/app` directory (PSR-4–style logic without Composer).

---

## Author & Purpose

Created by **Andrea Tasini**  
This project was built as a example to demonstrate knowledge of PHP, MVC design, and full-stack web development fundamentals to apply business logic as directed by a prompt.
