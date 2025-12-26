# JaKòb - Guide de Dépannage

## ❌ Erreur: "SyntaxError: Unexpected token 'D', Database C... is not valid JSON"

### 🔍 Diagnostic Complet

Cette erreur survient quand l'API `register.php` retourne du **texte brut** au lieu de **JSON**, causant une erreur de parsing dans JavaScript.

---

## 📋 TOUTES LES CAUSES POSSIBLES IDENTIFIÉES

### ✅ 1. **Problème dans config/database.php** (CAUSE PRINCIPALE - CORRIGÉE)

**Symptôme:** Le fichier utilisait `die()` qui envoyait du texte brut au lieu de JSON.

**Code problématique:**
```php
// Lignes 10, 85, 87, 96 - AVANT
if (!file_exists($path)) {
    die("Error: .env file not found at: $path");  // ❌ Texte brut!
}

if (getenv('APP_DEBUG') === 'true') {
    die("Database Connection Error: " . $e->getMessage());  // ❌ Texte brut!
}

die("Failed to connect to database: " . $e->getMessage());  // ❌ Texte brut!
```

**Solution appliquée:**
```php
// APRÈS - Lance des exceptions au lieu de die()
if (!file_exists($path)) {
    throw new Exception("Configuration file not found");  // ✅ Exception!
}

throw new Exception("Database connection failed: " . $e->getMessage());  // ✅ Exception!
```

---

### ✅ 2. **Exécution automatique de code** (CORRIGÉE)

**Problème:** Les lignes 93-99 exécutaient automatiquement la connexion.

**Code problématique:**
```php
// AVANT - S'exécute dès que le fichier est include
try {
    $pdo = getDbConnection();  // ❌ S'exécute immédiatement!
} catch (Exception $e) {
    die("Failed to connect to database: " . $e->getMessage());  // ❌ Texte brut!
}
return $pdo;
```

**Solution appliquée:**
```php
// APRÈS - Ne s'exécute PAS automatiquement
// DO NOT auto-execute connection - let the calling script handle it
// This prevents die() from being called when the file is included
```

---

### ✅ 3. **Nom de base de données incorrect** (CORRIGÉE)

**Problème:** `.env` spécifiait `DB_NAME=jakob` mais la base réelle était `jakob_db`

**Solution appliquée:**
```env
# .env - Ligne 6
DB_NAME=jakob_db  # ✅ Corrigé
```

---

### 🔄 4. **Cache PHP ou Navigateur** (POSSIBLE)

**Symptôme:** Même après correction, l'erreur persiste.

**Solutions:**
1. **Redémarrer le serveur PHP:**
   ```bash
   # Arrêter le serveur (Ctrl+C dans le terminal)
   # Puis redémarrer:
   php -S localhost:8000
   ```

2. **Vider le cache du navigateur:**
   - Chrome: `Ctrl+Shift+Delete`
   - Ou ouvrir en mode incognito: `Ctrl+Shift+N`

3. **Hard refresh:**
   - `Ctrl+F5` (Windows)
   - `Cmd+Shift+R` (Mac)

---

### 🔍 5. **BOM (Byte Order Mark)**

**Symptôme:** Caractères invisibles avant `<?php`

**Vérification:**
```bash
# Ouvrir les fichiers avec un éditeur qui affiche le BOM
# Ou utiliser:
file config/database.php
```

**Solution:**
- Sauvegarder les fichiers PHP en **UTF-8 sans BOM**
- Dans VS Code: `File > Save with Encoding > UTF-8`

---

### 🔍 6. **Espaces ou texte avant <?php**

**Symptôme:** Espaces blancs avant la balise d'ouverture PHP

**Vérification:**
```php
// ❌ MAUVAIS - Espace avant <?php
 <?php

// ✅ BON - Pas d'espace
<?php
```

**Solution:**
- S'assurer que `<?php` est à la toute première ligne
- Pas d'espace, pas de ligne vide avant

---

### 🔍 7. **Erreurs PHP affichées**

**Symptôme:** Warnings ou notices PHP s'affichent avant le JSON

