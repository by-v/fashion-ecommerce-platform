# AGENTS.md

## Overview
Laravel 12 e-commerce application (fashion template) with Filament v3 admin panel, Blade/Tailwind frontend, and Midtrans payment integration.

---

## Commands & Toolchain

### Development
- **Start all services** (server, queue, logs, vite concurrently):
  ```bash
  composer run dev
  ``` 
- **Single services**:
  - Web server: `php artisan serve`
  - Queue worker: `php artisan queue:listen --tries=1 --timeout=0`
  - Vite dev server: `npm run dev`
  - Asset build: `npm run build`

### Database & Migrations
- Run migrations: `php artisan migrate`
- Seed database: `php artisan db:seed`
- Fresh migration + seed: `php artisan migrate:fresh --seed`

### Testing & Code Style
- **Run all tests**:
  ```bash
  composer test
  # or
  php artisan test
  ```
- **Run a single test file**:
  ```bash
  php artisan test tests/Feature/ExampleTest.php
  ```
- **Run a specific test method**:
  ```bash
  php artisan test --filter=test_example
  ```
- **Code formatting (Laravel Pint)**:
  ```bash
  ./vendor/bin/pint
  ```

---

## Architecture & Conventions

### Admin Panel (Filament v3)
- Path: `/admin`
- Auth guard: `admin` guard configured in `config/auth.php` using standard `User` model.
- Access control: `User::canAccessPanel()` requires `role === 'admin'`.
- Upgrading Filament assets after package updates:
  ```bash
  php artisan filament:upgrade
  ```

### Payment Integration
- Provider: Midtrans Snap (`midtrans/midtrans-php`).
- Webhook endpoint: `POST /payment/notification` (exempt from auth middleware).

### Scheduled & Background Tasks
- `CancelExpiredOrders`: `app/Console/Commands/CancelExpiredOrders.php`
- `CompleteDeliveredOrders`: `app/Console/Commands/CompleteDeliveredOrders.php`

### Testing Quirks
- SQLite in-memory (`:memory:`) is configured in `phpunit.xml` with `sync` queue and `array` cache/session drivers.

---

## Coding Conventions
- **Business Logic Pattern**: Placed directly inside HTTP Controllers (e.g. `CheckoutController@store`, `CartController@add`, `PaymentController@notification`). Services and Actions patterns are **Not used in this project**.
- **Validation**: Performed inline within Controllers using `$request->validate([...])`. Form Request classes are reserved for auth/profile endpoints (`LoginRequest`, `ProfileUpdateRequest`).
- **Authorization**: Checked manually in Controllers via Eloquent query scoping (`where('user_id', Auth::id())`). Admin access is gated via `User::canAccessPanel()` for the `admin` guard. Policies and Gates are **Not used in this project**.
- **Naming Conventions**:
  - Controllers: PascalCase with `Controller` suffix (`CheckoutController`, `ShopController`).
  - Models: Singular PascalCase (`Product`, `Order`).
  - Tables & Foreign Keys: Plural snake_case (`shipping_methods`, `shipping_method_id`).
  - Routes: Named routes using dot notation (`checkout.cart`, `product.show`).

---

## Folder Structure
```
app/
├── Console/
│   └── Commands/          # Custom Artisan commands (CancelExpiredOrders, CompleteDeliveredOrders)
├── Events/                # Domain events (OrderCreated, OrderPaid)
├── Filament/              # Filament v3 admin panel configuration
│   └── Resources/         # Admin Resources and RelationManagers
├── Http/
│   ├── Controllers/       # Controllers handling web requests and domain logic
│   │   └── Auth/          # Authentication controllers (Laravel Breeze)
│   └── Requests/          # Request validation (Auth/Profile only)
├── Listeners/             # Event listeners (SendOrderPendingEmail, SendOrderPaidEmail)
├── Mail/                  # Mailable email definitions
├── Models/                # Eloquent models with relationships, casts, and model hooks
├── Observers/             # Model observers (OrderObserver)
└── Providers/             # Service providers (AdminPanelProvider, AppServiceProvider)
```
- **Custom / Non-default folders in `app/`**: `Filament/`, `Events/`, `Listeners/`, `Observers/`.
- `app/Services/` and `app/Actions/`: **Not used in this project**.

---

