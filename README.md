# Mini Adminer 🛠️

Mini Adminer is a lightweight, single-file PHP database management tool designed for quick and direct interaction with your MySQL/MariaDB databases. It allows you to execute full SQL queries, import `.sql` files, and export database backups cleanly. 🗃️

## Features ✨

- ⚡ **Full SQL Query Execution** — Supports all SQL operations (`SELECT`, `INSERT`, `UPDATE`, `DELETE`, `CREATE`, `ALTER`, `DROP`, etc.) including multi-query execution
- 📊 **Affected Rows & Result Viewer** — Displays formatted tables for read queries and affected row counts for write operations
- 📤 **Import SQL Files** — Upload and execute `.sql` or `.txt` dump files directly
- 📥 **Export / Dump Database** — Generate clean `.sql` backups with proper `NULL` handling and string escaping
- 🔐 **HTTP Basic Authentication** — Built-in login prompt to restrict unauthorized access

## What's New in v3 🚀

Upgraded from `v2` (`mini-adminer-v2.php`) to `v3` (`/v3/mini-adminer-v3.php`):

- **Removed Read-Only Restriction:** Eliminated the `safe_query()` filter so you can now run write, update, delete, and schema modification queries directly.
- **Multi-Query Execution Support:** Switched to `mysqli::multi_query()` with proper result buffer clearing, allowing multiple semicolon-separated SQL statements to run in one go.
- **Improved SQL Export & Dump:** Replaced `addslashes()` with `real_escape_string()`, added proper `NULL` value preservation, and included `DROP TABLE IF EXISTS` statements so exported backups re-import cleanly without syntax errors.
- **UTF-8 (`utf8mb4`) Support:** Enforced `utf8mb4` charset on connection to prevent encoding issues with emojis and special characters.
- **Enhanced UI & Feedback:** Added clear success/error banners, query persistence in the textarea after execution, and horizontal scrolling for wide result tables.

## Installation 🔧

### Prerequisites 📌

- **PHP 7.4+** (with `mysqli` extension enabled)
- **MySQL** or **MariaDB** server

### Installation Steps 🚶‍♂️

1. **Clone the Repository**  
   Clone the repository to your local machine:
   ```bash
   git clone [https://github.com/tegal1337/mini-adminer.git](https://github.com/tegal1337/mini-adminer.git)
   cd mini-adminer
   ```

2. **Configure Database Credentials & Auth**  
   Open `v3/mini-adminer-v3.php` and update the database credentials (and change the default login credentials):
   ```php
   $db_host = 'localhost';
   $db_username = 'your_db_username';
   $db_password = 'your_db_password';
   $db_name = 'your_db_name';
   ```

3. **Run the Application**  
   Upload `v3/mini-adminer-v3.php` to your web server and access it via your browser:
   ```text
   http://localhost/v3/mini-adminer-v3.php
   ```
   You will be prompted to enter the username (`human`) and password (`password`) for HTTP Basic Authentication. 🔒

## Security Notice ⚠️

Because **v3** allows full read/write/drop SQL execution, make sure to:
- Change the default `PHP_AUTH_USER` and `PHP_AUTH_PW` values before deploying to a live server.
- Remove the file from production servers once your database maintenance is finished.

---

Made by **Indonesian Code Party**
