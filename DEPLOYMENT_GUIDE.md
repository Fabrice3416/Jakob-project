# Guide de Déploiement - JaKòb Platform

## Problème Actuel
Les utilisateurs externes reçoivent une erreur 403 en accédant à **jakob.dev-dynamics.org**

## Cause
Le fichier `.htaccess` avec les bonnes permissions n'est pas encore uploadé sur le serveur Hostinger.

## Solution: Uploader les Fichiers sur Hostinger

### Méthode 1: Via hPanel (Interface Hostinger)

1. **Connectez-vous à Hostinger**
   - Allez sur https://hpanel.hostinger.com
   - Connectez-vous avec vos identifiants

2. **Accédez au File Manager**
   - Dans le menu, cliquez sur "Files" → "File Manager"
   - Naviguez vers le dossier `public_html` ou le dossier racine de votre site

3. **Uploadez le fichier .htaccess**
   - Cliquez sur "Upload Files"
   - Sélectionnez le fichier `.htaccess` depuis votre ordinateur:
     ```
     c:\Users\brucy\OneDrive\Bureau\jakob-development\.htaccess
     ```
   - Si un fichier .htaccess existe déjà, remplacez-le

4. **Vérifiez les permissions**
   - Clic droit sur `.htaccess` → Permissions
   - Définissez les permissions à **644** (rw-r--r--)

### Méthode 2: Via FTP (FileZilla)

1. **Téléchargez FileZilla** (si pas installé)
   - https://filezilla-project.org/download.php?type=client

2. **Récupérez vos identifiants FTP**
   - Dans hPanel: "Files" → "FTP Accounts"
   - Notez: Host, Username, Password, Port

3. **Connectez-vous via FTP**
   - Ouvrez FileZilla
   - Host: `ftp.jakob.dev-dynamics.org`
   - Username: Votre username FTP
   - Password: Votre mot de passe FTP
   - Port: 21

4. **Uploadez .htaccess**
   - Fenêtre gauche: Naviguez vers `c:\Users\brucy\OneDrive\Bureau\jakob-development\`
   - Fenêtre droite: Naviguez vers `public_html`
   - Glissez-déposez `.htaccess` vers le serveur

### Méthode 3: Via Git + SSH (Avancé)

Si vous avez configuré Git sur Hostinger:

```bash
# Dans votre terminal local
cd "c:\Users\brucy\OneDrive\Bureau\jakob-development"

# Assurez-vous que .htaccess est dans le commit
git add .htaccess
git commit -m "fix: add .htaccess to resolve 403 errors"

# Push vers le serveur
git push origin main
```

## Vérification

Après avoir uploadé le .htaccess, testez:

1. **Test depuis un autre appareil**
   - Demandez à quelqu'un d'accéder à https://jakob.dev-dynamics.org
   - Ils ne devraient plus avoir d'erreur 403

2. **Test avec server-check.php**
   - Uploadez aussi `server-check.php` sur le serveur
   - Accédez à https://jakob.dev-dynamics.org/server-check.php
   - Vérifiez que tout est vert

3. **Test des pages principales**
   - https://jakob.dev-dynamics.org/pages/auth/login.html
   - https://jakob.dev-dynamics.org/pages/main/home.html

## Si l'erreur 403 persiste

### Option 1: Vérifier les permissions des dossiers
Assurez-vous que tous les dossiers ont les bonnes permissions via hPanel:
- Dossiers: **755** (rwxr-xr-x)
- Fichiers: **644** (rw-r--r--)

### Option 2: Contacter le support Hostinger
Si le problème persiste, il peut y avoir des restrictions au niveau du compte:

1. Ouvrez un ticket support sur https://hpanel.hostinger.com
2. Mentionnez:
   ```
   Sujet: Erreur 403 sur jakob.dev-dynamics.org

   Message:
   Bonjour,

   Mon site jakob.dev-dynamics.org retourne une erreur 403 pour les visiteurs externes.
   J'ai vérifié:
   - Le fichier .htaccess contient "Require all granted"
   - Les permissions sont correctes (755/644)

   Pourriez-vous vérifier s'il y a des restrictions au niveau du serveur?

   Merci!
   ```

### Option 3: Alternative .htaccess simplifié
Si le .htaccess actuel ne fonctionne pas, essayez cette version simplifiée:

```apache
# Minimal .htaccess for Hostinger
Order allow,deny
Allow from all

DirectoryIndex index.html index.php

<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Fichiers à Uploader sur le Serveur

Voici la liste des fichiers/dossiers à synchroniser:

```
jakob-development/
├── .htaccess                    ← IMPORTANT!
├── server-check.php            ← Pour diagnostics
├── api/                        ← Tous les fichiers API
├── assets/                     ← CSS, JS, images
├── config/                     ← Configuration (sauf database.php avec mdp local)
├── pages/                      ← Toutes les pages HTML
└── database/                   ← Migrations SQL
```

⚠️ **NE PAS uploader:**
- `.env` avec vos identifiants locaux
- `.git/` (dossier Git)
- `.vscode/` (configuration VS Code)
- `.claude/` (configuration Claude)
- `node_modules/` (si vous avez des dépendances npm)

## Configuration de la Base de Données sur Hostinger

1. **Créez une base de données MySQL**
   - Dans hPanel: "Databases" → "MySQL Databases"
   - Créez une nouvelle base: `jakob_db`
   - Créez un utilisateur avec tous les privilèges

2. **Mettez à jour config/database.php sur le serveur**
   Utilisez les identifiants fournis par Hostinger:
   ```php
   define('DB_HOST', 'localhost'); // ou l'hôte fourni par Hostinger
   define('DB_NAME', 'u123456_jakob_db'); // nom avec préfixe
   define('DB_USER', 'u123456_jakob');
   define('DB_PASSWORD', 'votre_mot_de_passe');
   ```

3. **Importez la base de données**
   - Dans hPanel: "Databases" → "phpMyAdmin"
   - Sélectionnez votre base de données
   - Cliquez sur "Import"
   - Uploadez votre fichier SQL de dump

## Résumé des Actions Immédiates

✅ **À FAIRE MAINTENANT:**

1. Uploadez `.htaccess` sur Hostinger (Méthode 1 recommandée)
2. Vérifiez les permissions: 644 pour .htaccess
3. Testez depuis un autre appareil ou demandez à quelqu'un de tester
4. Si OK, uploadez `server-check.php` et vérifiez
5. Une fois confirmé, supprimez `server-check.php` (sécurité)

---

**Besoin d'aide?** Contactez le support Hostinger ou envoyez-moi plus de détails sur l'erreur.
