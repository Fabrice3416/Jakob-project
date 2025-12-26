# JaKòb - Security Documentation

## 🔒 Password Management

### Password Hashing

JaKòb uses **bcrypt** (via PHP's `password_hash()`) for secure password storage.

#### Why Bcrypt?
- Industry-standard for password hashing
- Automatically salted (prevents rainbow table attacks)
- Computationally expensive (prevents brute force)
- Future-proof (cost factor can be increased)

---

## 🔐 Implementation Details

### Registration Process

When a user registers ([api/register.php](api/register.php)):

```php
// 1. Receive plain-text password from form
$password = $data['password'];

// 2. Validate password strength
if (strlen($password) < 6) {
    throw new Exception('Password must be at least 6 characters long');
}

// 3. Hash password with bcrypt
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// 4. Store hashed password in database
$stmt = $pdo->prepare('INSERT INTO users (..., password, ...) VALUES (..., ?, ...)');
$stmt->execute([..., $hashedPassword, ...]);
```

**Output Example:**
```
Plain password: password123
Bcrypt hash:    $2y$12$gr36jBxylQF1beUFx2kJiunjOpOYGI1YMHMiU9.oWrRiQXDFjOvH.
```

### Login/Authentication Process

When a user logs in ([api/login.php](api/login.php)):

```php
// 1. Receive plain-text password from form
$password = $data['password'];

// 2. Fetch hashed password from database
$stmt = $pdo->prepare('SELECT password FROM users WHERE phone = ?');
$stmt->execute([$phone]);
$user = $stmt->fetch();

// 3. Verify password against hash
if (!password_verify($password, $user['password'])) {
    throw new Exception('Invalid credentials');
}

// Password is correct, create session
$_SESSION['user_id'] = $user['id'];
```

---

## 📊 Database Storage

### Users Table

The `users` table stores password hashes in the `password` field:

```sql
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `phone` VARCHAR(20) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,  -- Stores bcrypt hash
    ...
);
```

**Important:**
- Field type: `VARCHAR(255)` to accommodate bcrypt hashes (~60 characters)
- **NEVER** store plain-text passwords
- **NEVER** log or display password hashes

---

## 🧪 Test Accounts

The database includes pre-hashed test accounts ([database/schema.sql](database/schema.sql)):

### Donor Account
```
Email:    marie@example.com
Phone:    +50912345678
Password: password123
Hash:     $2y$12$gr36jBxylQF1beUFx2kJiunjOpOYGI1YMHMiU9.oWrRiQXDFjOvH.
```

### Influencer Account
```
Email:    basquiat@example.com
Phone:    +50987654321
Password: password123
Hash:     $2y$12$gr36jBxylQF1beUFx2kJiunjOpOYGI1YMHMiU9.oWrRiQXDFjOvH.
```

**Hash Generation:**

The hash was generated using [database/generate-password-hash.php](database/generate-password-hash.php):

```bash
php database/generate-password-hash.php
```

---

## 🛡️ Security Best Practices

### Current Implementation ✅

- [x] Bcrypt password hashing (`PASSWORD_BCRYPT`)
- [x] Automatic salt generation
- [x] Password verification via `password_verify()`
- [x] Minimum password length (6 characters)
- [x] HTTPS recommended for production
- [x] Session-based authentication
- [x] CORS headers configured

### Recommended Improvements 📋

#### Password Strength Requirements
```php
function validatePasswordStrength($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }

    return $errors;
}
```

#### Rate Limiting
Prevent brute force attacks by limiting login attempts:

```php
// Track failed login attempts
$_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
$_SESSION['last_attempt_time'] = time();

if ($_SESSION['login_attempts'] > 5) {
    $time_since_last = time() - $_SESSION['last_attempt_time'];

    if ($time_since_last < 300) { // 5 minutes lockout
        throw new Exception('Too many failed attempts. Try again in 5 minutes.');
    }

    // Reset after cooldown
    $_SESSION['login_attempts'] = 0;
}
```

#### Password Reset Flow
Implement secure password reset:

1. User requests reset via email
2. Generate unique token with expiration
3. Send reset link via email
4. Validate token on reset page
5. Allow new password entry
6. Hash and update password
7. Invalidate reset token

#### Two-Factor Authentication (2FA)
Add extra security layer:

- SMS OTP verification
- Email verification codes
- Authenticator app support (TOTP)

---

## 🔑 Password Management Functions

### Hash a Password
```php
$hash = password_hash($password, PASSWORD_BCRYPT);
```

### Verify a Password
```php
if (password_verify($plainPassword, $hashedPassword)) {
    // Password is correct
}
```

### Rehash if Needed (for algorithm updates)
```php
if (password_needs_rehash($hash, PASSWORD_BCRYPT)) {
    $newHash = password_hash($password, PASSWORD_BCRYPT);
    // Update database with new hash
}
```

### Generate Password Hash for Testing
```bash
php database/generate-password-hash.php
```

---

## 🚨 Security Checklist

### Before Production Deployment

- [ ] **HTTPS Only:** Enforce SSL/TLS for all connections
- [ ] **Environment Variables:** Never commit `.env` to version control
- [ ] **Strong Passwords:** Increase minimum length to 8+ characters
- [ ] **Password Complexity:** Require uppercase, lowercase, numbers, symbols
- [ ] **Rate Limiting:** Implement login attempt throttling
- [ ] **Session Security:**
  - Set secure cookie flags (`httpOnly`, `secure`, `sameSite`)
  - Implement session timeout
  - Regenerate session ID on login
- [ ] **CSRF Protection:** Add CSRF tokens to forms
- [ ] **SQL Injection:** Use prepared statements (already implemented)
- [ ] **XSS Prevention:** Sanitize all user inputs
- [ ] **Password Reset:** Implement secure reset flow
- [ ] **Audit Logging:** Log authentication events
- [ ] **Regular Updates:** Keep PHP and dependencies updated

---

## 📖 Additional Resources

### PHP Password Functions
- [password_hash()](https://www.php.net/manual/en/function.password-hash.php)
- [password_verify()](https://www.php.net/manual/en/function.password-verify.php)
- [password_needs_rehash()](https://www.php.net/manual/en/function.password-needs-rehash.php)

### Security Standards
- [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [NIST Digital Identity Guidelines](https://pages.nist.gov/800-63-3/)

---

## 🐛 Troubleshooting

### "Invalid credentials" error with correct password

**Possible causes:**
1. Password hash in database is incorrect
2. Bcrypt algorithm mismatch
3. Password contains special characters not properly escaped

**Solution:**
```bash
# Generate new hash
php database/generate-password-hash.php

# Update database manually via phpMyAdmin or:
UPDATE users SET password = '$2y$12$gr36jBxy...' WHERE email = 'user@example.com';
```

### Password verification always fails

**Check:**
1. Verify hash starts with `$2y$` (bcrypt identifier)
2. Hash length is approximately 60 characters
3. Database field is `VARCHAR(255)` or larger
4. No whitespace or encoding issues in hash

**Debug:**
```php
var_dump([
    'input_password' => $password,
    'stored_hash' => $user['password'],
    'hash_length' => strlen($user['password']),
    'verification_result' => password_verify($password, $user['password'])
]);
```

---

**Last Updated:** December 25, 2025
**Version:** 2.0.0 (MySQL Migration + Security Documentation)
