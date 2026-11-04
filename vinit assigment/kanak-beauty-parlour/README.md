# Kanak Beauty Parlour — Premium Website

**Tagline:** Beauty That Makes You Shine.  
**Owner:** Kavita Kawadker  
**Location:** Chandrashekhar Ward, Sadar, Betul, Madhya Pradesh, Near Sai Mandir  
**Contact:** 8839131870 | [@kavita_kawadker_makeup_artist](https://www.instagram.com/kavita_kawadker_makeup_artist)

---

## 🛠 Technology Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, custom responsive CSS, Vanilla JS |
| Backend | PHP 8+ (Core PHP, PDO) |
| Database | MySQL 8 |
| Dev Environment | XAMPP (Apache + MySQL) |
| Fonts | Google Fonts (Cormorant Garamond + Lato) |
| Icons | Font Awesome 6 |
| Payment | Razorpay |

---

## 📁 Project Structure

```
kanak-beauty-parlour/
│
├── index.php                 # Home page
├── about.php                 # About page
├── services.php              # Services page
├── gallery.php               # Gallery page
├── appointment.php           # Appointment booking
├── contact.php               # Contact page
│
├── config/
│   └── database.php          # PDO database connection
│
├── includes/
│   ├── header.php            # Shared HTML header + nav
│   ├── footer.php            # Shared footer
│   └── functions.php         # Helper functions
│
├── admin/
│   ├── create_admin.php      # CLI-only first-admin setup
│   ├── login.php             # Admin login
│   ├── logout.php            # CSRF-protected logout
│   ├── dashboard.php         # Admin dashboard
│   ├── appointments.php      # Appointment status and notes
│   └── gallery.php           # Validated gallery uploads/removal
│
├── api/
│   ├── book_appointment.php  # Booking AJAX endpoint
│   └── payment.php           # Razorpay order and payment verification
│
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── js/
│   │   └── main.js           # Main JavaScript
│   └── uploads/              # Created for gallery uploads; PHP execution denied
│
├── database.sql              # Complete database schema + seed data
├── .env.example              # Environment config template
└── README.md                 # This file
```

---

## 🚀 Installation Guide (XAMPP)

### Requirements
- XAMPP with PHP 8.0 or newer, Apache, and MySQL/MariaDB.
- PHP extensions: `pdo_mysql`, `fileinfo`, `mbstring`, and `curl` (cURL is required only for Razorpay API calls).
- phpMyAdmin, included with standard XAMPP installations.

### 1. Copy the project into XAMPP
Copy the existing project folder—do not replace its files—to:

```
C:\xampp\htdocs\kanak-beauty-parlour\
```

Confirm that `index.php`, `database.sql`, and `.env.example` are directly inside this folder.

### 2. Start Apache and MySQL
1. Open **XAMPP Control Panel**.
2. Click **Start** beside **Apache**.
3. Click **Start** beside **MySQL**.
4. Confirm both show **Running**. If a port is already in use, resolve that conflict in XAMPP before continuing.

### 3. Import the database
1. Open [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
2. Choose **New**, create `kanak_beauty_parlour` with `utf8mb4_unicode_ci` collation.
3. Select that database, choose **Import**, and select `C:\xampp\htdocs\kanak-beauty-parlour\database.sql`.
4. Click **Import/Go** and confirm the tables and seed services/settings appear.
5. Import this schema once into a new database. It is not a repeatable data migration.

The schema contains an inactive legacy admin row for compatibility; it cannot log in. Create your own active administrator using step 6 below. If you have an older database, first disable the old seed account in phpMyAdmin:

```sql
UPDATE admins
SET is_active = 0
WHERE username = 'admin' AND email = 'admin@kanakbeauty.com';
```

### 4. Set up `.env`
Open PowerShell and run:

```powershell
Set-Location 'C:\xampp\htdocs\kanak-beauty-parlour'
Copy-Item .env.example .env
notepad .env
```

Set the database values to match your local MySQL account. XAMPP commonly uses `root` with an empty password on a local development install; if your MySQL root account has a password, enter that password instead.

```dotenv
DB_HOST=localhost
DB_NAME=kanak_beauty_parlour
DB_USER=root
DB_PASS=
APP_URL=http://localhost/kanak-beauty-parlour
APP_ENV=development
```

Keep `.env` private and never commit real passwords or API keys. Do not add Razorpay keys until you have your own valid **test-mode** credentials. Leave the placeholders in `.env.example` unchanged.

### 5. Confirm PHP extensions and uploads
In XAMPP Control Panel, click **Config** beside Apache, open `PHP (php.ini)`, and confirm these extensions are enabled (remove a leading `;` if present): `pdo_mysql`, `fileinfo`, `mbstring`, and, for Razorpay, `curl`. Restart Apache after changing `php.ini`.

The admin gallery creates `assets\uploads\gallery` on first upload. The Apache `.htaccess` in `assets\uploads` disables directory listings and blocks PHP-family files. Ensure the Apache account can write to `assets` if image uploads are needed. For images up to 5 MB, `upload_max_filesize` must be at least `5M` and `post_max_size` must be greater than `5M`; restart Apache after changing these values.

### 6. Create the first admin securely
Use the XAMPP PHP CLI from a PowerShell window; do not put your password in the command, SQL file, or README. The following prompts without echoing the password, then pipes it to the CLI setup script:

```powershell
Set-Location 'C:\xampp\htdocs\kanak-beauty-parlour'
$secret = Read-Host 'Choose a new admin password (12+ characters)' -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secret)
try {
    $plain = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    $plain | & 'C:\xampp\php\php.exe' '.\admin\create_admin.php' 'your-chosen-username' 'your-email@example.com' 'Your Name'
    if ($LASTEXITCODE -ne 0) { throw 'Admin creation failed; check the message above.' }
}
finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    Remove-Variable secret, pointer, plain -ErrorAction SilentlyContinue
}
```

Replace the example username, email, and name with your own values. The script accepts the first account only when there are no active admins, validates the account details, and stores a `password_hash()` result; it is unavailable over HTTP. Keep the chosen login details private.

### 7. Launch the website
Open [http://localhost/kanak-beauty-parlour/](http://localhost/kanak-beauty-parlour/) in your browser. The admin sign-in is at [http://localhost/kanak-beauty-parlour/admin/login.php](http://localhost/kanak-beauty-parlour/admin/login.php).

If the database connection fails, check that MySQL is running, `.env` is in the project root, and its database credentials match MySQL. Check `C:\xampp\apache\logs\error.log` for PHP errors.

---

## 🔐 Admin and available workflows

Sign in at `/admin/login.php` using the account you created in step 6. The dashboard links to appointment review/status updates and gallery management. The login uses password hashes, CSRF checks, session ID regeneration, a 30-minute idle timeout, and secure session-cookie settings. Logout is a CSRF-protected POST.

The CLI first-admin script is intentionally not an HTTP account-creation page. It refuses to create another account once an active administrator exists.

---

## 💳 Payment Integration (Razorpay)

### Optional test mode
Online checkout is disabled unless you configure real Razorpay credentials and set an actual positive service price in the `services` table. All seeded service prices are NULL (“On Request”), so checkout correctly refuses to create a payment for them.

To test using Razorpay **test-mode** credentials:
1. Generate your own test keys in your Razorpay account.
2. Set them in the private `.env` file:
   ```
   RAZORPAY_KEY_ID=your_test_key_id
   RAZORPAY_KEY_SECRET=your_test_key_secret
   ```
3. Enable PHP cURL, restart Apache, and assign a real service price in MySQL.
4. Use only Razorpay's test checkout data. The server verifies the order signature and confirms the captured payment with Razorpay before updating the database.

No live credentials are included or enabled by default. Do not switch to live payments until your account is approved, production credentials are configured privately, HTTPS is enabled, and you have tested the end-to-end payment flow. The application stores gateway references/signatures, not card details.

---

## 📱 WhatsApp and business settings

The public footer and appointment page read phone, WhatsApp, address, opening hours, and Instagram values from `business_settings`. Update these values in phpMyAdmin; the current admin panel does not include a settings editor.

---

## 🔒 Security Features

- **PDO Prepared Statements** — All DB queries use parameterized queries
- **CSRF Tokens** — Booking, contact, payment, and admin mutations check tokens
- **Output Encoding** — User and database values are escaped when rendered
- **Password Hashing** — First admin password is stored using `password_hash()`
- **Session Security** — Strict mode, HTTP-only/SameSite cookies, login regeneration, and idle expiry
- **Image Upload Validation** — Server-side MIME, extension, size, and randomized-name checks
- **Admin Authorization** — Dashboard, appointments, and gallery pages require an active admin
- **Payment Verification** — Razorpay signatures and captured payment details are verified server-side

---

## 📊 Database Tables

| Table | Description |
|-------|-------------|
| `admins` | Admin user accounts |
| `customers` | Customer records |
| `services` | Beauty services catalog |
| `appointments` | Booking records |
| `payments` | Payment transactions |
| `gallery` | Portfolio images |
| `reviews` | Customer testimonials |
| `business_settings` | Configurable settings |
| `contact_messages` | Contact form submissions |

---

## 🎨 Customization

### Update Services

The current admin panel does not include service editing. Update service prices and activation in phpMyAdmin. Keep a price NULL until the parlour confirms it; online Razorpay checkout requires a price of at least ₹1.

### Add Gallery Photos

Sign into the admin area and choose **Gallery**. JPEG, PNG, and WebP files up to 5 MB are accepted; uploaded records appear on the public gallery. Do not upload images without permission to use them.

---

## 🌐 SEO

The website is optimized for:
- **Best Beauty Parlour in Betul**
- **Bridal Makeup Artist in Betul**
- **Makeup Artist in Betul, Madhya Pradesh**
- **Beauty Salon in Betul**
- **Party Makeup in Betul**

Public pages include page-specific titles/descriptions and semantic HTML. Fonts, icons, and sample photography load from third-party CDNs, so internet access is required for those assets; local page navigation and forms are served by XAMPP.

---

## 📞 Support

For technical support or customization:
- Review this README carefully
- Check PHP error logs: `C:\xampp\apache\logs\error.log`
- Ensure XAMPP Apache and MySQL are running
- Verify database was imported correctly

---

*Built for Kanak Beauty Parlour, Betul, Madhya Pradesh.*  
*Powered by PHP 8+, PDO, MySQL, and XAMPP.*
