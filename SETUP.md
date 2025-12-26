# JaKòb - Setup & Installation Guide

## 🚀 Quick Start

This guide will help you set up the JaKòb platform locally for development.

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.0+** (Recommended: PHP 8.5.1)
- **MySQL 8.0+** (or MariaDB 10.5+)
- **Web Server** (Apache, Nginx, or PHP built-in server)
- **phpMyAdmin** (Optional but recommended for database management)

---

## 🛠️ Installation Steps

### 1. Clone/Download the Project

```bash
cd /path/to/your/webroot
git clone <repository-url> jakob-development
cd jakob-development
```

### 2. Configure Environment Variables

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` file with your database credentials:

```env
# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=jakob
DB_USER=root
DB_PASSWORD=your_password_here

# Application
APP_NAME=JaKòb
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
```

**Important:** Never commit your `.env` file to version control!

### 3. Create MySQL Database

Open your MySQL client (phpMyAdmin or command line):

```sql
CREATE DATABASE jakob CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or use phpMyAdmin:
1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Click "New" in the left sidebar
3. Database name: `jakob`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### 4. Install Database Schema

**Option A: Web-based Installer (Recommended)**

1. Start your PHP server (see Step 5)
2. Navigate to: `http://localhost:8000/database/install.php`
3. The installer will:
   - Read your `.env` configuration
   - Create all database tables
   - Insert sample test data
   - Show you test account credentials

**Option B: Manual Installation**

```bash
mysql -u root -p jakob < database/schema.sql
```

Or via phpMyAdmin:
1. Select the `jakob` database
2. Click "Import" tab
3. Choose file: `database/schema.sql`
4. Click "Go"

### 5. Start the Development Server

**Using PHP Built-in Server:**

```bash
php -S localhost:8000
```

**Using XAMPP/WAMP:**
- Place project in `htdocs` or `www` folder
- Access via `http://localhost/jakob-development`

**Using Apache/Nginx:**
- Configure virtual host pointing to project root
- Ensure `.htaccess` is enabled (for Apache)

### 6. Access the Application

Open your browser and navigate to:

```
http://localhost:8000
```

You should be redirected to the splash screen (`pages/auth/splash.html`).

---

## 🧪 Test Accounts

After running the database installer, you'll have two test accounts:

### Donor Account
- **Phone:** `+50912345678`
- **Password:** `password123`
- **Type:** Donor (Donateur)
- **Access:** Main donor dashboard

### Influencer Account
- **Phone:** `+50987654321`
- **Password:** `password123`
- **Type:** Influencer (Créateur)
- **Access:** Creator campaigns dashboard

---

## 📁 Project Structure

```
jakob-development/
├── .env                      # Environment configuration (create from .env.example)
├── .env.example              # Environment template
├── index.html                # Entry point (redirects to splash)
│
├── api/                      # Backend API endpoints
│   ├── register.php          # User registration
│   ├── login.php             # User authentication
│   ├── logout.php            # Session termination
│   └── me.php                # Current user data
│
├── config/                   # Configuration files
│   ├── database.php          # MySQL PDO connection
│   └── session.php           # Session management helpers
│
├── database/                 # Database files
│   ├── schema.sql            # Complete database schema
│   └── install.php           # Web-based installer
│
├── pages/                    # Frontend pages
│   ├── auth/                 # Authentication pages
│   │   ├── splash.html       # Welcome/onboarding
│   │   ├── login.html        # User login
│   │   └── signup.html       # User registration
│   │
│   ├── main/                 # Main application pages
│   │   ├── home.html         # Donor dashboard
│   │   ├── explore.html      # Impact stories
│   │   ├── creator-profile.html
│   │   ├── campaign-details.html
│   │   ├── donation.html
│   │   └── payment-success.html
│   │
│   ├── user/                 # User profile & settings
│   │   ├── profile.html
│   │   ├── wallet.html
│   │   └── notifications.html
│   │
│   ├── creator/              # Creator-specific pages
│   │   └── my-campaigns.html
│   │
│   └── error/                # Error pages
│       ├── 404.html
│       └── offline.html
│
└── assets/                   # Static assets
    └── css/
        └── design-system.js  # Tailwind config
```

---

## 🗄️ Database Schema

The database supports **two user types**:

### Users Table (Base)
- Stores common user data (email, phone, password, user_type)
- `user_type` ENUM: `'donor'` or `'influencer'`

### Donors Table
- Linked to users via `user_id`
- Stores donor-specific data (first_name, last_name, total_donated, etc.)

### Influencers Table
- Linked to users via `user_id`
- Stores creator data (username, display_name, category, verified, etc.)

### Additional Tables
- `campaigns` - Fundraising campaigns
- `donations` - Donation records
- `followers` - Donor-influencer relationships
- `payment_methods` - User payment methods
- `transactions` - Wallet transactions
- `notifications` - User notifications

---

## 🔐 Authentication Flow

