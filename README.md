# Learning Management System (LMS)

A comprehensive Learning Management System built with Vue.js frontend and Laravel backend, designed to integrate with an existing Student Management System. This LMS provides course management, quiz creation and submission, forum discussions, and certificate generation for educational institutions.

## 🎯 Features

### For Students
- View enrolled courses only
- Access course materials and content
- Take quizzes and view results
- Participate in course forums
- View earned certificates
- Track course progress

### For Teachers
- Manage assigned courses
- Create and manage quizzes
- View student enrollments
- Monitor student progress
- Participate in course forums

### For Admins
- Create and manage all courses
- Assign courses to teachers
- View all quizzes across courses
- Monitor system-wide statistics
- Manage users (via Student Management System integration)

## 🛠️ Tech Stack

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Vue Router** - Client-side routing
- **Pinia** - State management
- **Tailwind CSS** - Utility-first CSS framework
- **Axios** - HTTP client
- **Vite** - Build tool and dev server

### Backend
- **Laravel** - PHP framework
- **MySQL** - Database
- **JWT Authentication** - Secure API authentication using `tymon/jwt-auth`
- **Spatie Permission** - Role-based access control

## 📋 Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 16
- npm or yarn
- MySQL >= 5.7
- Git

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Vyom03/learning-management-system.git
cd learning-management-system
```

### 2. Backend Setup (Laravel)

```bash
cd laravel-backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
# Update these values:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=loginauth
# DB_USERNAME=root
# DB_PASSWORD=your_password
```

### 3. Database Setup

#### Option A: Using SQL Files (Recommended)

Run the SQL files in order:

```bash
# 1. Create indexes for performance
mysql -u root -p loginauth < create-indexes-safe.sql

# 2. Create all LMS tables (quizzes, forums, quiz_attempts)
mysql -u root -p loginauth < setup-all-tables.sql

# Or on Windows, use the batch files:
create-indexes.bat
setup-all-tables.bat
```

#### Option B: Manual Setup

1. Ensure your MySQL database `loginauth` exists and contains:
   - `users` table
   - `courses` table
   - `enrollments` table
   - `roles` and `model_has_roles` tables (from Spatie Permission)

2. Create the following tables using the SQL files:
   - `quizzes` and `quiz_questions` (from `create-quiz-tables.sql`)
   - `forum_topics` and `forum_replies` (from `create-forum-tables.sql`)
   - `quiz_attempts` (from `create-quiz-attempts-table.sql`)

### 4. JWT Configuration

```bash
# Publish JWT config
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

# Generate JWT secret
php artisan jwt:secret
```

### 5. Frontend Setup (Vue.js)

```bash
cd frontend

# Install dependencies
npm install

# The frontend is configured to run on port 5177
# Update vite.config.js if you need a different port
```

### 6. Start Development Servers

#### Option A: Using Start Script (Recommended)
```bash
# Start both servers at once
start-servers.bat

# Stop all servers
kill-servers.bat
```

#### Option B: Manual Start

**Backend (Laravel)**
```bash
cd laravel-backend
php artisan serve
# Backend runs on http://localhost:8000
```

**Frontend (Vue.js)**
```bash
cd frontend
npm run dev
# Frontend runs on http://localhost:5177
```

**Note**: Use `kill-servers.bat` to force stop all servers if they hang or won't respond.

## ⚙️ Configuration

### Backend Configuration

1. **Database**: Update `.env` file with your MySQL credentials
2. **CORS**: Configured in `config/cors.php` to allow requests from `http://localhost:5177`
3. **JWT**: Secret key is automatically generated

### Frontend Configuration

1. **API Proxy**: Configured in `vite.config.js` to proxy `/api` requests to `http://localhost:8000` (Laravel backend)
2. **Port**: Frontend runs on port `5177` (configurable in `vite.config.js`)

**Important**: Make sure the proxy target in `vite.config.js` matches your Laravel backend port (default: 8000).

## 📚 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `GET /api/auth/me` - Get current user (protected)

### Dashboard
- `GET /api/dashboard/stats` - Get dashboard statistics (protected)

### Courses
- `GET /api/courses` - List courses (filtered by role)
- `GET /api/courses/{id}` - Get course details
- `POST /api/courses` - Create course (admin only)
- `PUT /api/courses/{id}` - Update course (teacher/admin)
- `DELETE /api/courses/{id}` - Delete course (teacher/admin)

### Enrollments
- `GET /api/enrollments/my-courses` - Get student's enrolled courses
- `POST /api/enrollments/{courseId}` - Enroll in course (student only)

### Quizzes
- `POST /api/quizzes` - Create quiz (teacher only)
- `GET /api/quizzes/course/{courseId}` - Get quizzes for a course
- `GET /api/quizzes/{id}` - Get quiz details
- `POST /api/quizzes/{id}/submit` - Submit quiz answers (student only)

