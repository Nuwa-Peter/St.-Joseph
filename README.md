# School Management System (Simple PHP Version)

This is a simplified version of the St. Joseph's Vocational SS Nyamityobora School Management System, built with plain PHP, MySQL, and Bootstrap.

## Prerequisites

-   **XAMPP:** You will need to have XAMPP installed on your Windows 11 machine. You can download it from the [official Apache Friends website](https://www.apachefriends.org/index.html).

## Setup Instructions

### 1. Start XAMPP

-   Open the XAMPP Control Panel.
-   Start the **Apache** and **MySQL** services.

### 2. Create the Database

-   Open your web browser and navigate to `http://localhost/phpmyadmin/`.
-   Click on the **Databases** tab.
-   In the "Create database" field, enter `school_management_simple_db` and click **Create**.

### 3. Import the Database Schema

-   Select the `school_management_simple_db` database from the left-hand menu.
-   Click on the **Import** tab.
-   Click on the "Choose File" button and select the `database_schema.sql` file from the root of this project.
-   Click the **Go** button at the bottom of the page to start the import process.

### 4. Configure the Application

-   The database connection details are in the `config.php` file at the root of the project.
-   By default, it is configured for a standard XAMPP installation (username: `root`, no password). If you have a different MySQL username or password, you will need to update this file.

### 5. Run the Application

-   Place the entire project folder inside the `htdocs` directory of your XAMPP installation (usually `C:/xampp/htdocs/`).
-   Open your web browser and navigate to `http://localhost/{your_project_folder_name}/`. For example, if you named the folder `school-management-php`, you would go to `http://localhost/school-management-php/`.
-   You should see the login page.

## Default Login Credentials

The database includes a default root user that you can use to log in and start using the system.

-   **Email:** `root@school.app`
-   **Password:** `password`

You can log in with these credentials to get started. It is highly recommended to change the default password after your first login.

---
*This README provides the essential steps to get the application running. Further development will involve converting more features from the original Laravel application.*
