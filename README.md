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

### 2️⃣ Install Dependencies
composer install