### Forums
- `GET /api/forums/course/{courseId}/topics` - Get forum topics
- `GET /api/forums/topics/{topicId}` - Get topic with replies
- `POST /api/forums/topics` - Create forum topic
- `POST /api/forums/replies` - Create forum reply

### Certificates
- `GET /api/certificates/my-certificates` - Get student's certificates

## 🔐 Authentication & Authorization

### User Roles
- **Student**: Can view enrolled courses, take quizzes, participate in forums
- **Teacher**: Can manage assigned courses, create quizzes, view students
- **Admin**: Can manage all courses, view all data, assign courses to teachers

### Access Control
- Students can only see courses they are enrolled in
- Teachers can only see and manage their own courses
- Admins can see all courses and manage everything
- Quiz creation is restricted to teachers only
- Course creation is restricted to admins only

## 📁 Project Structure

```
lms/
├── frontend/                 # Vue.js frontend application
│   ├── src/
│   │   ├── components/      # Reusable Vue components
│   │   ├── views/          # Page components
│   │   ├── router/         # Vue Router configuration
│   │   ├── stores/         # Pinia state management
│   │   └── services/       # API service layer
│   └── package.json
│
├── laravel-backend/         # Laravel backend API
│   ├── app/
│   │   └── Http/
│   │       └── Controllers/
│   │           └── Api/    # API controllers
│   ├── routes/
│   │   └── api.php        # API routes
│   └── database/
│       └── migrations/    # Database migrations
│
└── README.md
```

## 🗄️ Database Schema

### Core Tables
- `users` - User accounts (from Student Management System)
- `courses` - Course information
- `enrollments` - Student course enrollments
- `roles` & `model_has_roles` - Role-based permissions

### LMS Tables
- `quizzes` - Quiz information
- `quiz_questions` - Quiz questions with options
- `quiz_attempts` - Student quiz submissions and scores
- `forum_topics` - Forum discussion topics
- `forum_replies` - Forum topic replies
- `certificates` - Student certificates (optional)

## 🚦 Usage

### For Administrators

1. **Login** with admin credentials
2. **Create Courses** from the "Create Course" link in navigation
3. **Assign Teachers** to courses (via database or Student Management System)
4. **Monitor** system statistics on the dashboard

### For Teachers

1. **Login** with teacher credentials
2. **View Courses** assigned to you on the dashboard
3. **Create Quizzes** by navigating to a course and clicking "Create Quiz"
4. **Monitor** student progress and enrollments

### For Students

1. **Login** with student credentials (from Student Management System)
2. **View Enrolled Courses** on the dashboard
3. **Access Course Content** by clicking on a course
4. **Take Quizzes** and view results
5. **Participate in Forums** for course discussions
6. **View Certificates** upon course completion

## 🔧 Troubleshooting

### Common Issues

1. **Servers Hanging or Won't Stop**
   - Run `kill-servers.bat` to force stop all Node.js and PHP processes
   - Check Task Manager for lingering processes
   - Restart if needed

2. **Database Connection Errors**
   - Verify MySQL is running
   - Check `.env` database credentials
   - Ensure database `loginauth` exists

3. **JWT Authentication Errors**
   - Run `php artisan jwt:secret` to generate secret key
   - Clear Laravel cache: `php artisan config:clear`

4. **CORS Errors**
   - Verify CORS configuration in `config/cors.php`
   - Ensure frontend URL matches allowed origins

5. **Port Conflicts**
   - Frontend defaults to port 5177
   - Backend defaults to port 8000
   - Update ports in configuration files if needed
   - Use `kill-servers.bat` to free up ports

6. **Missing Tables**
   - Run `setup-all-tables.bat` or `setup-all-tables.sql`
   - Check database for required tables

7. **Commands Hanging**
   - If any command hangs, press `Ctrl+C` to cancel
   - Use `kill-servers.bat` to stop all server processes
   - Check for long-running MySQL queries or locked tables

## 📝 Development Notes

- The frontend uses Vite for development and building
- API requests are proxied through Vite dev server
- All API endpoints require JWT authentication except login
- Role information is included in JWT token claims
- Database indexes are optimized for performance

## 🤝 Integration with Student Management System

This LMS is designed to work with an existing Student Management System:
- Uses the same MySQL database (`loginauth`)
- Shares user accounts and roles
- Students/Teachers are created through the SMS
- Enrollment management can be done through SMS
- No user registration in LMS (students must be added via SMS)

## 📄 License

This project is part of a larger educational management system.

## 👤 Author

**Vyom03**

## 🙏 Acknowledgments

- Built with Laravel and Vue.js
- Integrated with Spatie Permission package
- Uses Tymon JWT Auth for authentication
