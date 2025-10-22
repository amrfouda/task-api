# 🧩 Laravel Task Management API

A backend RESTful API built with **Laravel 12.x** for managing **tasks**, **comments**, and **user assignments**.  
Implements authentication, authorization, notifications, and queued background jobs — following Laravel best practices and clean architecture principles.

---

## 🚀 Features

### 🔹 Task Management
- Create, update, delete, and list tasks.
- Fields: `title`, `description`, `status` (`pending`, `in-progress`, `completed`), `due_date`.
- Assign tasks to users and manage task ownership.
- Validation handled via custom **Form Request** classes.

### 🔹 User Authentication
- Secured with **Laravel Sanctum**.
- Only authenticated users can manage tasks and comments.
- Supports token-based authentication (`Bearer <token>`).

### 🔹 Authorization (Policies)
- **TaskPolicy**:
  - Only the task author can update or delete.
  - Author and assignee can view.
  - Any authenticated user can list or create tasks.
- **CommentPolicy**:
  - Only the commenter or task author can edit/delete.

### 🔹 Comments
- Users can comment on tasks.
- Task author gets **email notifications** when new comments are added.
- Notifications are queued asynchronously using Laravel’s **Queue system**.

### 🔹 Queues & Notifications
- Background jobs are handled via the **database queue driver**.
- Uses queued **Mailables** for comment notifications.

### 🔹 Optional Enhancements
- **Caching** for improved performance (tasks & comments).
- **Unit and Feature Tests** for reliability.

---

## ⚙️ Tech Stack

| Component | Technology |
|:-----------|:------------|
| Framework | Laravel 12.x |
| Language | PHP 8.2+ |
| Database | MySQL / SQLite |
| Authentication | Laravel Sanctum |
| Queue Driver | Database |
| Mail | SMTP (configurable) |
| Cache | File / Redis |

---

## 🧰 Installation & Setup

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/<your-username>/task-api.git
cd task-api
```

### 2️⃣ Install Dependencies
```bash
composer install
```

### 3️⃣ Environment Setup
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Update key environment variables:
```env
APP_NAME="Task Management API"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql       # or sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_DRIVER=file

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4️⃣ Generate Keys and Run Migrations
```bash
php artisan key:generate
php artisan migrate
```

### 5️⃣ Create Queue Table (if needed)
```bash
php artisan queue:table
php artisan migrate
```

### 6️⃣ Start the Server
```bash
php artisan serve
```
API runs on **http://localhost:8000**

---

## 🔑 Authentication (Laravel Sanctum)

### Register
```http
POST /api/register
```

### Login
```http
POST /api/login
```

Use the returned token in your request headers:
```
Authorization: Bearer <token>
```

---

## 📡 API Endpoints

| Method | Endpoint | Description |
|:-------|:----------|:-------------|
| `GET` | `/api/tasks` | List all tasks |
| `POST` | `/api/tasks` | Create a new task |
| `GET` | `/api/tasks/{id}` | View a specific task |
| `PUT` | `/api/tasks/{id}` | Update a task |
| `DELETE` | `/api/tasks/{id}` | Delete a task |
| `POST` | `/api/tasks/{id}/comments` | Add a comment |
| `GET` | `/api/tasks/{id}/comments` | List comments for a task |

---

## 🔔 Email Notifications
Whenever a comment is added, the **task author** receives an **email notification**.  
The email is sent through Laravel’s queue system to ensure non-blocking performance.

---

## 🧠 Design & Architecture Highlights
- **Repository & Service pattern** (optional abstraction for business logic).
- **Policy-based authorization** using Laravel’s built-in gate system.
- **Form Requests** for clean validation logic.
- **Queued notifications** for scalability.
- **RESTful controllers** for consistency.
- **Caching layer** for performance optimization.

---

## 🧪 Testing
Run automated tests:
```bash
php artisan test
```

---

## 🗂️ Project Structure
```
app/
 ├── Http/
 │    ├── Controllers/
 │    ├── Requests/
 │    └── Middleware/
 ├── Models/
 ├── Policies/
 └── Notifications/
routes/
 └── api.php
database/
 ├── migrations/
 └── seeders/
```

---

## 🧑‍💻 Author
**Amr Fouda**  
📍 Cairo, Egypt  
💻 Software Engineer — Laravel, C++, Qt, and REST API Developer  
🌐 [LinkedIn](https://www.linkedin.com/in/amrfouda)

---

## 🧾 License
This project is open-source under the [MIT License](LICENSE).

---

✅ *Built with Laravel and passion for clean, maintainable backend architecture.*
