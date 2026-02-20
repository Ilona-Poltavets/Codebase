# Codebase

Codebase is a team collaboration platform for project work: tickets, Trello-style boards, files, wiki, activity feed, and time tracking.

## Why this project exists

`Codebase` helps teams run project delivery in one place:
- plan and move tasks across board statuses;
- assign tasks to people;
- keep project files and documentation (Wiki);
- log time on tasks;
- review project activity in one timeline.

It is useful when a team wants one consistent workflow instead of multiple disconnected tools.

## What is included

- `Dashboard`: project overview, quick actions, board entry points.
- `Projects`: project list and project workspace.
- `Custom Trello Board`: board modes (Project / Developer / My Board).
- `Tickets`: create, update, comment, and log time.
- `Files`: project file area.
- `Wiki`: project documentation with versions.
- `Activity`: event feed.
- `Time`: time reports.

## Roles and access

Primary roles: `admin`, `owner`, `manager`, `developer`, `member`.

Example access rules:
- `Users` section is available only to `admin`, `owner`, `manager`.
- Other access depends on company and project membership.

## End-user guide (regular user)

1. Sign in with your assigned credentials.
2. Open `Dashboard`.
3. Go to `Projects` and select a project.
4. Work from the board:
   - `Project View` for status columns;
   - `Developer View` for assignee columns;
   - `My Board` for personal queue.
5. Open a `Ticket` and:
   - update status/fields;
   - add comments;
   - add manual time logs.
6. Use `Wiki` and `Files` as project knowledge sources.

## Quick start (local)

### 1) Install

```bash
composer install
npm install
```

### 2) Configure

```bash
cp .env.example .env
php artisan key:generate
```

Set your database credentials in `.env`.

### 3) Migrate and seed

```bash
php artisan migrate
php artisan db:seed
```

### 4) Run

In two terminals:

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

Or with compiled assets:

```bash
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

## Demo accounts (if demo seeder is used)

- `admin@example.com` / `admin123`
- `owner@mercuria.local` / `password`
- `manager@mercuria.local` / `password`
- `dev1@mercuria.local` / `password`
- `dev2@mercuria.local` / `password`
- `dev3@mercuria.local` / `password`
- `member@mercuria.local` / `password`

## Useful commands

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
npm run build
```

## Public access via ngrok

1. Start backend:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

2. Start ngrok:

```bash
ngrok http 8000
```

3. Set `APP_URL` in `.env` to your `https://...ngrok-free.app` URL and run:

```bash
php artisan optimize:clear
```
