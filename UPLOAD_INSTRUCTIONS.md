# 📤 Instructions Détaillées - Upload .htaccess vers Hostinger

## ⚠️ Problème Actuel
Le site jakob.dev-dynamics.org retourne toujours une erreur 403 car le fichier `.htaccess` n'est pas sur le serveur Hostinger.

## ✅ Solution: Upload via hPanel

### Étape 1: Préparer le fichier
Le fichier `.htaccess` est ici sur votre ordinateur:
```
c:\Users\brucy\OneDrive\Bureau\jakob-development\.htaccess
```

### Étape 2: Se connecter à Hostinger
1. Ouvrez votre navigateur
2. Allez sur: https://hpanel.hostinger.com
3. Connectez-vous avec vos identifiants

### Étape 3: Ouvrir File Manager
1. Dans le panneau de gauche, cliquez sur **"Files"**
2. Puis cliquez sur **"File Manager"**
3. Une nouvelle fenêtre s'ouvre avec vos fichiers

### Étape 4: Activer l'affichage des fichiers cachés
1. En haut à droite du File Manager, cherchez l'icône d'engrenage ou "Settings"
2. Cochez **"Show Hidden Files"** (Afficher les fichiers cachés)
3. Cliquez "Save" ou "OK"

### Étape 5: Naviguer vers le bon dossier
1. Vous devriez voir un dossier nommé `public_html` ou `htdocs`
2. Double-cliquez dessus pour entrer
3. C'est ici que tous vos fichiers de site doivent être

### Étape 6: Uploader .htaccess
1. Cliquez sur le bouton **"Upload"** en haut
2. Une fenêtre de sélection de fichier s'ouvre
3. Naviguez vers: `C:\Users\brucy\OneDrive\Bureau\jakob-development`
4. **IMPORTANT:** Si vous ne voyez pas `.htaccess`, activez "Fichiers cachés" dans Windows:
   - Dans l'explorateur Windows, cliquez sur "Affichage"
   - Cochez "Éléments masqués"
5. Sélectionnez `.htaccess`
6. Si un fichier `.htaccess` existe déjà, cochez **"Overwrite"**
7. Cliquez "Upload"

### Étape 7: Vérification
1. Dans File Manager, vérifiez que `.htaccess` apparaît dans la liste
2. Clic droit sur `.htaccess` → Permissions
3. Assurez-vous que les permissions sont **644** (rw-r--r--)

### Étape 8: Tester
1. Attendez 2-3 minutes (cache du serveur)
2. Demandez à quelqu'un d'accéder à: https://jakob.dev-dynamics.org
3. L'erreur 403 devrait disparaître!

---

## 🔧 Alternative: Upload via FTP (Si hPanel ne fonctionne pas)

### Prérequis
Téléchargez FileZilla: https://filezilla-project.org/download.php?type=client