### Registration (signup.html)
1. User selects type (donor or influencer)
2. Enters email, phone, password
3. POST to `api/register.php`
4. Creates user in `users` table
5. Creates type-specific record in `donors` or `influencers` table
6. Redirects based on type:
   - Donors → `pages/main/home.html`
   - Influencers → `pages/creator/my-campaigns.html`

### Login (login.html)
1. User enters phone + password
2. POST to `api/login.php`
3. Verifies credentials with bcrypt
4. Creates PHP session with user data
5. Returns user type and profile data
6. Redirects based on type

### Session Management
- PHP sessions via `config/session.php`
- Helper functions: `isLoggedIn()`, `isDonor()`, `isInfluencer()`
- Session data includes user_id, user_type, email, phone
- Optional "Remember Me" with secure cookies

---

## 🎨 Frontend Stack

- **CSS Framework:** Tailwind CSS (via CDN)
- **Icons:** Material Symbols Outlined
- **Fonts:** Plus Jakarta Sans (Google Fonts)
- **JavaScript:** Vanilla JS (ES6+)
- **Dark Mode:** Class-based (`class="dark"`)

### Color Palette
```css
{
  "primary": "#ea2a33",        /* Haitian red */
  "primary-dark": "#c91b24",   /* Hover state */
  "background-dark": "#211111",
  "surface-dark": "#2f1a1b",
}
```

---

## 🔧 Configuration

### Environment Variables (.env)

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | MySQL server host | localhost |
| `DB_PORT` | MySQL port | 3306 |
| `DB_NAME` | Database name | jakob |
| `DB_USER` | Database username | root |
| `DB_PASSWORD` | Database password | (empty) |
| `APP_ENV` | Environment (development/production) | development |
| `APP_DEBUG` | Enable debug mode | true |

### PHP Requirements
- PDO extension
- JSON extension
- Session support
- BCrypt password hashing

---

## 🧪 Testing

### Manual Testing

1. **Registration Flow:**
   - Go to `/pages/auth/signup.html`
   - Select "Support" (donor) or "Create" (influencer)
   - Fill form and submit
   - Verify redirect to correct dashboard

2. **Login Flow:**
   - Go to `/pages/auth/login.html`
   - Use test credentials above
   - Verify redirect based on user type

3. **API Testing:**
   ```bash
   # Register new user
   curl -X POST http://localhost:8000/api/register.php \
     -H "Content-Type: application/json" \
     -d '{"user_type":"donor","email":"test@example.com","phone":"+50911112222","password":"test123"}'

   # Login
   curl -X POST http://localhost:8000/api/login.php \
     -H "Content-Type: application/json" \
     -d '{"phone":"+50911112222","password":"test123"}'
   ```

---

## 🐛 Troubleshooting

### Database Connection Errors

**Error:** "PDO connection failed"
- Check `.env` credentials
- Verify MySQL service is running
- Ensure database `jakob` exists

**Error:** "Access denied for user"
- Verify `DB_USER` and `DB_PASSWORD` in `.env`
- Grant privileges: `GRANT ALL ON jakob.* TO 'root'@'localhost';`

### Page Not Loading

**Blank page or 500 error:**
- Check PHP error logs
- Enable error display in `.env`: `APP_DEBUG=true`
- Verify file permissions (755 for directories, 644 for files)

**404 errors on assets:**
- Check file paths are correct
- Ensure web server is serving from project root
- Verify `.htaccess` (Apache) or nginx config

### Login/Registration Not Working

**"CORS error" in console:**
- APIs include CORS headers by default
- If still failing, check server CORS configuration

**"Invalid JSON data":**
- Check browser console for request payload
- Verify Content-Type: application/json header

**Passwords not matching:**
- Sample password is `password123`
- New passwords are hashed with bcrypt

---

## 📝 Next Steps

After successful installation:

1. **Complete Profile Setup:**
   - Add profile completion pages for donors and influencers
   - Collect missing data (first_name, last_name, username, etc.)

2. **Payment Integration:**
   - Integrate MonCash API
   - Integrate NatCash API
   - Test donation flow

3. **Email/SMS Verification:**
   - Implement OTP verification
   - Email verification links

4. **Production Deployment:**
   - Set `APP_ENV=production` and `APP_DEBUG=false`
   - Use HTTPS for all requests
   - Configure proper CORS headers
   - Set up database backups

---

## 🇭🇹 Haitian Context

**Categories:**
- 🎨 Art & Artizana
- 🎵 Mizik
- 📚 Edikasyon
- 👥 Jèn (Youth)
- 🏛️ Eritaj & Istwa (Heritage)

**Language:**
- Primary: English (UI)
- Secondary: Kreyòl Ayisyen (messages, greetings)

**Payment Methods:**
- MonCash (Haitian mobile money)
- NatCash (Haitian mobile wallet)
- Credit/Debit cards
- Bank transfers

---

## 📞 Support

For issues or questions:
- Check `INDEX.md` for full documentation
- Review `NAVIGATION.md` for page structure
- Open an issue in the repository

**Version:** 2.0.0 (MySQL Migration + Dual User System)
**Last Updated:** December 25, 2025
