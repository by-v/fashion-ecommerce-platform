# BY.V — Fashion E-Commerce Platform

[![PHP](https://img.shields.io/badge/PHP-^8.2-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-^12.0-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-v3-FBBF24?logo=filament)](https://filamentphp.com)
[![Midtrans](https://img.shields.io/badge/Midtrans-^2.6-blue)](https://midtrans.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.4-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)

A fashion store built with **Laravel**, featuring **Midtrans (QRIS & Virtual Account)** integrated checkout, automatic email notifications via queue, and a complete **Filament** admin panel.

## Screenshots

| Home | Catalog / Shop | Checkout |
|------|----------------|----------|
| [Screenshot] | [Screenshot] | [Screenshot] |

| Order Confirmation | Admin Dashboard | Admin Orders |
|--------------------|-----------------|--------------|
| [Screenshot] | [Screenshot] | [Screenshot] |

## 🛠️ Tech Stack

- **Backend:** PHP 8.2+, Laravel 12, Laravel Queue (database), Tinker
- **Frontend:** Blade, Tailwind CSS, Alpine.js, Axios, Vite 7
- **Admin Panel:** Laravel Filament v3
- **Payment Gateway:** Midtrans Snap (midtrans/midtrans-php v2.6)
- **Database:** MySQL
- **Auth:** Laravel Breeze
- **Quality:** PHPUnit, Laravel Pint, Laravel Pail, Laravel Sail

## ✨ Features

### For Visitors
- Homepage with a featured product hero
- Product catalog with pagination and filters: **search, categories (multi), sizes (multi), featured products, and stock availability**
- Complete product detail page with **size (variant)** selection
- **Shopping cart** — add/update quantity/remove items, automatic stock validation
- **Checkout** from the cart (multi-product) or **Buy Now** (buy-now)
- Payment via **Midtrans Snap (QRIS & Virtual Account)**
- **Order confirmation** page with a 10-minute payment deadline
- **Track order** via order number (no login required, rate-limited)
- Order statuses: `pending → paid → processed → shipped → completed` (or `cancelled`)
- Profile: update data, change password, delete account

### ⚙️ For Admin (Filament `/admin`)
- **Dashboard** with statistics: total revenue, order count, pending orders, low-stock alerts, revenue trend chart, and latest orders table
- **Product management** with size variants (S/M/L/XL) — parent stock auto-synced from the total of its variants
- **Category, shipping method, and user management** (admin/customer roles)
- **Order management**:
  - Update status following the allowed transition flow (whitelist)
  - Fill in the **tracking number** when an order is shipped (triggers automatic shipping email)
  - Order cancellation **auto-restores stock**
  - Bulk delete (pending orders get their stock restored first)

### 📧 Automatic Email Notifications
| Event | Email | Recipient |
|---|---|---|
| Order created | Order Pending | Buyer |
| Payment successful | Order Paid | Buyer |
| Payment successful | Admin: New Order | Admin (`ADMIN_EMAIL`) |
| Order shipped | Order Shipped | Buyer |

## 🏗️ Architecture & Design Patterns

- **Business logic in Controllers** — inline `$request->validate()`, query scoping with `where('user_id', Auth::id())`, no service/action layer.
- **Events & Listeners** — `OrderCreated` and `OrderPaid` (registered in `AppServiceProvider`); queueable listeners (`ShouldQueue`, `tries: 3`, `backoff: 60s`).
- **Observer** — `OrderObserver` sets `shipped_at` and sends `OrderShippedMail` when the status changes to `shipped` (dispatch `afterCommit`).
- **Queue** — emails are sent as jobs (listeners & `dispatch(closure)->afterCommit()`); default driver `database`.
- **Atomicity** — order creation, stock deduction, and cart cleanup are wrapped in `DB::transaction`, with `lockForUpdate()` (pessimistic locking) to prevent stock race conditions.
- **Anti double-submit** — `Cache::lock` on checkout and a one-time submission token in the session.
- **Secure webhook** — `signature_key` validation (SHA512), `gross_amount` validation, and idempotency check (rejects duplicate notifications / non-`pending` status).
- **Scheduled tasks** — `orders:cancel-expired` (every 5 minutes) and `orders:complete-delivered` (hourly), both `withoutOverlapping`.
- **Stock management** — if a product has variants, the parent stock is auto-computed from the `SUM(stock)` of its variants (`syncProductStock()`).

## 🚀 Installation & Setup

### Requirements
- PHP >= 8.2 (with extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath)
- Composer 2.x
- Node.js >= 20 (for Vite 7) & npm
- MySQL 8+

### 1. Clone & install dependencies

```bash
git clone <repository-url>
cd fashion-ecommerce-platform

composer install
npm install
npm run build
```

### 2. Configure `.env`

```bash
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate
```

### 3. Database

Update `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env` according to your MySQL setup, then:

```bash
php artisan migrate
```

### 4. Setup Midtrans (Sandbox)

1. Register / log in to the [midtrans.com](https://midtrans.com) dashboard → *Settings > Access Keys* menu
2. Use the sandbox **Server Key** and **Client Key**, then set them in `.env`:

   ```
   MIDTRANS_SERVER_KEY=SB-Mid-server-...
   MIDTRANS_CLIENT_KEY=SB-Mid-client-...
   MIDTRANS_IS_PRODUCTION=false
   ```

3. **Webhook URL**: since the app is still local, expose your server with ngrok:
   ```bash
   ngrok http 8000
   ```
4. Copy your public ngrok URL (e.g. `https://xxxx.ngrok.io`), then register it as the **Payment Notification URL** in the Midtrans dashboard:
   ```
   https://xxxx.ngrok.io/payment/notification
   ```
   > The webhook endpoint does not require authentication and CSRF is specifically excluded for this route.

5. When ready for production, switch to the production server key and set `MIDTRANS_IS_PRODUCTION=true`.

### 5. Setup Gmail SMTP (Email Notifications)

1. Enable **2-Factor Authentication** on your Gmail account.
2. Go to [Google App Passwords](https://myaccount.google.com/apppasswords) → create an app-specific password for "Mail" / "Other".
3. Set in `.env`:

   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=email.anda@gmail.com
   MAIL_PASSWORD=xxxx xxxx xxxx xxxx   # App Password (16 characters)
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="BY.V <email.anda@gmail.com>"
   MAIL_FROM_NAME="BY.V"
   ```

4. Also fill in the admin email for "new order" notifications:
   ```
   ADMIN_EMAIL=admin@domain-anda.com
   ```

### 6. Create an admin account (Filament)

```bash
php artisan make:filament-user
```

> The command above creates a user with the default `role` of `customer`. To access the `/admin` panel, set the role to `admin`:

```bash
php artisan tinker --execute="\App\Models\User::where('email','email.anda@gmail.com')->update(['role' => 'admin']);"
```

### 7. Storage link & run

```bash
php artisan storage:link
composer run dev        # runs server, queue, logs, and vite all at once
```

Open `http://127.0.0.1:8000`, admin panel at `http://127.0.0.1:8000/admin`.

## 🔑 .env Configuration

| Variable | Description |
|---|---|
| `APP_NAME` | Brand name (default: `BY.V`) |
| `APP_URL` | Application URL |
| `APP_ENV`, `APP_DEBUG` | Environment & debug mode |
| `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database connection (MySQL) |
| `SESSION_DRIVER=database` | Database-backed sessions |
| `QUEUE_CONNECTION=database` | Database-backed queue (for emails) |
| `CACHE_STORE=database` | Database-backed cache |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | SMTP mailer |
| `MIDTRANS_SERVER_KEY` | Server Key (sandbox `SB-Mid-server-...` / production) |
| `MIDTRANS_CLIENT_KEY` | Client Key |
| `MIDTRANS_IS_PRODUCTION` | `false` = sandbox, `true` = production |
| `ADMIN_EMAIL` | Email receiving new order notifications (admin) |

> **Note:** `MIDTRANS_*` and `ADMIN_EMAIL` are not present in `.env.example` — add them manually to `.env`.

## ▶️ Running the Application

For development, run all services at once:

```bash
composer run dev
```

Or one by one:

| Service | Command | Description |
|---|---|---|
| Web server | `php artisan serve` | App at `http://127.0.0.1:8000` |
| Queue worker | `php artisan queue:work` | Processes emails & background jobs |
| Scheduler | `php artisan schedule:work` | Runs `orders:cancel-expired` & `orders:complete-delivered` |
| Vite (dev) | `npm run dev` | Hot reload assets |
| Webhook tunnel | `ngrok http 8000` | Public URL for Midtrans notifications |

For production, it is recommended to run the worker/scheduler as daemons:

```bash
php artisan queue:work --tries=3 --timeout=60
crontab -e   # then add:
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Testing & Code Style

```bash
composer test          # php artisan test
php artisan test --filter=TestName
./vendor/bin/pint      # format code
```

## 📁 Key Folder Structure

```
app/
├── Console/Commands/          # orders:cancel-expired, orders:complete-delivered
├── Events/                    # OrderCreated, OrderPaid
├── Filament/
│   ├── Resources/             # User, Category, Product, ShippingMethod, Order (+ RelationManagers)
│   └── Widgets/               # StatsOverview, RevenueTrendChart, LatestOrdersTable
├── Http/Controllers/          # Business logic in controllers (Home, Shop, Cart, Checkout, Order, Payment, TrackOrder, Profile)
├── Listeners/                 # SendOrderPendingEmail, SendOrderPaidEmail (queueable)
├── Mail/                      # OrderPendingMail, OrderPaidMail, AdminNewOrderMail, OrderShippedMail
├── Models/                    # Eloquent models + relationships
├── Observers/                 # OrderObserver (shipped_at + sends email)
└── Providers/                 # AppServiceProvider (events/observer), Filament/AdminPanelProvider

resources/views/
├── emails/                    # Email templates (order-pending, order-paid, order-shipped, admin-new-order)
├── shop.blade.php             # Catalog + filters
├── checkout.blade.php         # Checkout
├── order-confirmation.blade.php
├── track-order.blade.php      # Track order
└── layouts/                   # Modern shop layout, dashboard layout

routes/
├── web.php                    # All application routes
└── console.php                # Scheduler (cancel-expired, complete-delivered)

database/
├── migrations/                # 16 migrations (users → orders → payments, etc.)
└── seeders/                   # Category, Product, ShippingMethod
```

## 👤 Credit

Made with ☕ by **[V]**
   
> *"Built as a portfolio project to demonstrate full-stack Laravel development with real-world e-commerce features.