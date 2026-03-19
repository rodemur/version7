- **Backend:** Laravel 13.1.1, PHP 8.4.17
- **Frontend:** Vue 3 (собирается в `backend/public/`)
- **API:** Laravel Sanctum (токенная аутентификация)
- **Хостинг:** Timeweb (виртуальный хостинг)
- **База данных:** MySQL

## 🚀 Развёртывание на хостинге (Timeweb)

### 1. Требования хостинга

| Компонент | Версия | Примечание |
|-----------|--------|------------|
| PHP | 8.4+ | CLI и Web должны совпадать |
| Composer | 2.2+ | Для установки зависимостей |
| MySQL | 5.7+ или 8.0+ | База данных |
| mod_rewrite | Включён | Для .htaccess |

### 2. Настройка PHP на хостинге

bash
# Добавить в ~/.bash_profile для PHP 8.4 в CLI
export PATH=/opt/php8.4/bin:$PATH
export PATH=/home/c/cr19840/bin:$PATH

### 3 Установка Composer на хостинге

# Создать папку для бинарников
mkdir -p /home/c/cr19840/bin

# Установить Composer
php /opt/php8.4/bin/php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php /opt/php8.4/bin/php composer-setup.php --install-dir=/home/c/cr19840/bin --filename=composer
rm composer-setup.php

# Добавить в PATH
echo 'export PATH=/home/c/cr19840/bin:$PATH' >> ~/.bash_profile
source ~/.bash_profile

### 4. Развёртывание кода

# Клонировать репозиторий
cd ~/rezerv
git clone https://github.com/rodemur/version7.git backend
cd backend

# Установить зависимости
composer install --optimize-autoloader

# Создать .env из шаблона
cp .env.example .env

# Сгенерировать ключ приложения
php artisan key:generate

### 5. Настройка .env

APP_NAME=Rezerv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://budget.romantsov.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cr19840_rezerv
DB_USERNAME=cr19840_rezerv_user
DB_PASSWORD=***

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
BROADCAST_DRIVER=log
FILESYSTEM_DISK=local

### 6. Установка API-роутинга (Laravel 11+)

# Laravel 11+ не создаёт api.php по умолчанию
php artisan install:api

# Запустить миграции
php artisan migrate --force

# Опубликовать конфиг Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"


### 7. Настройка модели User

// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}


### 8. Настройка маршрутов
# routes/api.php:

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn($request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    // ... другие API маршруты
});

# routes/web.php:
// Заглушка для middleware auth (чтобы не было ошибки "Route [login] not defined")
Route::get('/login', fn() => response()->json(['message' => 'Use /api/login'], 401))->name('login');

Route::get('/', fn() => view('welcome'));

### 9. Настройка .htaccess (безопасность + маршрутизация)
<IfModule mod_rewrite.c>
    Options -MultiViews -Indexes
    RewriteEngine On
    
    # Блокировка доступа к системным файлам
    RewriteRule ^(\.env|\.git|composer\.json) - [F,L]
    RewriteRule ^(app|bootstrap|config|database|storage|vendor)/ - [F,L]
    
    # Защитные заголовки
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    
    # Маршрутизация Laravel
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

### 10. Символическая ссылка для public_html
# На Timeweb веб-сервер смотрит в public_html
ln -s /home/c/cr19840/rezerv/backend/public /home/c/cr19840/rezerv/backend/public_html

### 11. Права на папки
chmod -R 775 storage bootstrap/cache

### 12. Очистка кэша

│  📋 РЕЗЕРВ — СВОДКА ПО РАЗВЁРТЫВАНИЮ                           │
├─────────────────────────────────────────────────────────────────┤
│  ✅ Laravel 13.1.1 + PHP 8.4.17 + Composer 2.9.5               │
│  ✅ Sanctum API (токенная аутентификация)                       │
│  ✅ Хостинг: Timeweb (виртуальный)                              │
│  ✅ База данных: MySQL                                          │
│  ✅ Сессии/Кэш: файловые (не БД)                                │
│  ✅ API маршруты: /api/login, /api/register, /api/user, ...    │
│  ✅ Безопасность: .htaccess блокирует .env, /app, /config      │
│  ✅ Документация: DEPLOYMENT.md в корне проекта                 │
├─────────────────────────────────────────────────────────────────┤
│  🔧 Критичные файлы:                                            │
│    - routes/api.php (API маршруты)                              │
│    - routes/web.php (заглушка login для middleware)             │
│    - app/Models/User.php (HasApiTokens trait)                   │
│    - public/.htaccess (безопасность + маршрутизация)            │
│    - bootstrap/app.php (подключение API роутинга)               │
│    - .env (переменные окружения, НЕ в git)                      │
├─────────────────────────────────────────────────────────────────┤
│  🌐 Сайт: https://budget.romantsov.com                          │
│  📦 GitHub: https://github.com/rodemur/version7      
