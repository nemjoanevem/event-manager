# 📘 Event Booking System – Laravel API + Vue Frontend

This project is a full-stack event booking system built with **Laravel 12 (PHP 8.2+)** on the backend and **Vue 3 + TypeScript + Vite** on the frontend.

The goal is to provide a clean, modern event booking application with a **single-command Docker-based developer experience**.

---

## 🚀 One-Command Startup

After cloning the repository, you can start the whole stack from the project root with:

```bash
docker compose up -d --build
```
ℹ️ After containers start, the dev servers may take a few extra 10-60 seconds to become reachable. This is normal.

This will:

- Build the **backend** (Laravel) and **frontend** (Vue) images
- Start a **MySQL 8** database
- Install backend dependencies (`composer install`) if needed
- Install frontend dependencies (`npm install`) if needed
- Create `.env` files for backend and frontend (from `.env.example` if missing)
- Generate the Laravel `APP_KEY` if missing
- Run database **migrations** and **seeders**
- Start:
  - Laravel dev server on **http://localhost:8000**
  - Vite dev server on **http://localhost:5173**

A separate **test database** (`app_test`) is also created automatically at container startup.

You do **not** need to run `php artisan serve`, `npm run dev`, or `php artisan migrate` manually – Docker and the entrypoint scripts handle this for you.

---

## 🧩 Application Features

### Backend (Laravel 12)

- **Authentication**
  - Laravel Sanctum
  - CSRF-secured SPA auth flow
- **Events**
  - Listing with search, sorting, and pagination
  - Public event details
- **Bookings**
  - Seat booking with capacity validation
  - User’s personal booking list
  - Booking cancellation (allowed before event start)
- **Admin**
  - Create / update / delete events
  - View event participants (paginated, searchable)
  - Export participants to CSV
- **Architecture**
  - Services, policies, form requests, resources
  - Clear separation of concerns

### Frontend (Vue 3 + TypeScript)

- Vue Router with navigation guards
- Pinia stores (auth, UI loader)
- Axios wrapper with:
  - global loading indicator
  - CSRF handling
- Screens:
  - Login & Registration
  - Event list with booking & admin modals
  - Event details with admin participant table + CSV export
  - User booking management page
- Styling:
  - Tailwind CSS
  - Clean, minimal component structure

---

## 🧪 Running Tests

Tests are executed inside the **backend** container.

From the project root:

```bash
docker compose exec backend php artisan test
```
