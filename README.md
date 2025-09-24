# Laravel + Vite + TailwindCSS + SCSS

This project is built with **Laravel** as the backend, **Vite** for asset bundling, styled with **Tailwind CSS**, and includes **SCSS** support.

---

## 🚀 Installation & Setup

### 1. Clone the repository
```bash
git clone <repo-url>
cd <project-folder>
````

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure `.env`

Copy `.env.example` and set up your database and environment variables:

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Start development servers

In two terminals:

```bash
# Backend
php artisan serve

# Frontend
npm run dev
```

### 5.2. Start development servers (OSP)

```bash
osp use PHP-8.3
osp add Node-24.8.0
osp add MySQL-8.2

cd home/codebase
composer run dev
```

Access the project:

* Laravel API → [http://127.0.0.1:8000](http://127.0.0.1:8000)
* Vite (Frontend) → [http://127.0.0.1:5173](http://127.0.0.1:5173)

---

## 📦 Production Build

```bash
npm run build
php artisan optimize
```

---

## 📂 Project Structure

* `resources/scss/app.scss` – main stylesheet, includes Tailwind and custom SCSS.
* `tailwind.config.cjs` – Tailwind configuration.
* `postcss.config.cjs` – PostCSS plugins (Tailwind + Autoprefixer).
* `vite.config.js` – Vite configuration.

---

## ⚡ Useful Commands

```bash
php artisan serve       # Run Laravel backend
npm run dev             # Run Vite in dev mode
npm run build           # Build for production
npm run preview         # Preview production build
```

---

## 🛠 Requirements

* PHP >= 8.1
* Composer
* Node.js >= 18
* NPM or Yarn

```

---

Do you want me to also add **Blade layout examples (header/footer, Tailwind classes, Vite integration)** to the README so it looks like a proper starter kit?
```
