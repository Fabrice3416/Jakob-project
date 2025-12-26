# 🔐 Corriger les Permissions - Erreur 403 Persistante

## Diagnostic
✅ Le .htaccess est bien uploadé et actif (headers CORS visibles)
❌ Erreur 403 persiste → Problème de permissions de fichiers/dossiers

## Solution 1: Corriger les Permissions via hPanel

### Étape 1: Se connecter
1. Allez sur https://hpanel.hostinger.com
2. Connectez-vous
3. Files → File Manager

### Étape 2: Vérifier les permissions du dossier public_html
1. Clic droit sur le dossier `public_html`
2. Sélectionnez **"Permissions"** ou **"Change Permissions"**
3. Définissez à **755** (rwxr-xr-x)
   - Owner: Read, Write, Execute (7)
   - Group: Read, Execute (5)
   - Public: Read, Execute (5)
4. **IMPORTANT:** Cochez **"Apply to subdirectories"** (Appliquer aux sous-dossiers)
5. Cliquez "Save"

### Étape 3: Créer un fichier index.html simple
Si vous n'avez pas de fichier `index.html` à la racine:

1. Dans File Manager, allez dans `public_html`
2. Cliquez "New File"
3. Nom: `index.html`
4. Éditez le fichier et ajoutez:
```html
<!DOCTYPE html>
<html>
<head>
    <title>JaKòb - Welcome</title>
</head>
<body>
    <h1>JaKòb Platform</h1>
    <p>Site en construction...</p>
    <p><a href="/pages/auth/login.html">Login</a></p>
    <p><a href="/pages/main/home.html">Home</a></p>
</body>
</html>
```
5. Sauvegardez
6. Testez: https://jakob.dev-dynamics.org

---

## Solution 2: Vérifier la Configuration du Domaine

### Dans hPanel:
1. Allez dans **"Websites"** (menu gauche)
2. Trouvez **jakob.dev-dynamics.org**
3. Cliquez sur **"Manage"**
4. Vérifiez:
   - **Document Root:** Doit être `/public_html` ou le bon dossier
   - **PHP Version:** Doit être actif (7.4+ recommandé)
   - **SSL:** Doit être actif si vous utilisez HTTPS

### Changer le Document Root si nécessaire:
1. Dans la gestion du site
2. Cherchez **"Change website root"** ou **"Document root"**
3. Assurez-vous que c'est `/public_html` et non un sous-dossier

---

## Solution 3: Contacter le Support Hostinger

Si les solutions ci-dessus ne marchent pas, le support doit débloquer quelque chose.

### Message à envoyer au support:
```
Sujet: Erreur 403 Forbidden persistante sur jakob.dev-dynamics.org

Bonjour,

Mon site jakob.dev-dynamics.org retourne une erreur 403 Forbidden pour tous les visiteurs.

J'ai déjà:
✅ Uploadé un fichier .htaccess avec "Require all granted" et "Allow from all"
✅ Vérifié que le .htaccess est actif (les headers CORS sont appliqués)
✅ Vérifié les permissions des fichiers (755/644)
✅ Créé un fichier index.html à la racine

Malgré cela, l'erreur 403 persiste.

Pouvez-vous vérifier s'il y a:
1. Des restrictions au niveau du compte
2. Un problème avec la configuration du domaine
3. Des règles de firewall qui bloquent l'accès

Merci de votre aide!
```

**Comment contacter le support:**
1. Dans hPanel, cliquez sur l'icône de **chat** en bas à droite
2. OU allez dans "Help" → "Contact support"
3. Le support est disponible 24/7

---

## Solution 4: Vérifier s'il y a un autre .htaccess

Parfois, il y a plusieurs fichiers .htaccess qui se contredisent:

### Dans File Manager:
1. Activez "Show Hidden Files"
2. Cherchez s'il y a un `.htaccess` dans:
   - La racine du compte (au-dessus de public_html)
   - Dans des sous-dossiers (pages/, api/, etc.)
3. Si vous en trouvez d'autres, vérifiez leur contenu
4. Supprimez ceux qui ne sont pas nécessaires

---

## Solution 5: Créer un .htaccess minimal

Remplacez votre .htaccess actuel par cette version ultra-simple:

```apache
# Configuration minimale
Options +FollowSymLinks -Indexes
DirectoryIndex index.html index.php

# Permissions
<IfModule mod_authz_core.c>
    Require all granted
</IfModule>

Order allow,deny
Allow from all
```

Si cette version fonctionne, ajoutez les autres règles une par une pour identifier le problème.

---

## Tests de Diagnostic

### Test 1: Créer un fichier test.html
1. Dans public_html, créez `test.html`
2. Contenu simple:
```html
<!DOCTYPE html>
<html><body><h1>Test OK</h1></body></html>
```
3. Testez: https://jakob.dev-dynamics.org/test.html
4. Si ça marche → Le problème est avec index.html ou le routing
5. Si ça ne marche pas → Le problème est plus profond (permissions/config)

### Test 2: Créer un fichier test.php
1. Dans public_html, créez `test.php`
2. Contenu:
```php
<?php
echo "PHP fonctionne!";
phpinfo();
?>
```
3. Testez: https://jakob.dev-dynamics.org/test.php
4. Vérifiez si PHP fonctionne

### Test 3: Vérifier les logs
Dans hPanel:
1. Allez dans "Statistics" → "Error Logs"
2. Cherchez les erreurs récentes liées à jakob.dev-dynamics.org
3. Partagez les messages d'erreur avec le support si nécessaire

---

## Checklist Rapide

Cochez ce que vous avez fait:

- [ ] Fichier .htaccess uploadé dans public_html
- [ ] Permissions du dossier public_html: 755
- [ ] Permissions des fichiers HTML: 644
- [ ] Un fichier index.html existe à la racine
- [ ] Document Root configuré sur /public_html
- [ ] PHP activé sur le domaine
- [ ] Pas d'autres .htaccess conflictuels
- [ ] Test avec test.html simple
- [ ] Vérifié les error logs
- [ ] Contacté le support si rien ne fonctionne

---

## Prochaine Étape

**ACTION IMMÉDIATE:**
1. Créez un fichier `index.html` simple dans public_html
2. Testez https://jakob.dev-dynamics.org
3. Si ça ne marche toujours pas → **Contactez le support Hostinger** avec le message ci-dessus

Le support pourra voir des choses que vous ne pouvez pas voir (restrictions de compte, firewall, etc.)
