# St. Joseph's Vocational SS Nyamityobora - School Management System

## Project Vision

Develop a robust and user-friendly web-based School Management System for St. Joseph's Vocational SS Nyamityobora. The system must be highly responsive, supporting desktop, tablet, and mobile views, and feature a modern, intuitive user interface with theme-changing capabilities.

---

## Development Log & Instructions

### Local Development Setup

1.  **Prerequisites:** Ensure you have XAMPP (for MySQL) and Node.js installed.
2.  **Database:** Start the MySQL service in XAMPP and create a database named `st_josephs_db`.
3.  **Environment:** Copy `.env.example` to `.env` and ensure the `DB_` variables are correctly set for your local MySQL.
4.  **Dependencies:** Run `composer install` and `npm install`.
5.  **Application Key:** Run `php artisan key:generate`.
6.  **Clear Caches:** Run `php artisan config:clear` and `php artisan cache:clear` to ensure your `.env` settings are loaded.
7.  **Database Setup:** Run `php artisan migrate:fresh --seed` to create all tables and the initial admin user.
8.  **Run the Servers:**
    *   In one terminal, run `php artisan serve`.
    *   In a **second** terminal, run `npm run dev`.
9.  **Access:** Open your browser to `http://127.0.0.1:8000`.

---

## Project Status & Completed Features

### Phase 1: Core System Setup and User Management (Complete)
- [x] Project Initialization and Database Setup
- [x] UI and Authentication Scaffolding (Laravel Breeze)
- [x] Enhanced User Model with Custom Fields and Roles
- [x] Initial `Root` User Account Seeder
- [x] Core UI Layout (Sidebar, Top Nav, Footer)
- [x] Role-Based Access Control (RBAC) Middleware

### Phase 2: Academic Core (Complete)
- [x] Class & Stream Management
- [x] Subject & Paper Management
- [x] Teacher & Student Assignment System
- [x] Mark Entry System for Teachers
- [x] **New:** Scaffolding for a new "Exams" module.

### Phase 3: Student Lifecycle & Documents (Complete)
- [x] Advanced Student Data Management UI with search, filters, and photo upload.
- [x] Bulk Student Upload (Excel) & Data Download (PDF & Excel).
- [x] Report Card & ID Card Generation (PDF with Photo & QR Code).

### Phase 4: Communication & Content (Complete)
- [x] Real-time Chat for staff communication.
- [x] Content Sharing via a simple Video Library.
- [x] **New:** Bulk Messaging system (Email/SMS).

### Phase 5: Financial Management (Complete)
- [x] Fee & Expense Category Management.
- [x] Fee Structure creation system.
- [x] Automatic Invoice Generation and manual payment recording.
- [x] Expense Recording and Categorization.
- [x] Financial Reporting (Outstanding Balances, Income vs. Expenditure, etc.).

### Phase 6: Student Welfare & Co-curriculars (Complete)
- [x] Dormitory / Hostel Management (Dormitories and Rooms).
- [x] Student Room Assignment System.
- [x] Extracurricular Activity (Clubs) Management, including memberships.
- [ ] Discipline & Conduct Log (Planned for future release)
- [ ] Health & Medical Records (Planned for future release)

### Phase 7: Library & Resource Management (Complete)
- [x] Digital Library System with Book Catalog (CRUD).
- [x] Book Checkout and Check-in System.
- [x] General School Inventory Management (CRUD).
- [x] Shared Resource Booking System with a calendar view.

### Phase 8: Enhanced Portals & Engagement (In Progress)
- [x] Digital Notice Board via the "Announcements" module (CRUD).
- [ ] Dedicated Parent/Guardian Portal (Planned for future release)
- [ ] Dedicated Student Portal (Planned for future release)

### Phase 9 & 10: Advanced Administration & AI (In Progress)
- [x] **New:** Automatic Daily Database Backup system with UI for on-demand backups.
- [x] **New:** Scaffolding for AI-powered reports.
- [ ] System Audit Trail (Planned for future release)
- [ ] Alumni Network (Planned for future release)
- [ ] Advanced Timetabling (Planned for future release)
