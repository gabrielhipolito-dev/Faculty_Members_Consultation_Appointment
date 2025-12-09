-- 1. Create database
CREATE DATABASE IF NOT EXISTS Faculty_Consultation1;
USE Faculty_Consultation1;

-- 2. Users table (Admin, Faculty, Student)
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin','Faculty','Student') NOT NULL,
    
    birthdate DATE,
    gender ENUM('Male','Female','Other'),
    contact_number VARCHAR(20) UNIQUE,
    address VARCHAR(255),
    profile_picture VARCHAR(255),
    status ENUM('Active','Inactive') DEFAULT 'Active'
);

-- 3. Faculty table
CREATE TABLE Faculty (
    faculty_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department VARCHAR(100),
    specialization VARCHAR(150),
    faculty_number VARCHAR(50),

    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- 4. Student table
CREATE TABLE Student (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    course VARCHAR(100),
    year_level ENUM('1','2','3','4','5'),
    student_number VARCHAR(50) UNIQUE,

    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- 5. Availability table (faculty schedule)
CREATE TABLE Availability (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday'),
    start_time TIME,
    end_time TIME,

    FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id) ON DELETE CASCADE
);

-- 6. Appointments table (student-faculty booking)
CREATE TABLE Appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    faculty_id INT NOT NULL,
    availability_id INT NOT NULL,
    appointment_date DATE,
    topic VARCHAR(255),
    purpose TEXT,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',

    FOREIGN KEY (student_id) REFERENCES Student(student_id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id) ON DELETE CASCADE,
    FOREIGN KEY (availability_id) REFERENCES Availability(availability_id) ON DELETE CASCADE,

    UNIQUE KEY unique_booking (availability_id, appointment_date)
);

-- 7. Insert default Admin user (hashed password only)
INSERT INTO Users (
    name, username, email, password, role,
    birthdate, gender, contact_number, address, profile_picture, status
) VALUES (
    'System Administrator',
    'admin',
    'admin@example.com',
    '$2y$10$w72CN6z1bKKH6Pw3LEHafeLZreUu9VkItln5gQ/Nwm8bqGqxIhUw2',  -- hashed
    'Admin',
    '1990-01-01',
    'Other',
    '+639171234567',
    '123 Admin Street, City, Country',
    '/uploads/admin/admin.jpg',
    'Active'
);

------
/*
Project Faculty_Consultation1 {
  database_type: "MySQL"
}

Table Users {
  user_id int [pk, increment]
  name varchar(100)
  username varchar(50) [unique]
  email varchar(100) [unique]
  password varchar(255)
  password_plain varchar(50) [note: 'Optional plain-text for testing only']
  role enum('Admin','Faculty','Student')

  birthdate date
  gender enum('Male','Female','Other')
  contact_number varchar(20)
  address varchar(255)

  profile_picture varchar(255)  // <-- added here so Admin also has picture

  status enum('Active','Inactive')
}

Table Faculty {
  faculty_id int [pk, increment]
  user_id int [unique, ref: > Users.user_id]
  department varchar(100)
  specialization varchar(150)
  faculty_number varchar(50)
}

Table Student {
  student_id int [pk, increment]
  user_id int [unique, ref: > Users.user_id]
  course varchar(100)
  year_level enum('1','2','3','4','5')
  student_number varchar(50)
}

Table Availability {
  availability_id int [pk, increment]
  faculty_id int [ref: > Faculty.faculty_id]
  day_of_week enum('Monday','Tuesday','Wednesday','Thursday','Friday')
  start_time time
  end_time time
}

Table Appointments {
  appointment_id int [pk, increment]
  student_id int [ref: > Student.student_id]
  faculty_id int [ref: > Faculty.faculty_id]
  availability_id int [ref: > Availability.availability_id]

  appointment_date date
  topic varchar(255)
  purpose text
  status enum('Pending','Approved','Rejected') [note: 'Simplified approval']

  indexes {
    (availability_id, appointment_date) [unique]
  }
}


*/