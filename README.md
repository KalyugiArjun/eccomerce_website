# ShopKart - Production-Grade Indian Multi-Vendor E-Commerce Marketplace

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-16%20Passed%20(84%20Assertions)-success?style=for-the-badge)](tests/)
[![Sanctum](https://img.shields.io/badge/API-Sanctum%20REST-blue?style=for-the-badge)](routes/api.php)

**ShopKart** is a production-style, hyper-local and pan-India multi-vendor marketplace platform. Built with modern Indian e-commerce conventions in mind, it combines the vast catalog scale of Amazon/Flipkart with the instantaneous neighborhood discovery of Blinkit/Zepto.

---

## 🌟 Core USPs & Architectural Innovations

### 1. Multi-Vendor Order Splitting & Isolation
When a customer purchases items from multiple distinct sellers in a single checkout:
- **Master Order (`ORD-...`)**: The customer and platform admin see a unified order with aggregate totals, taxes, discounts, and payment lifecycle.
- **Decomposed Seller Sub-Orders (`SUB-...-A`, `SUB-...-B`)**: The system automatically splits the order into isolated sub-orders per vendor.
- **Strict Tenant Isolation**: Sellers can only view, pack, update status, and track shipments for their own packages. Cross-seller access is prevented at the database and policy level.

### 2. Hyper-Local Nearby Seller Discovery & Leaflet Maps
- Real-time GPS and pincode location selection (e.g. Lucknow, Delhi, Mumbai, Bengaluru).
- **Haversine Geolocation Engine**: Calculates real-time radial distance ($km$) between the shopper and active vendor storefronts.
- **Interactive OpenStreetMap + Leaflet**: Displays seller shop locations, delivery radiuses, and live ETAs without requiring paid Google Maps API keys.

### 3. "Need it Fast?" Instant Delivery Mode
- Shoppers can filter the marketplace by delivery speed:
  - ⚡ **Under 30 minutes**
  - 🛵 **Under 1 hour**
  - 📦 **Today**
  - 📅 **Tomorrow / Anytime**
- Only products whose sellers service the customer's current radius within that timeframe are surfaced.

### 4. Transparent Seller Comparison & "Best Value" Breakdown
- Multiple vendors selling the same or comparable products are displayed side-by-side with price, distance, rating, delivery time, return policy, and trust score.
- **No Black-Box Bias**: Rather than declaring a generic "Best Seller", ShopKart explains transparent factors: *"4.8★ + 1,240 orders + 25 min delivery"*.

### 5. Product Price History & Drop Alerts
- **Price History Ledger**: Every seller price adjustment is tracked with timestamps.
- **Interactive Price Chart (Chart.js)**: Customers view historical price fluctuations directly on the product detail page.
- **Price Drop Alerts**: Customers can set target threshold prices and receive database and email notifications when prices drop.

### 6. Group Buying Engine (Group Buy & Save)
- Multi-tier group discounts (e.g. 2 buyers = 10% off, 5 buyers = 18% off, 10 buyers = 25% off).
- Live countdown timers, atomic database locking (`lockForUpdate`), duplicate join prevention, and automatic order conversion upon target completion.

### 7. Grounded AI Shopping Assistant
- Context-aware shopping assistant supporting Natural English and Hinglish (e.g. *"Mujhe ₹2000 ke andar college ke liye black shoes chahiye"*).
- **Strict Catalog Grounding**: Parses intent and budget constraints to query real database products. Never hallucinates non-existent products.
- Modular service class (`AiAssistantService`) ready for local semantic matching or Gemini API.

### 8. Visual Product Search (Search by Image)
- Customers upload a picture or photo of a product.
- `VisualSearchService` processes image properties and visual tags, querying similar items across the multi-vendor catalog.

### 9. Gift Mode
- Checkout option with custom gift wrapping, personalized gift messages, scheduled delivery, and automatic price-tag hiding on generated GST tax invoices.

### 10. Social Wishlists
- Customers can create wishlists (e.g., *"Arjun's Birthday Wishlist"*) and generate shareable public links (`/wishlist/shared/{token}`) that protect private customer information.

### 11. Multi-Tier Commission Ledger & Seller Payouts
- Supports 3-tier commission hierarchy: **Seller-Specific Rate > Category-Specific Rate > Platform Global Default (10%)**.
- Automatic deduction of platform commission, calculating net seller receivable per sub-order.
- Admin disbursement ledger for processing NEFT/RTGS bank transfers with UTR tracking.

### 12. Indian Statutory Compliance & GST Invoices
- Formats tailored for India: ₹ (INR), 10-digit mobile numbers, 6-digit PIN codes, 15-character GSTIN, PAN, and Bank IFSC validation.
- Printable GST Tax Invoices featuring Seller GSTIN, HSN breakdown, SAC codes, and delivery disclaimers.

---

## 🏗️ Architecture & Technology Stack

| Layer | Technology |
|---|---|
| **Framework** | Laravel 12.x / PHP 8.2+ |
| **Database** | SQLite (Default for zero-config portable execution) / MySQL 8.0+ |
| **Authentication** | Laravel Session + Laravel Sanctum (Token-based REST APIs) |
| **Authorization** | Laravel Policies (`OrderPolicy`, `SellerOrderPolicy`, `ProductPolicy`) & Middleware (`admin`, `seller`, `customer`) |
| **Frontend** | Laravel Blade, HTML5, Vanilla CSS3 (`shopkart.css`), Bootstrap 5.3 |
| **Maps** | Leaflet.js + OpenStreetMap |
| **Charts** | Chart.js 4.4 |
| **Services** | `OrderService`, `CommissionService`, `PaymentService`, `SellerService`, `GroupBuyingService`, `RecommendationService`, `AiAssistantService`, `VisualSearchService` |

---

## 🚀 Quick Start & Installation

### 1. Prerequisites
- PHP 8.2 or 8.3
- Composer 2.x
- SQLite extension or MySQL database

### 2. Clone & Install Dependencies
```bash
git clone <repo-url> shopkart
cd shopkart

composer install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup & Realistic Indian Marketplace Seeder
```bash
# Creates all 13 database tables and seeds realistic Indian marketplace data
php artisan migrate:fresh --seed
```

The database seeder provisions:
- **51 Users**: 1 Admin, 10 Approved Sellers across Indian metros, 40 Active Shoppers
- **15 Categories & Subcategories**: Electronics, Fashion, Footwear, Groceries, Home Appliances, etc.
- **12 Verified Brands**: Boat, Nike, Samsung, Titan, FabIndia, etc.
- **52 Products with Color & Size Variants**: Real Indian prices, SKUs, inventory levels, and price history
- **4 Active Coupons**: `SAVE20`, `WELCOME100`, `FESTIVE15`, `FLAT150`
- **Active Group Buying Campaigns & Verified Reviews**
- **Simulated Orders & Split Sub-Orders**

---

## 🔑 Pre-Seeded Demonstration Accounts

All accounts share the default password: **`password123`**

| Role | Email | Password | Access Portal |
|---|---|---|---|
| **Platform Admin** | `admin@shopkart.in` | `password123` | `/admin/dashboard` |
| **Seller 1 (Lucknow)** | `lucknow.fashion@shopkart.in` | `password123` | `/seller/dashboard` |
| **Seller 2 (Delhi)** | `delhi.electronics@shopkart.in` | `password123` | `/seller/dashboard` |
| **Seller 3 (Mumbai)** | `mumbai.footwear@shopkart.in` | `password123` | `/seller/dashboard` |
| **Customer** | `aarav.sharma.1@example.com` | `password123` | Storefront & Checkout |

---

## 🧪 Automated Testing Suite

ShopKart comes with complete automated test coverage for critical business workflows:

```bash
php artisan test
```

### Test Suites Included:
- [x] **`AuthTest`**: Customer registration, login, seller onboarding compliance, and unauthorized access denials.
- [x] **`MultiVendorOrderTest`**: Multi-vendor cart creation, unified order placement, automated splitting into seller sub-orders, commission deduction, and inventory decrement.
- [x] **`SellerIsolationTest`**: Strict tenant isolation preventing Seller A from inspecting or editing Seller B's packages or catalog items.
- [x] **`CouponTest`**: Percentage discounts with maximum discount caps, minimum order thresholds, and user-limit validations.
- [x] **`GroupBuyingTest`**: Group buy campaign initialization, atomic locking, duplicate join prevention, and auto-completion when target buyers join.
- [x] **`ApiSanctumTest`**: Token-based Sanctum authentication (`/api/register`, `/api/login`, `/api/profile`), public product and seller API endpoints.

---

## 📡 REST API Endpoints (Laravel Sanctum)

All API responses follow standardized JSON structures. Protected endpoints require the `Authorization: Bearer <token>` header.

### Public Endpoints
- `POST /api/register` - Create customer account & issue API token
- `POST /api/login` - Authenticate customer & issue API token
- `GET /api/products` - Filterable, paginated product catalog
- `GET /api/products/{slug}` - Detailed product specifications, variants & reviews
- `GET /api/categories` - Marketplace taxonomy with subcategories
- `GET /api/sellers` - Directory of approved vendors

### Protected Endpoints (`auth:sanctum`)
- `GET /api/profile` - Authenticated customer details & preferred location
- `POST /api/logout` - Revoke current API token
- `GET /api/cart` - View items, grouped by vendor with totals
- `POST /api/cart/add` - Add product or variant to cart
- `DELETE /api/cart/item/{itemId}` - Remove item from cart
- `GET /api/orders` - Customer's historical orders
- `GET /api/orders/{orderNumber}` - Order details with decomposed seller packages
- `GET /api/wishlist` - Customer's saved wishlist items

---

## 🛡️ Security Implementation
- **Strict Role-Based Middleware**: `EnsureAdmin`, `EnsureSeller`, and `EnsureCustomer` enforce boundary isolation on all routes.
- **Server-Side Payment Signature Verification**: Prevents client-side payment tampering; checks Razorpay HMAC signatures.
- **Transactional State Management**: Database transactions (`DB::transaction`) wrap checkout, inventory adjustments, and status progressions.
- **XSS & Injection Protection**: Eloquent parameter binding and Blade escaping throughout.
- **CSRF Token Verification**: Active across all web POST/PUT forms.

---

## 📄 License
The ShopKart platform is open-source software licensed under the [MIT license](LICENSE).
