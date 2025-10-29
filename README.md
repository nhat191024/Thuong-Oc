# Thương Ốc - Restaurant Management System

A comprehensive restaurant management web application built with Laravel, Inertia.js, Vue 3, and Filament. Designed to streamline operations for "Thương Ốc" restaurant, featuring order management, inventory control, payment integration, and administrative tools.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-4-FDAE4B?style=flat&logo=filament&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0-8A2BE2?style=flat&logo=inertia&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-42b883?style=flat&logo=vue.js&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker&logoColor=white)

## 📋 Features

- **Order Management**: Streamlined order processing and tracking
- **Inventory Control**: Real-time inventory management
- **Payment Integration**: PayOS payment gateway integration
- **Media Management**: Spatie Media Library for image and file handling
- **User Permissions**: Role-based access control with Spatie Permission
- **Voucher System**: Discount vouchers and promotion management
- **Admin Panel**: Powerful Filament-based administration interface
- **Real-time Updates**: Laravel Reverb for WebSocket connections
- **Modern UI**: Built with Inertia.js (Laravel + Vue 3) and Tailwind CSS v4

## 🛠️ Tech Stack

### Backend

- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Authentication**: Laravel Fortify
- **Real-time**: Laravel Reverb
- **Cache/Queue**: Redis (Predis)

### Frontend

- **SPA Framework**: Inertia.js (Laravel + Vue 3)
- **Admin Panel**: Filament 4
- **CSS Framework**: Tailwind CSS 4
- **Build Tool**: Vite 7

### Key Packages

- **Spatie Media Library**: Media management
- **Spatie Permission**: Role & permission management
- **Spatie Sluggable**: Automatic slug generation
- **Spatie Tags**: Tagging system
- **Laravel Vouchers**: Voucher management
- **PayOS**: Payment gateway integration

### Development Tools

- **Testing**: Pest 4 (with Laravel plugin)
- **Code Style**: Laravel Pint
- **Debugging**: Laravel Debugbar, LaraDumps
- **IDE Support**: Laravel IDE Helper
- **Query Optimization**: Laravel Query Detector
- **Log Viewer**: Opcodesio Log Viewer

## 📦 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & npm/pnpm
- Docker & Docker Compose (for containerized deployment)
- Redis (for cache and queues)

## 🚀 Installation

### Method 1: Traditional Setup

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd Thuong-Oc
    ```

2. **Install dependencies**

    ```bash
    composer install
    npm install
    # or using pnpm
    pnpm install
    ```

3. **Environment configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Configure your `.env` file**
    - Database credentials
    - Redis connection
    - PayOS API credentials
    - Mail settings

5. **Run migrations and seeders**

    ```bash
    php artisan migrate --seed
    ```

6. **Build frontend assets**

    ```bash
    npm run build
    # or for development
    npm run dev
    ```

7. **Start the development server**
    ```bash
    php artisan serve
    ```

### Method 2: Docker Deployment

1. **Ensure Docker network exists**

    ```bash
    docker network create web-network
    ```

2. **Build and start containers**

    ```bash
    docker-compose up -d
    ```

3. **Access the container and setup**

    ```bash
    docker exec -it thuong_oc bash
    composer install
    php artisan key:generate
    php artisan migrate --seed
    npm install && npm run build
    ```

4. **Access the application**
    - Application: `http://localhost:10010`

## 🏃 Development

### Quick Start with Composer Script

```bash
composer run dev
```

This command will concurrently run:

- Laravel development server
- Queue worker
- Vite development server

### Individual Commands

```bash
# Start Laravel server
php artisan serve

# Start queue worker
php artisan queue:listen --tries=1

# Start Vite dev server
npm run dev

# Start Reverb WebSocket server
php artisan reverb:start
```

## 🧪 Testing

Run the test suite using Pest:

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with coverage
php artisan test --coverage
```

## 📝 Code Style

Format your code with Laravel Pint:

```bash
# Fix code style issues
vendor/bin/pint

# Check without fixing
vendor/bin/pint --test
```

## 🔑 Default Credentials

After running seeders, you can login with:

- **Email**: (check your seeder files)
- **Password**: (check your seeder files)

⚠️ **Remember to change default credentials in production!**

## 📱 Admin Panel

Access the Filament admin panel at:

```
http://localhost:10010/admin
```

## 🔐 Security

- Two-factor authentication supported via Laravel Fortify
- Role-based access control with Spatie Permission
- CSRF protection enabled
- XSS protection via Laravel's built-in features

## 🌐 Environment Variables

Key environment variables to configure:

```env
APP_NAME="Thương Ốc"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thuong_oc
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

PAYOS_CLIENT_ID=your_client_id
PAYOS_API_KEY=your_api_key
PAYOS_CHECKSUM_KEY=your_checksum_key

REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
```

## 📚 Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue.js Documentation](https://vuejs.org/)
- [Filament Documentation](https://filamentphp.com/docs)
- [Flux UI Documentation](https://fluxui.dev)
- [PayOS Documentation](https://payos.vn/docs)

## 📄 License

This project is licensed under the MIT License.

## 👥 Support

For support and questions:

- Create an issue in the repository
- Contact the development team

## 🙏 Acknowledgments

- Laravel Team for the amazing framework
- Inertia.js & Vue Team for modern SPA experience
- Filament Team for the admin panel
- Spatie for their excellent Laravel packages
- All contributors and package maintainers

---

**Built with ❤️ for Thương Ốc Restaurant**