## Key Models & Relationships
- **`Order`**:
  - Relationships: `belongsTo(User::class)`, `belongsTo(ShippingMethod::class)`, `hasMany(OrderItem::class)`, `hasOne(Payment::class)`.
  - Important fields & statuses: `status` (`pending`, `paid`, `processed`, `shipped`, `completed`, `cancelled`), `order_number`, `payment_deadline`, `shipped_at`, `tracking_number`, `subtotal`, `shipping_cost`, `total`.
- **`Product`**:
  - Relationships: `belongsTo(Category::class)`, `hasMany(ProductVariant::class)`.
  - Important fields: `stock` (synced automatically from variants if variants exist), `price`, `is_featured`, `slug`.
- **`ProductVariant`**:
  - Relationships: `belongsTo(Product::class)`.
  - Important fields: `size`, `stock`. Automatically triggers `syncProductStock()` on model `saved` and `deleted` events to update parent `Product.stock`.
- **`Cart` & `CartItem`**:
  - Relationships: `Cart` `belongsTo(User::class)`, `hasMany(CartItem::class)`. `CartItem` `belongsTo(Product::class)`, `belongsTo(ProductVariant::class)`.
- **`Payment`**:
  - Relationships: `belongsTo(Order::class)`.
  - Important fields: `payment_gateway_id`, `method`, `status` (`success`), `paid_at`.
- **`ShippingMethod`**:
  - Important fields: `name`, `cost`, `estimated_days`, `is_active`.

---

## Business Logic & Domain Rules
- **Order Creation (`CheckoutController@store`)**:
  - Validates recipient details and stock availability before placing orders.
  - Generates 10-character uppercase alphanumeric `order_number`.
  - Calculates subtotal and total including shipping.
  - Sets payment deadline to 10 minutes from creation (`now()->addMinutes(10)`).
  - Decrements variant/product stock inside `DB::transaction`.
  - Clears cart when checking out from cart.
  - Dispatches `OrderCreated` event.
  - Prevents duplicate order placement within 10 seconds.
- **Payment & Expiry Flow (`PaymentController`, `OrderController`, `CancelExpiredOrders`)**:
  - Midtrans Snap webhook (`POST /payment/notification`) updates status to `paid` when payment succeeds (`capture`/`settlement` + `accept`).
  - When payment expires or fails, stock is restored to `ProductVariant` or `Product`.
  - Accessing `order.confirmation` triggers `expireIfNeeded()` which auto-cancels expired orders and restores stock.
- **Shipping & Auto-Completion (`OrderObserver`, `CompleteDeliveredOrders`)**:
  - Updating `Order.status` to `shipped` fires `OrderObserver`, setting `shipped_at` timestamp and dispatching `OrderShippedMail` after commit.
  - `orders:complete-delivered` updates `shipped` orders to `completed` after `shippingMethod.estimated_days` pass.

---

## What to Avoid
1. **Do NOT introduce `Services/` or `Actions/` classes** unless refactoring the entire codebase. Keep business logic directly inside Controllers to maintain consistency.
2. **Do NOT update `Product.stock` manually** when variants exist; always update `ProductVariant.stock` so `syncProductStock()` can aggregate total stock.
3. **Do NOT update multi-table transaction data outside `DB::transaction`** (e.g., creating orders, updating stock, deleting cart items).
4. **Do NOT skip stock restoration logic** when cancelling orders or handling expired payments.
5. **Do NOT enable manual order creation in Filament Admin Panel**; `OrderResource::canCreate()` explicitly returns `false`.
6. **Do NOT remove `.afterCommit()`** on queue dispatches in observers to avoid race conditions prior to database transaction commit.

---

## Filament Admin Panel Specifics
- **Path**: `/admin`
- **Auth Guard**: `admin`
- **Resources**:
  - `UserResource`: User management.
  - `CategoryResource`: Product categories.
  - `ProductResource`: Products with `VariantsRelationManager`.
  - `ShippingMethodResource`: Shipping methods and costs.
  - `OrderResource`: Order monitoring and status changes with `ItemsRelationManager` (`canCreate` disabled).
- **Conventions**: Setting order status to `shipped` in `OrderResource` form automatically populates `shipped_at`, which triggers `OrderObserver`.

---

## API Response Format
- **Not used in this project**. This project is a server-side rendered Blade application. The only JSON endpoint is `GET /payment/{orderNumber}/snap-token` returning `{ "snap_token": "..." }` or `{ "error": "..." }`.
