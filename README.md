# 🏛️ Faculty Members Consultation Appointment System

## ⭐ Project Lead Credit

**Author/Lead:** HIPOLITO GABRIEL, Software Lead

---

## 🌟 Overview

This guide provides the streamlined process for setting up the **Faculty Members Consultation Appointment System** using XAMPP and Git Bash. This workflow ensures that users can correctly locate the XAMPP directory regardless of their initial terminal location.

---

## 1. Initial Setup: Tools and Installation

### 1.1. ⬇️ Download & Install XAMPP

1.  **Download:** Go to the official XAMPP website: **[https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)** and download the installer for your OS.
2.  **Install:** Run the installer. Keep the default path (e.g., `C:\xampp`) to avoid permission issues.

### 1.2. ⬇️ Download & Install Git Bash (or Git)

1.  **Download:** Get the Git installer from **[https://git-scm.com/downloads](https://git-scm.com/downloads)**.
2.  **Install:** Run the installer, accepting the default options. This provides the **Git Bash** terminal for cloning the repository.

---

## 2. Server Activation and Project Cloning

### 2.1. 🟢 Start XAMPP Services

1.  Open the **XAMPP Control Panel**.
2.  Click the **Start** button for the following modules:
    * **Apache** (Web Server)
    * **MySQL** (Database Server)
3.  Ensure both modules turn **green** before proceeding. 

### 2.2. 📂 Navigate to htdocs using Git Bash

We need to navigate to the web root folder, `htdocs`.

1.  Open **Git Bash**. You will start in your home directory, indicated by `~`.
2.  Navigate up the directory tree until you reach the root (C: drive on Windows):
    ```bash
    cd ../..
    ```
3.  Use the `ls` command to list the contents of the current folder and locate the XAMPP directory. You may need to run `cd ../..` and `ls` a few times to find it.
    ```bash
    ls
    ```
4.  Once you see the `xampp` folder (or equivalent path on Mac/Linux), navigate into the `htdocs` folder:
    ```bash
    cd xampp/htdocs
    ```

### 2.3. 📥 Clone the Repository and Open Code Editor

1.  While in the `htdocs` directory in Git Bash, run the clone command. This downloads the repository and creates the project folder.

    ```bash
    git clone [YOUR_REPOSITORY_URL_HERE]
    ```

2.  Change your directory (`cd`) *into* the newly cloned repository folder:

    ```bash
    cd Faculty_Members_Consultation_Appointment
    ```

3.  Finally, open the entire project folder in your VS Code editor:

    ```bash
    code .
    ```

---

## 3. Final Steps: Database and Admin Account

### 3.1. ⚙️ Database Setup

The project requires a database named **`faculty_consultation1`** and uses the **`main_db.sql`** file for table creation and initial data.

1.  **Access phpMyAdmin:** Open your browser and go to `http://localhost/phpmyadmin/`.
2.  **Import Data:**
    * **Select** the newly created **`faculty_consultation1`** from the left sidebar.
    * Click the **Import** tab at the top.
    * Click **Choose file** and locate the database dump file: **`main_db.sql`** (it should be inside your project folder).
    * Click the **Go** button to execute the import.

### 3.2. 🔑 Set Admin Password (Using Hash Generator)

Because passwords are secured using PHP's `password_hash` function (bcrypt) to prevent SQL injection, the initial admin password must be set manually.

1.  **Generate the Hash:**
    * Open your web browser and navigate to the file responsible for generating the hash:
        ```
        http://localhost/Faculty_Members_Consultation_Appointment/generate_hash.php
        ```
    * The script in this file is: `<?php echo password_hash('admin123', PASSWORD_DEFAULT); ?>`
    * The browser will display a long, hashed string (e.g., `$2y$10$T8O...`). **Copy this entire string.**

2.  **Update the Database:**
    * Go back to phpMyAdmin: `http://localhost/phpmyadmin/`.
    * Select the **`faculty_consultation1`** database.
    * Click on the **`user`** table.
    * Find the **admin** record (or the user you wish to update).
    * Click **Edit**.
    * Locate the `password` field and **paste the copied hash string** into the Value input box.
    * Click **Go** to save the changes.

### 3.3. 🌐 Access the Application

The application is now live on your local server.

* Open your web browser and navigate to:
    ```
    http://localhost/Faculty_Members_Consultation_Appointment/
    ```
* You can now log in using the username `admin` and password `admin123`.

---

## 4. 🤝 Contribution Guidelines

If you wish to contribute improvements or fix bugs, please follow the standard **Fork and Pull Request** workflow:

1.  **Fork** the repository on the hosting platform (e.g., GitHub).
2.  **Clone** your forked repository to your local machine (using the steps in Section 2.2, but cloning your fork instead).
3.  Create a new feature branch (`git checkout -b feature/my-new-feature`).
4.  Make your changes, commit them with clear messages, and push them to your fork.
5.  Open a **Pull Request (PR)** from your fork's branch to the main project repository's `main` branch.

All contributions are welcome and greatly appreciated!

---
This guide is now fully comprehensive, covering all the setup, security, and contribution steps.
