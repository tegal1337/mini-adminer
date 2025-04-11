# Mini Adminer 🛠️

Mini Adminer is a simple yet powerful tool to interact with your databases. It allows you to execute SQL queries, import SQL files, export databases, and dump database content. 🗃️

## Features ✨

- 🔍 Execute SQL queries
- 📤 Import SQL files
- 📥 Export database to a SQL file
- 💾 Dump the entire database for backups
- 🚀 Supports MySQL, PostgreSQL, and SQLite

## Installation 🔧

### Prerequisites 📌

Ensure you have a database server installed:

- **MySQL** / **MariaDB**
- **PostgreSQL**
- **SQLite**

### Installation Steps 🚶‍♂️

1. **Clone the Repository**  
   Clone the repository to your local machine:
   ```bash
   git clone https://github.com/williamlaurent/mini-adminer.git
   cd mini-adminer
   ```

2. **Configure Database Credentials**  
   Open the mini-adminer-v2.php file and update the database credentials:
   ```bash
   $db_host = 'localhost';
   $db_username = 'your_db_username';
   $db_password = 'your_db_password';
   $db_name = 'your_db_name';
   ```

3. **Run the Application**  
   Upload the mini-adminer-v2.php file to your web server and access it via your browser:
   ```bash
   http://localhost/mini_adminer.php
   ```
   You will be prompted to enter the username (human) and password (password) for basic authentication. 🔒
