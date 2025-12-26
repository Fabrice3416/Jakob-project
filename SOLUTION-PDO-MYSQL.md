# ✅ SOLUTION - Erreur PDO MySQL Driver

## 🔴 Problème Identifié

**Erreur:** `Database connection failed: could not find driver`

**Cause:** L'extension PDO MySQL n'était pas activée dans PHP.

---

## 🔍 Diagnostic Effectué

### 1. **Erreur JSON** (Résolue précédemment)
```
SyntaxError: Unexpected token 'D', "Database C... is not valid JSON"
```
- **Cause:** `config/database.php` utilisait `die()` au lieu de `throw new Exception()`
- **Solution:** Modifié pour lancer des exceptions ✅

### 2. **Erreur 400 Bad Request** (Problème actuel)
```
POST http://localhost:8000/api/register.php 400 (Bad Request)
```
- **Test avec curl:**
```bash
curl -X POST http://localhost:8000/api/register.php \
  -H "Content-Type: application/json" \
  -d '{"user_type":"donor","email":"test@example.com","phone":"+50912345678","password":"password123"}'
```
- **Résultat:**
```json
{"success":false,"message":"Database connection failed: could not find driver"}
```

### 3. **Vérification des Extensions PHP**
```bash
php -m | grep pdo
```
**Résultat:**
```
PDO
pdo_pgsql
```
❌ **Manque:** `pdo_mysql`

---

## ✅ SOLUTION APPLIQUÉE

### Étape 1: Localiser php.ini
```bash
php --ini
```
**Résultat:** `C:\php\php.ini`

### Étape 2: Activer les Extensions MySQL

**Fichier:** `C:\php\php.ini`

**Modifications effectuées:**

1. **Ligne 928** - Activer MySQLi:
```ini
# AVANT
;extension=mysqli

# APRÈS
extension=mysqli
```

2. **Ligne 932** - Activer PDO MySQL:
```ini
# AVANT
;extension=pdo_mysql

# APRÈS
extension=pdo_mysql
```

### Étape 3: Redémarrer le Serveur PHP

**IMPORTANT:** Les modifications de `php.ini` ne sont appliquées qu'après redémarrage du serveur PHP.

#### Dans le terminal où PHP tourne:
1. Appuyer sur **Ctrl+C** pour arrêter le serveur
2. Redémarrer avec:
```bash
cd C:\Users\brucy\OneDrive\Bureau\jakob-development
php -S localhost:8000
```

### Étape 4: Vérifier l'Activation

**Option 1:** Script de test créé
```
http://localhost:8000/test-pdo.php
```
Ce script vérifie:
- ✅ Extension PDO chargée
- ✅ Driver PDO MySQL disponible
- ✅ Connexion à la base de données
- ✅ Liste des tables
- ✅ Version MySQL

**Option 2:** Ligne de commande
```bash
php -m | grep -i mysql
```
**Résultat attendu:**
```
mysqli
pdo_mysql
```

---

## 📋 RÉCAPITULATIF DES FICHIERS MODIFIÉS

| Fichier | Modification | Statut |
|---------|--------------|--------|
| `C:\php\php.ini` (ligne 928) | `extension=mysqli` | ✅ Activé |
| `C:\php\php.ini` (ligne 932) | `extension=pdo_mysql` | ✅ Activé |
| `config/database.php` | Exceptions au lieu de `die()` | ✅ Corrigé |
| `.env` (ligne 6) | `DB_NAME=jakob_db` | ✅ Corrigé |

---

## 🧪 TESTS À EFFECTUER

### 1. Vérifier les Extensions PDO
```
http://localhost:8000/test-pdo.php
```
**Attendu:**
- ✅ PDO extension is loaded
- ✅ PDO MySQL driver is loaded
- ✅ MySQLi extension is loaded
- ✅ Database connection successful
- ✅ Connected to database: jakob_db

