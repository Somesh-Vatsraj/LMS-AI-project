# LMS-AI — Learning Management System with AI Tutor

A complete web-based **Learning Management System with AI Tutor (LMS-AI)** built using **PHP 8+, MySQL, HTML5, CSS3, and Vanilla JavaScript**.

The platform combines traditional LMS features with AI-powered learning assistance to help students learn, practice, and track their progress.

## 🚀 Features

### 👨‍🎓 Student Module

* Student registration and secure login
* Student dashboard
* Course catalog
* Course search and filtering
* Video lectures
* Lesson progress tracking
* Timestamped notes
* Bookmarks
* Assignment submission
* Online quizzes
* Quiz results and performance
* Learning history
* AI Tutor
* AI-generated lesson summaries
* AI-generated quizzes
* AI-generated flashcards
* Personalized study plans

### 👨‍🏫 Instructor Module

* Instructor dashboard
* Course creation and management
* Lesson management
* Video/material uploads
* Assignment creation
* Quiz creation
* Student performance tracking
* Course analytics
* Learning content management

### 🛠️ Admin Module

* Admin dashboard
* User management
* Student management
* Instructor management
* Course management
* Course approval system
* Content moderation
* Platform monitoring
* Role-Based Access Control (RBAC)

### 🤖 AI Tutor

The AI Tutor is designed to provide course-related learning assistance using course content and Retrieval-Augmented Generation (RAG) concepts.

Features include:

* Course-grounded answers
* Source references
* Lesson summaries
* Automatic quiz generation
* Flashcard generation
* Personalized study plans
* Context-aware learning assistance

## 💻 Technology Stack

| Technology | Usage                  |
| ---------- | ---------------------- |
| PHP 8+     | Backend                |
| MySQL      | Database               |
| HTML5      | Frontend Structure     |
| CSS3       | UI/Responsive Design   |
| JavaScript | Frontend Interactivity |
| AJAX       | Asynchronous Requests  |
| REST API   | API Communication      |
| RAG        | AI Knowledge Retrieval |

## 📁 Project Structure

```text
LMS-AI/
│
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── courses.php
│   └── settings.php
│
├── instructor/
│   ├── dashboard.php
│   ├── courses.php
│   ├── lessons.php
│   ├── assignments.php
│   └── analytics.php
│
├── student/
│   ├── dashboard.php
│   ├── courses.php
│   ├── lessons.php
│   ├── assignments.php
│   ├── quizzes.php
│   └── progress.php
│
├── ai/
│   ├── tutor.php
│   ├── rag.php
│   ├── summary.php
│   └── quiz-generator.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── includes/
│   ├── config.php
│   ├── database.php
│   ├── auth.php
│   └── functions.php
│
├── uploads/
│   ├── courses/
│   ├── lessons/
│   └── assignments/
│
├── database/
│   └── database.sql
│
├── index.php
├── login.php
├── register.php
└── README.md
```

## ⚙️ Requirements

Before running the project, install:

* PHP 8.0 or higher
* MySQL 5.7+ / MySQL 8+
* Apache Server
* XAMPP / WAMP / LAMP
* Modern Web Browser

## 🔧 Installation

### 1. Clone the repository

```bash
git clone https://github.com/YOUR-GITHUB-USERNAME/LMS-AI.git
```

### 2. Open the project

```bash
cd LMS-AI
```

### 3. Copy to XAMPP

Copy the project folder to:

```text
C:\xampp\htdocs\LMS-AI
```

### 4. Start XAMPP

Start:

```text
Apache
MySQL
```

from the XAMPP Control Panel.

### 5. Create Database

Open:

```text
http://localhost/phpmyadmin
```

Create a database:

```text
lms_ai
```

Import:

```text
database/database.sql
```

### 6. Configure Database

Update your database configuration file:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "lms_ai";
```

### 7. Run the Application

Open:

```text
http://localhost/LMS-AI/
```

## 🔐 Security

The project includes security-focused features such as:

* Password hashing
* Session-based authentication
* Role-Based Access Control
* Input validation
* Prepared SQL statements
* Access control for protected pages
* File upload validation

## 📊 Main Modules

```text
Authentication
      ↓
Role-Based Access Control
      ↓
 ┌──────────┬─────────────┬───────────┐
 │ Student  │ Instructor  │   Admin   │
 └──────────┴─────────────┴───────────┘
      ↓           ↓             ↓
   Courses     Content       Management
      ↓           ↓             ↓
 Assignments    Analytics     Approval
      ↓
   Quizzes
      ↓
 Learning Progress
      ↓
   AI Tutor
```

## 🎯 Project Objective

The main objective of LMS-AI is to provide a centralized digital learning platform where students can access educational content, complete assignments and quizzes, track their progress, and receive AI-assisted learning support.

## 👨‍💻 Developer

**Somesh Vatsraj**

BCA Student & PHP/MySQL Developer

### Skills

* PHP
* MySQL
* HTML5
* CSS3
* JavaScript
* C
* Java
* Python
* DBMS

## 📌 Project Status

**Status:** Active Development

This project is being developed as a professional portfolio project and can be extended with additional AI, payment, notification, analytics, and communication features.

## 📄 License

This project is intended for educational and portfolio purposes.
