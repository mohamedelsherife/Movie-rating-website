# Movie Rating Website

A web application built with **Laravel 13** for managing movies and TV series. It currently includes an admin panel where an administrator can create, edit, and delete movies and series, along with their directors, genres, and poster images.

## Features

- Admin authentication using a dedicated `admin` guard, with "Remember me" support.
- Dashboard showing content statistics.
- **Movies** management: title, director, release year, description, genres, and poster.
- **Series** management: title, director, start and end year (empty = still running), number of seasons and episodes, description, genres, and poster.
- Pick an existing director or type a new name, and it is created automatically.
- Pick existing genres or add new ones directly from the same form.
- Poster uploads (jpg, jpeg, png, webp up to 10 MB). Old posters are deleted automatically when a record is updated or removed.
- Seeders with sample directors, genres, movies, and series.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Database | MySQL |
| Frontend | Blade components, Tailwind CSS 4, Vite |
| Testing | Pest |

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- MySQL

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/mohamedelsherife/Movie-rating-website.git
cd Movie-rating-website

# 2. Install dependencies
composer install
npm install

# 3. Set up the environment file
cp .env.example .env
php artisan key:generate
```

4. Create a MySQL database named `movie_rating_website`, then update the connection settings in `.env` if needed:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=movie_rating_website
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 5. Run the migrations and seed sample data
php artisan migrate --seed

# 6. Link the storage folder so poster images can be served
php artisan storage:link

# 7. Start the app (server + Vite + queue together)
composer run dev
```

Then open `http://localhost:8000` in your browser.

## Admin Account

A default admin account is created when the seeders run:

| Field | Value |
|---|---|
| Email | `madaramy@gmail.com` |
| Password | `1234mada` |

> ⚠️ You should change these credentials. Add the following to your `.env` file, then run the command below:

```env
ADMIN_NAME=Administrator
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=your-strong-password
```

```bash
php artisan db:seed --class=AdminSeeder
```

## Routes

| Method | URI | Description |
|---|---|---|
| GET | `/` | Redirects to `/admin` |
| GET / POST | `/admin/login` | Admin login |
| POST | `/admin/logout` | Logout |
| GET | `/admin` | Dashboard |
| GET, POST, PUT, DELETE | `/admin/movies` | Manage movies |
| GET, POST, PUT, DELETE | `/admin/series` | Manage series |

Every `/admin` route except the login page requires a logged-in admin.

## Database Schema

| Table | Description |
|---|---|
| `admins` | Admin accounts |
| `directors` | Directors (unique name) |
| `genres` | Genres (unique name) |
| `movies` | Movies (belong to a director) |
| `series` | Series (belong to a director) |
| `movie_genres` | Movie ↔ genre pivot (many-to-many) |
| `series_genres` | Series ↔ genre pivot (many-to-many) |

## Project Structure

```
app/
├── Http/Controllers/
│   ├── AdminController.php    # Login, logout, and dashboard
│   ├── MovieController.php    # Movie CRUD
│   └── SeriesController.php   # Series CRUD
└── Models/                    # Admin, Director, Genre, Movie, Series, User
database/
├── migrations/
└── seeders/                   # Sample data + admin account
resources/views/
├── layouts/app.blade.php      # Main layout (navbar + footer)
├── admin/                     # Admin panel pages
└── components/                # Reusable Blade components
routes/web.php
```

## Useful Commands

```bash
composer run dev                   # Start the development environment
composer run test                  # Run the test suite
npm run build                      # Build frontend assets for production
php artisan migrate:fresh --seed   # Rebuild the database from scratch
./vendor/bin/pint                  # Format the code
```

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