### 2. Tester l'API Register
```bash
curl -X POST http://localhost:8000/api/register.php \
  -H "Content-Type: application/json" \
  -d '{"user_type":"donor","email":"newuser@example.com","phone":"+50912340000","password":"password123"}'
```
**Attendu (succès):**
```json
{
  "success": true,
  "message": "Account created successfully",
  "data": {
    "user_id": 3,
    "user_type": "donor",
    "email": "newuser@example.com"
  }
}
```

### 3. Tester via l'Interface Web
1. Ouvrir: `http://localhost:8000/pages/auth/signup.html`
2. Sélectionner type d'utilisateur (Donor ou Influencer)
3. Remplir le formulaire:
   - Email: `nouvelutilisateur@example.com`
   - Téléphone: `+50912340001`
   - Mot de passe: `password123`
4. Soumettre
5. **Attendu:** Message de succès et redirection

### 4. Vérifier dans phpMyAdmin
```sql
SELECT * FROM users ORDER BY id DESC LIMIT 5;
SELECT * FROM donors ORDER BY id DESC LIMIT 5;
```

---

## 🚨 SI LE PROBLÈME PERSISTE

### 1. Vérifier que le serveur a été redémarré
- Arrêter avec `Ctrl+C`
- Relancer: `php -S localhost:8000`

### 2. Vérifier que php.ini a bien été modifié
```bash
php -i | grep "Configuration File"
php -m | grep -i mysql
```

### 3. Vérifier les chemins d'extensions
Dans `php.ini`, chercher:
```ini
extension_dir = "ext"
```
S'assurer que le dossier `C:\php\ext` existe et contient:
- `php_mysqli.dll`
- `php_pdo_mysql.dll`

### 4. Vider le cache du navigateur
- `Ctrl+Shift+Delete`
- Ou ouvrir en mode navigation privée

### 5. Consulter les logs du serveur PHP
- Le terminal où PHP tourne affiche les requêtes
- Chercher les codes d'erreur (200, 400, 500)

---

## 📊 CHRONOLOGIE DES CORRECTIONS

| Date | Problème | Solution | Statut |
|------|----------|----------|--------|
| 25 déc | VARCHAR > 767 bytes | Réduit à VARCHAR(191) | ✅ |
| 25 déc | Hachage mot de passe | Ajouté bcrypt | ✅ |
| 25 déc | DB name mismatch | `.env` DB_NAME=jakob_db | ✅ |
| 25 déc | `die()` dans config | Exceptions | ✅ |
| 25 déc | PDO MySQL manquant | Activé dans php.ini | ✅ |

---

## ✅ PROCHAINES ÉTAPES

Une fois que `test-pdo.php` confirme que tout fonctionne:

1. **Tester l'inscription complète** sur signup.html
2. **Tester la connexion** sur login.html avec:
   - Email: `marie@example.com`
   - Password: `password123`
3. **Vérifier les redirections** selon le type d'utilisateur
4. **Documenter** les comptes de test créés

---

## 📞 AIDE SUPPLÉMENTAIRE

### Extensions PHP requises pour JaKòb:
- ✅ `pdo` - Base PDO
- ✅ `pdo_mysql` - Driver MySQL pour PDO
- ✅ `mysqli` - Extension MySQLi (optionnelle mais recommandée)
- ✅ `json` - Manipulation JSON (généralement activé par défaut)
- ✅ `session` - Gestion des sessions (activé par défaut)

### Commandes utiles:
```bash
# Lister toutes les extensions chargées
php -m

# Chercher une extension spécifique
php -m | grep -i mysql

# Voir la configuration complète
php -i

# Tester un fichier PHP
php -f test-pdo.php
```

---

**Date:** 25 décembre 2025
**Version:** 2.0.2 - Activation PDO MySQL
**Statut:** ✅ Extensions activées - Redémarrage requis
**Prochain test:** `http://localhost:8000/test-pdo.php`
