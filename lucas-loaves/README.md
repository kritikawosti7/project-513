# Luca's Loaves

Dynamic PHP/MySQL bakery website for the Dynamic Website Development assessment.

## Project layout

```text
lucas-loaves/
├── app/
│   ├── .htaccess
│   ├── admin_auth.php
│   ├── bootstrap.php
│   ├── config.php
│   ├── create_admin.php
│   └── partials/
│       ├── footer.php
│       └── header.php
├── database.sql
├── public/
│   ├── admin-download.php
│   ├── admin-logout.php
│   ├── admin-order.php
│   ├── admin.php
│   ├── about.php
│   ├── careers.php
│   ├── contact.php
│   ├── images/
│   ├── index.php
│   ├── order.php
│   ├── privacy.php
│   ├── site.js
│   ├── sitemap.php
│   └── style.css
└── storage/
    ├── .htaccess
    └── applications/
```

The web server document root should point to `public/`. PHP configuration and
uploaded applicant files are kept outside that web root. Deny rules in
`app/.htaccess` and `storage/.htaccess` provide an extra barrier when using the
default XAMPP `htdocs` document root.

## XAMPP setup

1. Copy the complete `lucas-loaves` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin/`, choose **Import**, select
   `C:\xampp\htdocs\lucas-loaves\database.sql`, and run the import.
4. Confirm the local MySQL settings in `app/config.php`. The defaults match a
   standard XAMPP installation (`root` with an empty password). If you set a
   MySQL root password, put it in `DB_PASSWORD` in this file and keep the file
   outside the public web root.
5. Open `http://localhost/lucas-loaves/public/`. A product list confirms that
   PHP can connect to the database.

For deployment, point the web server document root at the project's `public/`
directory and set a non-empty database password. Do not expose `app/` or
`storage/` through the web server.

## Main project files

- `database.sql`: creates the database and five related tables, then seeds the
  four bread products.
- `app/config.php`: local database and project path settings.
- `app/bootstrap.php`: starts a hardened PHP session and provides PDO, HTML
  escaping, and CSRF helpers for later phases.
- `public/index.php`: bakery landing page with products loaded from MySQL.
- `public/about.php`, `public/contact.php`, `public/sitemap.php`, and
  `public/privacy.php`: public information pages.
- `public/careers.php`: validates career applications and stores validated
  resumes in `storage/applications/`, outside the public document root.
- `public/order.php`: provides the session cart and securely creates pickup
  orders using current database prices.
- `public/admin.php`, `public/admin-order.php`, `public/admin-download.php`, and
  `public/admin-logout.php`: protected staff dashboard, order details, private
  document downloads, and CSRF-protected logout.
- `app/admin_auth.php`: shared 30-minute administrator session checks.
- `app/create_admin.php`: CLI-only first-admin setup; it stores a bcrypt hash,
  never a plaintext password.
- `app/partials/`: shared accessible navigation and footer.
- `public/style.css` and `public/site.js`: responsive site styles and mobile
  navigation behavior.

## Create the first admin account

From PowerShell, run:

```powershell
cd C:\xampp\htdocs\lucas-loaves
C:\xampp\php\php.exe app\create_admin.php
```

Follow the prompts to choose a unique username and a strong password of at
least 12 characters. For bcrypt compatibility, the password is limited to 72
bytes. The password prompt is local to your console; do not put a password in
source code or a URL. Then open
`http://localhost/lucas-loaves/public/admin.php` and sign in.

## Assessment portfolio screenshots

Capture these screens with the browser at desktop width, plus at least one
mobile-width view to demonstrate responsive behavior:

1. Home page hero, featured breads, and opening-hours strip.
2. About page showing the artisan process and ingredient values.
3. Online ordering page showing products, then a basket with an updated
   quantity and total.
4. Checkout confirmation showing the generated order number and total. Use
   test customer details.
5. Careers page form and its successful submission message. Use a test
   applicant and do not expose a real resume or personal contact details.
6. Contact page and Site Map.
7. Admin sign-in page (never include the password) and the dashboard with
   example order/application records.
8. An order detail view and the application cover-letter/resume download
   controls. Use test records and do not include private applicant data.

Avoid capturing passwords, live customer/applicant information, or the contents
of uploaded private documents.
