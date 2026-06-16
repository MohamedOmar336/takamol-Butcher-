# 🥩 Takamul Butcher POS & Inventory System

A comprehensive, bilingual (Arabic & English) Point of Sale and Admin management system built specifically for the **Takamul Butcher Shop** using **Laravel 11** and **SQLite**.

---

## 🔑 Demo Account Credentials

Use these seeded credentials to log in at `/login` and explore different permissions:

| Role | Email | Password | Granted Permissions |
| :--- | :--- | :--- | :--- |
| **Super Admin** (المدير العام) | `admin@takamul.com` | `admin123` | All Access / Master Control |
| **Cashier** (كاشير مبيعات) | `cashier@takamul.com` | `cashier123` | `access_pos` (POS checkout terminal only) |
| **Store Manager** (مدير فرع) | `manager@takamul.com` | `manager123` | `access_pos`, `manage_inventory`, `view_reports` |

---

## ⚡ Main Features

1. **POS Cashier Terminal (`/`)**:
    *   **5-Column Grid Layout** optimized for desktop and touchscreens.
    *   **Scale Barcode Decoding (EAN-13)**: Intercepts TM-A electronic scale barcodes (e.g. `2011135015034`), extracts the product PLU (`01113`), decodes weight (`1.503 kg`), and adds it to the cart.
    *   **USB Barcode Scanner Integration**: Background Javascript intercepts scans instantly, playing a scanner beep audio tone on success.
    *   **Bilingual & Themes**: Instantly toggle between English and Arabic (RTL layout) and Light/Dark themes.
    *   **Customer Credit/Debt Checkout**: Link customers to orders, enforce individual credit limits, and support credit (Debt/الشكك) payments.
2. **Admin Panel Dashboard (`/admin`)**:
    *   **Key Metrics Summary**: Live sales revenue, low stock counts, and debt ledger balances.
    *   **Weekly Sales Chart**: Pure CSS/HTML flex layout chart.
    *   **Permissions System (`/admin/users`)**: Create sub-users with specific dashboard module permissions (`access_pos`, `manage_inventory`, `view_reports`, `manage_users`).
3. **CRM Customer Directory (`/admin/customers`)**:
    *   Credit limits management.
    *   Live customer financial ledger listing full transaction history and debt settle/payoff forms.
4. **Excel Product Importer (`/admin/products`)**:
    *   Bulk add or update products using `.xlsx` or `.csv` files.
    *   Accepts column names in both Arabic and English.
    *   Includes downloadable pre-filled templates.
5. **Daily Sales Email Report**:
    *   Sends a summary of daily sales, low stock alerts, and top items to the owner's email at 23:30 daily.
    *   Includes a **"Send Report Now"** override button in the dashboard for manual reports.
6. **Thermal Receipt Printing (`/pos/receipt/{id}`)**:
    *   Designed for standard 80mm thermal paper widths with print dialog auto-launch and auto-close.

---

## ⚙️ Setup and Installation

### Prerequisites
*   PHP 8.2 or higher
*   SQLite extension enabled in `php.ini`
*   Composer

### Steps

1.  **Clone the Repository** and open the directory:
    ```powershell
    cd "Point of Sale system for a butcher"
    ```
2.  **Install dependencies**:
    ```powershell
    composer install
    ```
3.  **Configure Environment**:
    *   Copy `.env.example` to `.env`.
    *   The database is pre-configured to SQLite (`database/database.sqlite`).
    *   Update email settings for the daily sales report:
        ```env
        MAIL_MAILER=smtp
        MAIL_HOST=sandbox.smtp.mailtrap.io
        MAIL_PORT=2525
        MAIL_USERNAME=your_username
        MAIL_PASSWORD=your_password
        OWNER_EMAIL=owner@example.com
        ```
4.  **Database Migration & Seeding**:
    *   Run migrations and seed the initial categories, products, and default accounts:
        ```powershell
        php artisan migrate:fresh --seed
        ```
5.  **Run Dev Server**:
    ```powershell
    php artisan serve
    ```
    Visit `http://127.0.0.1:8000` in your web browser.

---

## ⚖️ How to Test the Scale Barcode Simulation
1. Log in as Super Admin (`admin@takamul.com` / `admin123`).
2. Go to the **Scale Simulator** page (⚖️) in the sidebar.
3. Select a product, enter a weight (e.g. `1.503` kg), and click **"Generate Scale Barcode"**.
4. Copy the generated barcode (e.g. `2011135015034`).
5. Open the **POS Terminal** (🛒), focus/click anywhere on the page, paste it, and press Enter.
6. A beep sound plays and the item is added to the cart with the exact weight and calculated price.

---

## 📄 Documentation

Full details of this system written in **Egyptian Arabic** (البلدي) are available:
*   Word Document (.docx format): [documentation_eg.docx](public/documentation_eg.docx) (or the copy in the root folder).
*   Markdown Document (.md format): [documentation_eg.md](documentation_eg.md).
