# 🧩 Task Management API (Laravel)

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://www.php.net/)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-6DB33F.svg)](https://laravel.com/docs/sanctum)
[![Queues](https://img.shields.io/badge/Queues-Redis-orange.svg)](https://laravel.com/docs/queues)

A clean, production‑style **REST API** for managing **tasks** and **comments**, secured with **Laravel Sanctum** and powered by **queued notifications** (Redis).  
**Developed by _Amr Fouda_.**

---

## ✨ Features

- **Authentication** with Laravel **Sanctum** (token‑based).
- **Tasks**: CRUD, status (`pending`, `in-progress`, `completed`), due date, assignee.
- **Comments**: create & list per task.
- **Authorization (Policies)**:
  - Author can update/delete a task.
  - Author **or** assignee can view a task.
  - Commenter **or** task author can edit/delete comments.
- **Async notifications**: when a comment is added, the task author is notified via **queued** mail/notification.
- **Queues**: Redis (dev) with `queue:work` or Horizon (optional).

> This README mixes developer‑focused setup with portfolio‑friendly presentation.


---

## 🧱 Tech Stack

- **Laravel** 12.x (PHP 8.2+)
- **Database**: MySQL (or SQLite for local dev)
- **Auth**: Laravel **Sanctum**
- **Queues**: **Redis** (dev) / Database driver fallback
- **Mail**: SMTP (configurable via `.env`)


---

## 🚀 Getting Started

### 1) Clone & Install
```bash
git clone https://github.com/amrfouda/task-api.git
cd task-api
sudo apt update
sudo apt install composer
sudo apt install php-xml
composer install
sudo apt install php-mysql
sudo apt install mysql-server
sudo mysql

Then inside the MySQL shell:
  CREATE DATABASE laravel;
  CREATE USER 'laravel'@'127.0.0.1' IDENTIFIED BY 'secret';
  GRANT ALL PRIVILEGES ON laravel.* TO 'laravel'@'127.0.0.1';
  FLUSH PRIVILEGES;
  EXIT;
```

### 2) Environment
Copy and configure your environment:
```bash
cp .env.example .env
php artisan key:generate
```

Minimal `.env` essentials (tweak as needed):
```env
APP_NAME="Task Management API"
APP_URL=http://localhost:8000

# DB (choose one)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=

# or SQLite
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite

# Queues
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_CLIENT=phpredis   # or 'predis'

# Mail (example: Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

> After changing `.env`, run:  
> `php artisan config:clear` and `php artisan cache:clear`.

### 3) Database & Queues
```bash
php artisan migrate
php artisan queue:failed-table && php artisan migrate   # for failed jobs storage
```

Start a Redis server (pick one):

- **WSL/Ubuntu**
  ```bash
  sudo apt update && sudo apt install -y redis-server
  sudo service redis-server start
  redis-cli PING  # PONG
  ```
- **Docker**
  ```bash
  docker run -d --name redis -p 6379:6379 redis
  docker exec -it redis redis-cli PING  # PONG
  ```

Run a worker (dev):
```bash
php artisan queue:work redis --sleep=1 --tries=3 --timeout=90 -v
```

### 4) Serve API
```bash
php artisan serve
# http://localhost:8000
```


---

## 🔐 Authentication (Sanctum, token-based)

Typical endpoints (adjust if your routes differ):

- **Register** — `POST /api/register`
- **Login** — `POST /api/login` → returns token
- **Logout** — `POST /api/logout` (requires Bearer token)

Send the token on subsequent requests:
```
Authorization: Bearer <token>
```


---

## 📡 API Endpoints (Examples)

> **Note**: Paths below assume resourceful routes like `Route::apiResource('tasks', TaskController::class)` and nested comments.

### Tasks
**Create Task**  
`POST /api/tasks`
```json
{
  "title": "Write README",
  "description": "Prepare a professional README for the API",
  "status": "pending",
  "due_date": "2025-11-01",
  "assignee_id": 2
}
```
**Response 201**
```json
{
  "id": 1,
  "title": "Write README",
  "description": "Prepare a professional README for the API",
  "status": "pending",
  "due_date": "2025-11-01",
  "author_id": 1,
  "assignee_id": 2,
  "created_at": "2025-10-26T10:00:00Z"
}
```

**List Tasks**  
`GET /api/tasks` → 200 `[ ... ]`

**Show Task**  
`GET /api/tasks/{id}` → 200 (includes `author`, `assignee` if eager loaded)

**Update Task**  
`PUT /api/tasks/{id}`
```json
{ "status": "in-progress" }
```
→ 200 updated task

**Delete Task**  
`DELETE /api/tasks/{id}` → 204


### Comments
**Add Comment**  
`POST /api/tasks/{task}/comments`
```json
{ "body": "Looks good—please add API examples." }
```
→ 201 and **triggers a queued notification** to the task author.

**List Comments for Task**  
`GET /api/tasks/{task}/comments` → 200 `[ ... ]`


---

## 🛡️ Authorization Summary (Policies)

- **Tasks**
  - `viewAny`: any authenticated user.
  - `view`: author **or** assignee.
  - `create`: any authenticated user.
  - `update/delete`: **author only** (customize as needed).

- **Comments**
  - `viewAny`: authenticated.
  - `update/delete`: **commenter** or **task author**.


---

## 📬 Queued Notifications (Redis)

When a comment is created, a **Notification** (e.g., `NewCommentNotification`) is dispatched **asynchronously**:
- Notification implements `ShouldQueue` (or is sent via `->queue()`).
- Jobs are pushed to Redis (`queues:default`).
- A worker (`queue:work redis`) processes and sends the email without delaying API responses.

If something fails:
```bash
php artisan queue:failed     # inspect failed jobs
php artisan queue:retry all  # retry
```

Optional dashboard:
```bash
composer require laravel/horizon
php artisan horizon:install && php artisan migrate
php artisan horizon
# http://localhost/horizon
```


---

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

> In Feature tests, use helpers like `assertDatabaseHas()` to verify persisted state.


---

## 📬 Postman Collection

1. Import the provided **Postman collection** (`postman/Task-API.postman_collection.json`) and **environment** (`postman/local.postman_environment.json`) if included.
2. Set `base_url` to `http://localhost:8000`.
3. Run in order:
   - **Auth / Register** → **Auth / Login** → set `token` env var automatically via test script (or paste manually).
   - Use token in **Tasks** and **Comments** requests (Authorization tab → `Bearer Token`).

> If you don’t have a collection exported yet, you can generate one quickly from your existing routes with a REST client or share request examples from this README.


---

## 🗂️ Project Structure (high level)

```
app/
 ├── Http/
 │    ├── Controllers/       # TaskController, CommentController, Auth controllers
 │    ├── Requests/          # TaskStoreRequest, TaskUpdateRequest, CommentStoreRequest
 │    └── Middleware/
 ├── Jobs/                   # e.g., NewCommentOnTask (dispatches notifications)
 ├── Notifications/          # NewCommentNotification (ShouldQueue)
 ├── Models/                 # Task, Comment, User
 └── Policies/               # TaskPolicy, CommentPolicy
routes/
 └── api.php
database/
 ├── migrations/
 └── seeders/
```


---

## 🛣️ Common Commands

```bash
# Config & cache
php artisan config:clear
php artisan cache:clear

# DB
php artisan migrate
php artisan migrate:fresh --seed

# Queues
php artisan queue:work redis -v
php artisan queue:failed
php artisan queue:retry all

# Serve
php artisan serve
```

---

## 👤 Author

**Amr Fouda** — Software Engineer (Laravel, REST APIs, C++, Qt)  
Cairo, Egypt