**Vérification:**
```php
// Ajouter en haut de register.php (temporairement)
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Solution permanente:**
```php
// En production, désactiver l'affichage des erreurs
error_reporting(0);
ini_set('display_errors', 0);
```

---

### 🔍 8. **Headers déjà envoyés**

**Symptôme:** "Cannot modify header information - headers already sent"

**Causes:**
- Texte avant `<?php`
- `echo` ou `print` avant `header()`
- Espaces après `?>`

**Solution:**
```php
// En haut de register.php - AVANT tout output
header('Content-Type: application/json; charset=utf-8');
```

---

### 🔍 9. **Problème de buffering**

**Symptôme:** Output buffer non vidé

**Solution:**
```php
// En haut de register.php
ob_start();

// À la fin
ob_end_clean();
echo json_encode($response);
```

---

### 🔍 10. **Chemin d'API incorrect**

**Symptôme:** 404 ou fichier non trouvé

**Vérification dans signup.html:**
```javascript
// Ligne 215 - Vérifier le chemin
const response = await fetch('../../api/register.php', {
```

**Solution:**
- Depuis `pages/auth/signup.html`
- Chemin correct: `../../api/register.php`
- Vérifier que le fichier existe

---

## 🛠️ OUTILS DE DIAGNOSTIC

### 1. **Script de test complet**

Ouvrir dans le navigateur: `http://localhost:8000/api/test-debug.php`

Ce script vérifie:
- ✅ Fichier .env existe
- ✅ Variables d'environnement chargées
- ✅ Connexion MySQL
- ✅ Base de données accessible
- ✅ Tables présentes
- ✅ Hachage de mot de passe
- ✅ Headers HTTP
- ✅ Output JSON

### 2. **Test via console navigateur**

```javascript
// Ouvrir la console (F12) et exécuter:
fetch('http://localhost:8000/api/register.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        user_type: 'donor',
        email: 'test@example.com',
        phone: '+50912345678',
        password: 'password123'
    })
})
.then(res => res.text())
.then(text => {
    console.log('Raw response:', text);
    try {
        const json = JSON.parse(text);
        console.log('Parsed JSON:', json);
    } catch(e) {
        console.error('JSON parse error:', e);
        console.error('Response is not valid JSON');
    }
});
```

### 3. **Test via curl**

```bash
curl -X POST http://localhost:8000/api/register.php \
  -H "Content-Type: application/json" \
  -d '{"user_type":"donor","email":"test@example.com","phone":"+50912345678","password":"password123"}' \
  -v
```

---

## ✅ VÉRIFICATION POST-CORRECTION

### 1. Redémarrer le serveur PHP
```bash
# Arrêter (Ctrl+C)
php -S localhost:8000
```

### 2. Vider le cache navigateur
- `Ctrl+Shift+Delete`
- Cocher "Cached images and files"
- Cliquer "Clear data"

### 3. Tester l'inscription
- Ouvrir `http://localhost:8000/pages/auth/signup.html`
- Remplir le formulaire
- Soumettre
- Vérifier la console (F12) pour les erreurs

### 4. Vérifier la base de données
```sql
-- Dans phpMyAdmin
SELECT * FROM users ORDER BY id DESC LIMIT 5;
SELECT * FROM donors ORDER BY id DESC LIMIT 5;
SELECT * FROM influencers ORDER BY id DESC LIMIT 5;
```

---

## 📊 RÉSUMÉ DES CORRECTIONS APPLIQUÉES

| # | Problème | Fichier | Statut |
|---|----------|---------|--------|
| 1 | `die()` au lieu d'exceptions | `config/database.php` | ✅ Corrigé |
| 2 | Auto-exécution de connexion | `config/database.php` | ✅ Corrigé |
| 3 | Nom de base de données | `.env` | ✅ Corrigé |
| 4 | VARCHAR trop longs | `database/schema.sql` | ✅ Corrigé |
| 5 | Hachage bcrypt | `database/schema.sql` | ✅ Corrigé |

---

## 🚀 PROCHAINES ÉTAPES

1. **Redémarrer le serveur PHP**
2. **Vider le cache navigateur**
3. **Tester l'inscription** sur `signup.html`
4. **Vérifier les logs** du serveur PHP
5. **Consulter** `api/test-debug.php` si problème persiste

---

## 📞 SUPPORT

Si l'erreur persiste après toutes ces corrections:

1. Vérifier les logs du serveur PHP dans le terminal
2. Ouvrir la console navigateur (F12) > Network > Voir la réponse brute
3. Exécuter `api/test-debug.php` pour un diagnostic complet
4. Partager les messages d'erreur exacts

---

**Date:** 25 décembre 2025
**Version:** 2.0.1 - Corrections JSON API
**Statut:** ✅ Problème identifié et corrigé
