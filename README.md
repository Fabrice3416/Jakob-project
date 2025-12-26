# JaKòb - Plateforme de Soutien aux Créateurs Haïtiens 🇭🇹

> Annou Sipòte Kilti Ayisyen - Support Haitian creators, artists, and changemakers directly.

## 🚀 Démarrage Rapide

1. **Ouvrir l'application**
   ```
   Ouvrir index.html dans un navigateur
   → Redirige automatiquement vers pages/auth/splash.html
   ```

2. **Base de données MySQL**
   - Copier `.env.example` vers `.env`
   - Configurer les identifiants MySQL dans `.env`
   - Installer via: `http://localhost:8000/database/install.php`
   - Ou manuellement: `mysql -u root -p jakob < database/schema.sql`

3. **Serveur PHP**
   ```bash
   cd C:\Users\brucy\OneDrive\Bureau\jakob-development
   php -S localhost:8000
   ```

## 📁 Structure

```
jakob-development/
├── index.html                 # Point d'entrée
├── .env                       # Configuration (à créer)
├── .env.example               # Template configuration
├── INDEX.md                   # Documentation complète
├── NAVIGATION.md              # Guide de navigation
├── SETUP.md                   # Guide d'installation
├── SECURITY.md                # Documentation sécurité
├── README.md                  # Ce fichier
│
├── api/                       # APIs backend
│   ├── register.php           # Inscription
│   ├── login.php              # Connexion
│   ├── logout.php             # Déconnexion
│   └── me.php                 # Données utilisateur
│
├── config/                    # Configuration
│   ├── database.php           # Connexion MySQL (PDO)
│   └── session.php            # Gestion sessions
│
├── database/                  # Base de données
│   ├── schema.sql             # Schéma MySQL complet
│   ├── install.php            # Installateur web
│   └── generate-password-hash.php
│
├── assets/
│   └── css/
│       └── design-system.js   # Design system unifié
│
└── pages/
    ├── auth/                  # 3 pages authentification
    ├── main/                  # 6 pages principales
    ├── user/                  # 3 pages utilisateur
    ├── creator/               # 1 page créateur
    └── error/                 # 2 pages d'erreur
```

**Total: 15 pages HTML** organisées et fonctionnelles

## 🎨 Design System

- **Couleur primaire**: #ea2a33 (Rouge haïtien)
- **Typographie**: Plus Jakarta Sans
- **Framework**: Tailwind CSS (via CDN)
- **Icônes**: Material Symbols Outlined
- **Mode**: Dark mode par défaut

## 🔗 Navigation

### Pages Principales
- 🏠 **Home**: `pages/main/home.html` - Dashboard avec catégories
- 🔍 **Explore**: `pages/main/explore.html` - Impact stories
- 💳 **Wallet**: `pages/user/wallet.html` - Portefeuille
- 👤 **Profile**: `pages/user/profile.html` - Profil & paramètres

### Flux de Don
```
Home/Explore → Creator Profile → Donation → Payment Success → Home
```

### Authentification
```
Splash → Login/Signup → Home
```

## 📚 Documentation

- **[INDEX.md](INDEX.md)** - Documentation complète du projet
- **[NAVIGATION.md](NAVIGATION.md)** - Guide détaillé de navigation
- **[SETUP.md](SETUP.md)** - Guide d'installation et configuration
- **[SECURITY.md](SECURITY.md)** - Sécurité et gestion des mots de passe
- **[design-system.js](assets/css/design-system.js)** - Système de design

## 🧪 Comptes de Test

Après installation de la base de données:

**Compte Donateur:**
- Téléphone: `+50912345678`
- Mot de passe: `password123`

**Compte Influenceur:**
- Téléphone: `+50987654321`
- Mot de passe: `password123`

## ✅ État d'Avancement

### Phase 1 & 2: Complétées ✅
- Système de design unifié
- Harmonisation de 6 fichiers
- Couleurs et typographie standardisées

### Phase 3: Complétée ✅
- 15 pages créées et organisées
- Structure de dossiers propre
- Navigation fonctionnelle
- Migration MySQL avec .env
- Système double utilisateurs (donateurs/influenceurs)
- APIs d'authentification complètes
- Hachage bcrypt des mots de passe
- Gestion de session sécurisée
- Documentation complète (INDEX, NAVIGATION, SETUP, SECURITY)

### Phase 4: À Venir 🎯
- Animations et interactions
- Empty states
- Pages additionnelles
- Intégrations MonCash/NatCash

## 🛠️ Technologies

- **Frontend**: HTML5, Tailwind CSS, JavaScript (Vanilla)
- **Backend**: PHP 8.5.1
- **Database**: MySQL 8.0+ (avec PDO)
- **Sécurité**: Bcrypt (PASSWORD_BCRYPT), Prepared Statements, Sessions PHP
- **Paiements**: MonCash, NatCash (à intégrer)

## 🇭🇹 Culture Haïtienne

L'application intègre:
- Kreyòl Ayisyen dans l'interface
- Patterns Taino décoratifs
- Couleurs du drapeau haïtien
- 5 catégories culturelles (Art, Musique, Éducation, Jeunesse, Patrimoine)

## 📞 Support

Pour toute question:
- Consulter [INDEX.md](INDEX.md) pour la documentation technique
- Voir [NAVIGATION.md](NAVIGATION.md) pour les flux utilisateur
- Vérifier [design-system.js](assets/css/design-system.js) pour le design

## 📝 Version

**2.0.0** - 25 décembre 2025
- Restructuration complète
- 15 pages organisées
- Navigation cohérente
- Documentation complète

---

**JaKòb** - Empowering Haitian Creators 🇭🇹❤️
