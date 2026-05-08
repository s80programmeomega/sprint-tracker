# Sprint Tracker

A Jira-lite project and sprint management API built with **Laravel 10** and **Vue 3**.

> Built as a training project to learn the Laravel + Vue 3 stack.

---

## Stack

- **Backend**: Laravel 10, PHP 8.1
- **Auth**: Laravel Sanctum (API tokens) + Laravel Fortify (registration/login)
- **Roles & Permissions**: Spatie Laravel Permission
- **Frontend**: Vue 3 (Composition API), Pinia, Vue Router 4, Axios
- **Database**: MySQL / PostgreSQL

---

## Features

- Projects with members and roles (admin, member, viewer)
- Sprints per project with start/end dates and status tracking
- Tasks on a Kanban board (To Do → In Progress → Done)
- Task assignment, priorities, and due dates
- Activity log tracking every change on a task
- Role-based access control across all endpoints

---

## Data Model

```
User
 └── belongs to many Projects (role: admin | member | viewer)

Project
 └── has many Sprints
 └── has many Tasks (through Sprint)

Sprint
 └── belongs to Project
 └── has many Tasks

Task
 └── belongs to Sprint
 └── assigned to User
 └── has many ActivityLogs
```

---

## Local Setup

```bash
git clone git@github.com:s80programmeomega/sprint-tracker.git
cd sprint-tracker

composer install
cp .env.example .env
php artisan key:generate

# configure DB in .env, then:
php artisan migrate --seed
php artisan serve
```

---

## Project Structure

```
app/
├── Enums/          # TaskStatus, TaskPriority (PHP 8.1 backed enums)
├── Models/         # User, Project, Sprint, Task, ActivityLog
├── Http/
│   ├── Controllers/Api/   # Resource controllers (Phase 3+)
│   ├── Requests/          # Form request validation (Phase 3+)
│   └── Resources/         # API response transformers (Phase 3+)
└── Services/       # Business logic layer (Phase 5+)
```

---

## Training Phases

| Phase | Topic | Status |
|-------|-------|--------|
| 1 | JS Foundations (async/await, ES modules) | done |
| 2 | Laravel setup, migrations, models, enums | done |
| 3 | Auth (Sanctum + Fortify), roles (Spatie) | done |
| 4 | API controllers, Form Requests, Resources | done |
| 5 | Service layer | done |
| 6 | Vue 3 foundations | done |
| 7 | Vue Router + Pinia | done |
| 8 | Advanced UI — Kanban, modals, filters | pending |