### Étape 1: Obtenir vos identifiants FTP
1. Dans hPanel, allez dans "Files" → "FTP Accounts"
2. Notez:
   - **Host:** ftp.jakob.dev-dynamics.org (ou l'adresse fournie)
   - **Username:** Votre nom d'utilisateur FTP
   - **Password:** Votre mot de passe FTP
   - **Port:** 21

### Étape 2: Se connecter avec FileZilla
1. Ouvrez FileZilla
2. En haut, remplissez:
   - Hôte: ftp.jakob.dev-dynamics.org
   - Identifiant: Votre username FTP
   - Mot de passe: Votre password FTP
   - Port: 21
3. Cliquez "Connexion rapide"

### Étape 3: Naviguer vers le bon dossier
- **Panneau de droite (serveur distant):** Double-cliquez sur `public_html`
- **Panneau de gauche (ordinateur local):** Naviguez vers `C:\Users\brucy\OneDrive\Bureau\jakob-development`

### Étape 4: Uploader
1. Dans le panneau de gauche, trouvez `.htaccess`
2. Faites un clic droit dessus → "Upload"
3. Ou glissez-déposez vers le panneau de droite

### Étape 5: Vérifier les permissions
1. Clic droit sur `.htaccess` dans le panneau de droite
2. "File permissions"
3. Entrez: **644**
4. OK

---

## ❓ Si le fichier .htaccess n'apparaît pas sur Windows

### Pour voir les fichiers cachés dans Windows Explorer:
1. Ouvrez l'explorateur de fichiers
2. Allez dans: `C:\Users\brucy\OneDrive\Bureau\jakob-development`
3. Cliquez sur l'onglet **"Affichage"** en haut
4. Cochez **"Éléments masqués"** ou **"Fichiers cachés"**
5. Le fichier `.htaccess` devrait maintenant apparaître

### Alternative: Copier le contenu
Si vous ne trouvez toujours pas `.htaccess`:

1. **Créez-le directement sur le serveur:**
   - Dans hPanel File Manager
   - Cliquez "New File"
   - Nom: `.htaccess` (avec le point au début)
   - Cliquez "Create"

2. **Copiez le contenu:**
   - Ouvrez le fichier que je viens de créer dans VS Code
   - Copiez TOUT le contenu (Ctrl+A, Ctrl+C)

3. **Éditez sur le serveur:**
   - Dans File Manager, clic droit sur `.htaccess` → "Edit"
   - Collez le contenu (Ctrl+V)
   - Sauvegardez

---

## 🎯 Contenu du fichier .htaccess

Voici le contenu complet à copier si nécessaire:

```apache
# JaKòb Platform - Hostinger/LiteSpeed Configuration

# ============================================
# PERMISSIONS - RÉSOUDRE ERREUR 403
# ============================================
# Autoriser l'accès depuis toutes les IPs
Order allow,deny
Allow from all

# Alternative pour Apache 2.4+
<IfModule mod_authz_core.c>
    Require all granted
</IfModule>

# ============================================
# REWRITE ENGINE
# ============================================
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Ne pas réécrire les fichiers réels et les dossiers
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
</IfModule>

# ============================================
# DIRECTORY INDEX
# ============================================
DirectoryIndex index.html index.php

# ============================================
# CORS HEADERS (pour les APIs)
# ============================================
<IfModule mod_headers.c>
    # Autoriser les requêtes cross-origin
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"

    # Headers de sécurité
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# ============================================
# MIME TYPES
# ============================================
<IfModule mod_mime.c>
    AddType application/javascript .js
    AddType text/css .css
    AddType image/svg+xml .svg
    AddType application/json .json
    AddType font/woff2 .woff2
    AddType font/woff .woff
    AddType font/ttf .ttf
</IfModule>

# ============================================
# PROTECTION DES FICHIERS SENSIBLES
# ============================================
# Bloquer l'accès aux fichiers de configuration
<FilesMatch "^(\.env|\.htaccess|\.htpasswd|\.git|config\.php|database\.php)$">
    Order allow,deny
    Deny from all
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
</FilesMatch>

# ============================================
# PERFORMANCES - COMPRESSION
# ============================================
<IfModule mod_deflate.c>
    # Compresser le texte, HTML, CSS, JavaScript, XML
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE image/svg+xml
</IfModule>

# ============================================
# PERFORMANCES - CACHE
# ============================================
<IfModule mod_expires.c>
    ExpiresActive On

    # Images
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType image/x-icon "access plus 1 year"

    # CSS et JavaScript
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"

    # Fonts
    ExpiresByType font/woff2 "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/ttf "access plus 1 year"

    # HTML et données
    ExpiresByType text/html "access plus 0 seconds"
    ExpiresByType application/json "access plus 0 seconds"
    ExpiresByType application/xml "access plus 0 seconds"
</IfModule>

# ============================================
# SÉCURITÉ - DÉSACTIVER DIRECTORY BROWSING
# ============================================
Options -Indexes

# ============================================
# SÉCURITÉ - LIMITER LES UPLOADS
# ============================================
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value max_input_time 300
</IfModule>

# ============================================
# PAGES D'ERREUR PERSONNALISÉES
# ============================================
ErrorDocument 403 /pages/error/403.html
ErrorDocument 404 /pages/error/404.html
ErrorDocument 500 /pages/error/500.html

# ============================================
# LITESPEED SPECIFIC (pour Hostinger)
# ============================================
<IfModule LiteSpeed>
    # Cache pour les pages statiques
    CacheLookup on

    # Ne pas cacher les pages dynamiques (PHP, API)
    <FilesMatch "\.php$">
        CacheLookup off
    </FilesMatch>
</IfModule>
```

---

## 📞 Besoin d'Aide?

Si vous êtes bloqué:
1. Prenez une capture d'écran de l'erreur
2. Contactez le support Hostinger via le chat dans hPanel
3. Dites-leur: "Je reçois une erreur 403, j'ai besoin d'aide pour uploader mon fichier .htaccess"

**Support Hostinger:** Disponible 24/7 dans hPanel (icône de chat en bas à droite)
