# SaaS Backend System - Laravel 12

A minimal, secure, and extensible SaaS backend system built with Laravel 12 that powers a Super Admin Dashboard (Blade templates) and provides a REST API for a Next.js frontend.

## Features

- **Super Admin Dashboard**: Traditional Laravel Blade views with TailwindCSS for managing events, hotels, and bookings
- **REST API**: Secure API endpoints protected by Laravel Sanctum for Next.js frontend consumption
- **Authentication**: 
  - Laravel Breeze with Blade for super admin login (session-based)
  - Laravel Sanctum for stateless token authentication (SPA tokens)
- **File Uploads**: Image uploads for event logos/banners and hotel images
- **Real-time Updates**: Livewire v3 support for dynamic form updates

## Tech Stack

- **Laravel 12** with PHP 8.3+
- **Laravel Breeze** (Blade stack)
- **Laravel Sanctum** for API authentication
- **Livewire v3** for real-time components
- **MySQL/PostgreSQL** database
- **TailwindCSS** for styling

## Installation

1. **Clone the repository and install dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Set up your database in `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

5. **Create storage link for file uploads:**
   ```bash
   php artisan storage:link
   ```

6. **Build assets:**
   ```bash
   npm run build
   # or for development:
   npm run dev
   ```

7. **Create the first super admin user:**

   **Option 1: Using Seeder (Recommended)**
   ```bash
   php artisan db:seed --class=AdminSeeder
   ```
   This creates a default admin user:
   - Email: `admin@example.com`
   - Password: `password`
   ⚠️ **Change the password after first login!**

   **Option 2: Using Artisan Command (Interactive)**
   ```bash
   php artisan admin:create-user
   ```
   This will prompt you to enter name, email, and password interactively.

   **Option 3: Using Registration Page**
   Visit `/register` in your browser and create an account (if registration is enabled).

   **Option 4: Using Tinker**
   ```bash
   php artisan tinker
   ```
   Then run:
   ```php
   User::create([
       'name' => 'Super Admin',
       'email' => 'admin@example.com',
       'password' => Hash::make('your-secure-password'),
       'email_verified_at' => now(),
   ]);
   ```

8. **Start the development server:**
   ```bash
   php artisan serve
   ```

9. **Access the dashboard:**
   - Visit `http://localhost:8000/login`
   - Login with your super admin credentials
   - Access the dashboard at `http://localhost:8000/dashboard`

## Deploying in a subdirectory (ex: `/admin/public`)

If your Laravel `public/` directory is not at the domain root (for example the app is accessed at `https://example.com/admin/public`), set:

```env
APP_URL=https://example.com/admin/public
ASSET_URL=https://example.com/admin/public
APP_BASE_PATH=/admin/public
```

Then clear caches after deploy:

```bash
php artisan optimize:clear
```

## Database Structure

### Events
- `id`, `name`, `slug` (auto-generated), `logo_path`, `banner_path`, `description`, `menu_links` (JSON), `status`, `created_at`, `updated_at`

### Hotels
- `id`, `event_id`, `name`, `location`, `description`, `price_per_night`, `availability`, `image_path`, `status`, `created_at`, `updated_at`

### Bookings
- `id`, `event_id`, `hotel_id`, `guest_name`, `guest_email`, `checkin_date`, `checkout_date`, `guests_count`, `status`, `created_at`, `updated_at`

## Super Admin Dashboard Routes

- `/dashboard` - Main dashboard
- `/admin/events` - List all events
- `/admin/events/create` - Create new event
- `/admin/events/{id}/edit` - Edit event
- `/admin/events/{id}/hotels` - Manage hotels for an event
- `/admin/bookings` - View all bookings

## REST API Endpoints

All API routes are protected by `auth:sanctum` middleware.

### Events
- `GET /api/events` - List all published events
- `GET /api/events/{event}` - Get specific event details

### Hotels
- `GET /api/events/{event}/hotels` - List hotels for an event
- `GET /api/hotels/{hotel}` - Get specific hotel details

### Bookings
- `GET /api/bookings` - List user's bookings
- `POST /api/bookings` - Create new booking
- `GET /api/bookings/{booking}` - Get specific booking
- `PATCH /api/bookings/{booking}/status` - Update booking status

## API Authentication

To authenticate API requests from your Next.js frontend:

1. **Login endpoint** (use Breeze's login):
   ```bash
   POST /login
   ```

2. **Get API token** (after login):
   ```bash
   POST /api/sanctum/token
   ```

3. **Include token in requests**:
   ```javascript
   Authorization: Bearer {token}
   ```

## File Storage

Uploaded images are stored in `storage/app/public`:
- Event logos: `storage/app/public/events/logos/`
- Event banners: `storage/app/public/events/banners/`
- Hotel images: `storage/app/public/hotels/`

Make sure to run `php artisan storage:link` to create the symbolic link.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
