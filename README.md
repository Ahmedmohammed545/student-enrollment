# Student Enrollment System

A web-based enrollment system for Lebawi International Academy, with an admin portal for staff and a client portal for parents. Built with PHP and MySQL.

## Features

**Admin portal**
- Admin login and a dashboard with totals, charts (gender, grade, age) and recent approvals
- Review pending registrations: approve (and assign a section) or decline
- Add, edit, delete and filter students, and view their uploaded transcripts and birth certificates (PDF)
- Class capacities per grade and section (default 25) with Full / Almost Full / Available status
- Promote students to the next grade or section in bulk, with progression history
- User list and profile management

**Client (parent) portal**
- Sign up and log in
- Step-by-step student registration: grade, student details, then photo, transcript and birth certificate
- Dashboard showing registration status (pending, approved, declined) and a confirmation slip

**Built-in logic**
- Auto-generated student IDs (`LIA####`), age and graduation year
- Validation for Ethiopian phone numbers (`+251...`) and file types (images and PDFs)

## Built with
PHP, MySQL/MariaDB, Bootstrap, jQuery, Chart.js, Font Awesome

## Getting started
1. Install [XAMPP](https://www.apachefriends.org/) (Apache + MySQL) and copy this project into `htdocs`.
2. Create a database named `student` and import `student.sql`.
3. Check the database settings in `admin/db_con.php`.
4. Create an admin user in phpMyAdmin:
   ```sql
   INSERT INTO users (name, username, password, email, photo, status, role)
   VALUES ('Admin', 'admin_user', SHA1(MD5('your-password')), 'you@example.com', '', 'active', 'admin');
   ```
5. Open `http://localhost/<project-folder>/admin/login.php` for staff or `.../client/index.php` for parents.

## Project structure
```
admin/        staff portal (pages load through index.php?page=...)
client/       parent portal
css/, js/     shared assets
student.sql   database schema
```

## Known limitations
- Passwords are hashed with SHA1(MD5); this should move to `password_hash()`.
- A few queries (for example in `delete.php`) are not parameterised yet.
- The local setup uses MySQL `root` with no password.

## Authors
Team project by [names]. My part: [what you built, e.g. client registration flow, approval workflow].
