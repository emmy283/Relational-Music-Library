# Relational-Music-Library


A specialized backend application designed to handle relational data within a music database. This project focuses on data normalization and the enforcement of database constraints to maintain a clean and reliable dataset.

## 🚀 Key Features
* **Three-Tier Relational Schema**: Manages interconnected tables for `singer_name`, `song_type` (genres), and `song_name`.
* **Data Integrity & Constraints**: Implements `UNIQUE` constraints and `FOREIGN KEY` relationships to prevent duplicate entries and ensure referential integrity.
* **Smart Input Processing**: Features automatic string normalization (case-sensitivity handling and whitespace trimming) before database insertion.
* **Dynamic Relational Forms**: Frontend forms that dynamically populate selection menus from existing relational tables, ensuring users can only link songs to existing singers and genres.

## 🛠 Technical Implementation
* **Backend (PHP)**: Managed via **PDO** for secure, prepared SQL statements.
* **Database (SQLite)**: Designed with a normalized architecture to reduce data redundancy.
* **Session Management**: Uses PHP sessions to provide real-time feedback and error messaging to the user.

## 📂 Project Structure
* `songPage.php`: The central application containing the database initialization, business logic, and the user interface.

## 💻 Setup & Installation
1. Clone the repository to your local server directory.
2. Ensure the `$databasePath` in `songPage.php` points to your SQLite `.db` location.
3. Open the page in your browser; the system will automatically generate the required tables on the first load.
