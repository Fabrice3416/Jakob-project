# JaKòb - Documentation Complète

## 📱 Structure du Projet

```
jakob-development/
├── index.html                  # Point d'entrée (redirect vers splash)
├── db.php                      # Configuration base de données
├── test-db.php                 # Tests de connexion PostgreSQL
├── INDEX.md                    # Ce fichier - Documentation complète
├── NAVIGATION.md              # Guide de navigation détaillé
│
├── api/
│   └── inscription.php         # API inscription donateur
│
├── assets/
│   └── css/
│       └── design-system.js    # Système de design unifié
│
└── pages/
    ├── auth/                   # Authentification
    │   ├── splash.html         # Onboarding/Welcome
    │   ├── login.html          # Connexion
    │   └── signup.html         # Inscription donateur
    │
    ├── main/                   # Pages principales
    │   ├── home.html           # Dashboard principal
    │   ├── explore.html        # Impact stories & découverte
    │   ├── creator-profile.html # Profil créateur détaillé
    │   ├── campaign-details.html # Détails campagne
    │   ├── donation.html       # Sélection montant don
    │   └── payment-success.html # Confirmation paiement
    │
    ├── user/                   # Profil utilisateur
    │   ├── profile.html        # Profil & paramètres
    │   ├── wallet.html         # Portefeuille détaillé
    │   └── notifications.html  # Centre notifications
    │
    ├── creator/                # Gestion créateur
    │   └── my-campaigns.html   # Mes campagnes/projets
    │
    └── error/                  # Pages d'erreur
        ├── 404.html            # Page non trouvée
        └── offline.html        # Pas de connexion
```

**Total**: 15 pages HTML organisées

---

## 🎨 Système de Design Unifié

### Couleurs Principales
```css
{
  "primary": "#ea2a33",        /* Rouge haïtien */
  "primary-dark": "#c91b24",   /* Hover states */
  "accent": "#f7c59f",         /* Beige/Doré */
  "background-dark": "#211111", /* Fond principal */
  "surface-dark": "#2f1a1b",   /* Cartes/surfaces */
  "card-dark": "#382020",      /* Cartes alternatives */
  "text-muted": "#c9a092"      /* Texte secondaire */
}
```

### Typographie
- **Font**: Plus Jakarta Sans (400, 500, 600, 700, 800)
- **Icônes**: Material Symbols Outlined (filled variant)

### Composants Standardisés
- **Navigation Bottom**: Floating rounded-full avec 4 icônes
- **Buttons**: Primary (bg-primary), Secondary (bg-white/5), Ghost
- **Cards**: Surface-dark avec border-white/10, rounded-2xl
- **Inputs**: Rounded-xl avec focus-ring primary

---

## 🔗 Navigation & Flux

### Flux d'Authentification
```
index.html → pages/auth/splash.html
                ↓
        [User Choice]
        ├─→ Get Started → signup.html → home.html
        ├─→ Sign In → login.html → home.html
        └─→ Explore as Guest → home.html
```

### Navigation Principale (Bottom Nav)
Présente sur toutes les pages principales avec 4 liens:
1. 🏠 **Home** → `pages/main/home.html`
2. 🔍 **Explore** → `pages/main/explore.html`
3. 💳 **Wallet** → `pages/user/wallet.html`
4. 👤 **Profile** → `pages/user/profile.html`

### Parcours de Don
```
home.html / explore.html
    ↓
creator-profile.html (Voir profil créateur)
    ↓
donation.html (Choisir montant)
    ↓
payment-success.html (Confirmation)
    ↓
home.html (Retour accueil)
```

### Gestion Utilisateur
```
profile.html (Hub paramètres)
    ├→ wallet.html (Gérer portefeuille)
    ├→ notifications.html (Voir notifications)
    ├→ Personal Info (à créer)
    └→ Settings (à créer)
```

---

## 📄 Description des Pages

### 🔐 Auth (3 pages)

**splash.html** - Page d'accueil
- 3 features highlights (Direct Support, Community, Transparent)
- CTA: Get Started, Explore as Guest, Sign In
- Animations: floating background elements, pulse effects

**login.html** - Connexion
- Phone + Password inputs
- Password visibility toggle
- Remember me checkbox
- Social login: MonCash/NatCash buttons
- Redirect vers home après login

**signup.html** - Inscription donateur
- Nom, téléphone, mot de passe
- Validation frontend + backend (api/inscription.php)
- Stockage PostgreSQL (table users)

---

### 🏠 Main (6 pages)

**home.html** - Dashboard principal
- Hero section avec solde/stats
- Carrousel créateurs en vedette
- Grille 5 catégories: Art, Musique, Éducation, Jeunesse, Patrimoine
- FAB button (floating) pour don rapide
- Bottom navigation active sur Home

**explore.html** - Impact Stories
- Stories carousel (style Instagram)
- Cartes impact immersives
- Filtres: Tous, Récents, Populaires
- Quick impact button
- Bottom navigation active sur Explore

**creator-profile.html** - Profil créateur
- Header avec cover image
- Avatar + stats (Projets, Followers, Impact)
- Bio + badges
- Section Projets actifs
- Section Social links
- Activity feed
- CTA: Support Creator

**campaign-details.html** - Détails campagne
- Hero image de campagne
- Progress bar (funded %)
- Campaign story + objectifs
- Expected impact (stats)
- Recent supporters list
- CTA fixe bottom: Support This Campaign

**donation.html** - Sélection montant
- Profil créateur (avatar + nom)
- Amount selector (chips: 50, 100, 250, 500 HTG)
- Custom amount input
- Payment methods indicator (MonCash/NatCash)
- CTA: Send Love button

**payment-success.html** - Confirmation
- Success animation (heart icon pulsing)
- "Mèsi Anpil!" message
- Receipt card avec:
  - Montant donné
  - Recipient (créateur)
  - Payment method
- Reaction bar: Like, Share, Receipt
- CTA: Done (retour home)

---

### 👤 User (3 pages)

**profile.html** - Profil utilisateur
- Cover gradient + avatar
- Edit profile button
- User info (nom, bio, location)
- Stats grid: Donations, Following, HTG Given
- Menu sections:
  - Account (Personal Info, Payment Methods, Notifications)
  - Preferences (Language, Dark Mode)
  - Support (Help Center, About)
- Sign Out button
- Bottom navigation active sur Profile

**wallet.html** - Portefeuille
- Balance card avec total HTG
- Trend indicator (+12%)
- Quick actions: Add, Send, Receive, Exchange
- Payment methods cards:
  - MonCash (balance: 1,850 HTG)
  - NatCash (balance: 600 HTG)
- Recent transactions list
- Bottom navigation active sur Wallet

**notifications.html** - Centre notifications
- Filter tabs: All, Donations, Updates, System
- Notifications groupées par date:
  - Today (unread avec border-left colored)
  - Yesterday (read, opacity réduite)
  - This week
- Types: Donation success, Campaign alert, Milestones
- Empty state (hidden par défaut)
- Bottom navigation

---

### 🎨 Creator (1 page)

**my-campaigns.html** - Gestion projets
- Header avec avatar + greeting
- Intro text
- Filter chips: All, Active (12), Draft (3), Completed (8)
- Project cards avec:
  - Thumbnail
  - Status badge
  - Progress bar
  - Stats (raised, goal, backers, days left)
  - Payment method indicator
- FAB: "Start a journey" (créer campagne)
- Bottom navigation

---

### ⚠️ Error (2 pages)

**404.html** - Page non trouvée
- Icône search_off
- Message: "Page Not Found"
- CTA: Go to Home, Go Back
- Help text avec Contact Support link

**offline.html** - Pas de connexion
- Icône wifi_off
- Message: "No Internet Connection"
- Connection status card avec troubleshooting
- Auto-refresh toutes les 3s
- Auto-redirect quand connexion rétablie

---

## 🛠️ Technologies

### Frontend
- **CSS Framework**: Tailwind CSS (via CDN)
- **Icons**: Material Symbols Outlined
- **Fonts**: Plus Jakarta Sans (Google Fonts)
- **Dark Mode**: class-based (`class="dark"`)

### Backend
- **Server**: PHP 8.5.1
- **Database**: PostgreSQL 18 (port 5433)
- **DB User**: phpuser / simple123
- **API**: RESTful (api/inscription.php)

### Paiements (À implémenter)
- MonCash API
- NatCash API

---

## 📊 État d'Avancement

### ✅ Phase 1 & 2: Complétées
- [x] Système de design unifié créé
- [x] 6 fichiers "autre-*" harmonisés
- [x] Couleurs standardisées (#ea2a33)
- [x] Typographie unifiée (Plus Jakarta Sans)
- [x] Navigation bottom cohérente

### ✅ Phase 3: Complétée
- [x] 15 pages créées et organisées
- [x] Structure de dossiers (api, assets, pages)
- [x] Tous les fichiers renommés logiquement
- [x] Navigation inter-pages fonctionnelle
- [x] Documentation complète (INDEX.md, NAVIGATION.md)

### 🎯 Phase 4: Prochaines Étapes

#### Interactions & Animations
- [ ] Skeleton loaders pour cartes
- [ ] Toast notifications (success, error, info)
- [ ] Page transitions
- [ ] Pull-to-refresh
- [ ] Scroll animations
- [ ] Loading states pour actions

#### Empty States
- [ ] No donations yet (wallet)
- [ ] No notifications
- [ ] No campaigns (creator)
- [ ] No results (search)

#### Fonctionnalités Manquantes
- [ ] Système de recherche/filtres
- [ ] Favoris/bookmarks
- [ ] Partage de campagnes
- [ ] Commentaires sur projets
- [ ] Messages directs
- [ ] Impact reports détaillés

#### Pages Additionnelles
- [ ] Personal information edit
- [ ] Language settings (Kreyòl/English)
- [ ] Help center / FAQ
- [ ] About JaKòb
- [ ] Terms & Privacy
- [ ] Transaction details
- [ ] Payment method management
- [ ] Creator registration flow
- [ ] Campaign creation wizard

#### Intégrations
- [ ] MonCash API réelle
- [ ] NatCash API réelle
- [ ] Email notifications
- [ ] SMS notifications (OTP)
- [ ] Social media sharing
- [ ] Analytics tracking

---

## 🔒 Sécurité

### Implémentées ✅
- [x] **Bcrypt password hashing** (PASSWORD_BCRYPT avec `password_hash()`)
- [x] **Password verification** (via `password_verify()`)
- [x] **SQL injection prevention** (Prepared statements PDO)
- [x] **CORS headers** (api/*.php)
- [x] **MySQL avec PDO** (secure database connection)
- [x] **Session management** (PHP sessions via config/session.php)
- [x] **Input validation** (frontend + backend)
- [x] **Secure password storage** (VARCHAR(255) pour hashes bcrypt)

### Documentation
- 📄 [SECURITY.md](SECURITY.md) - Guide complet de sécurité
- 🔑 Hash bcrypt généré: `$2y$12$gr36jBxylQF1beUFx2kJiunjOpOYGI1YMHMiU9.oWrRiQXDFjOvH.`
- 🧪 Comptes de test utilisent le mot de passe: `password123`

### À Implémenter 📋
- [ ] **CSRF protection** (tokens anti-CSRF)
- [ ] **Rate limiting API** (prévention brute force)
- [ ] **XSS prevention** (sanitization complète)
- [ ] **2FA authentication** (OTP SMS/Email)
- [ ] **Password strength requirements** (8+ chars, majuscules, chiffres, symboles)
- [ ] **Password reset flow** (via email avec tokens)
- [ ] **Session timeout** (expiration automatique)
- [ ] **Audit logging** (logs des événements d'authentification)

---

## 🎨 Guidelines UX/UI

### Accessibilité (WCAG AA)
- Touch targets: 44px minimum
- Contrast ratio: 4.5:1 (text), 3:1 (large text)
- Focus indicators visibles
- Alt text pour images
- Semantic HTML

### Performance
- Lazy loading d'images recommandé
- Minification CSS/JS en production
- Service Worker pour mode offline
- Cache strategy pour assets statiques

### Mobile-First
- Container max-width: 448px
- Responsive breakpoints Tailwind
- Bottom navigation accessible au pouce
- Haptic feedback (à implémenter)

---

## 🇭🇹 Éléments Culturels

### Kreyòl Ayisyen
- "Mèsi Anpil!" (Merci beaucoup)
- "Annou Sipòte Kilti Ayisyen" (Supportons la culture haïtienne)
- "Pwojè Mwen" (Mes projets)
- "Eritaj & Istwa" (Patrimoine & Histoire)

### Visuels
- Patterns Taino dans backgrounds
- Couleurs drapeau: Rouge (#ea2a33) et Bleu
- Icônes culturelles: temple_hindu, palette, school

### Catégories
1. 🎨 Art & Artizana
2. 🎵 Mizik
3. 📚 Edikasyon
4. 👥 Jèn
5. 🏛️ Eritaj & Istwa

---

## 📞 Support & Contribution

### Fichiers de Référence
- **Design**: `assets/css/design-system.js`
- **Database**: `db.php` (PostgreSQL config)
- **API**: `api/inscription.php`
- **Navigation**: `NAVIGATION.md`

### Conventions de Nommage
- Pages: `kebab-case.html` (ex: `creator-profile.html`)
- Dossiers: `lowercase` (ex: `auth`, `main`, `user`)
- CSS Classes: Tailwind utilities
- JavaScript: camelCase

### Git Structure (Recommandée)
```
main
├── feature/auth-flow
├── feature/payment-integration
├── feature/creator-dashboard
└── bugfix/navigation-links
```

---

## 📝 Changelog

### Version 2.0.0 (25 décembre 2025)
- ✨ Restructuration complète du projet
- ✨ 15 pages organisées dans dossiers logiques
- ✨ Navigation cohérente sur toutes les pages
- ✨ Documentation complète (INDEX.md, NAVIGATION.md)
- 🔧 Tous les liens inter-pages corrigés
- 🗑️ Suppression fichiers inutilisés (menu.html, thanks.html, profil.php)

### Version 1.0.0 (Initiale)
- ✅ Système de design créé
- ✅ 6 fichiers "autre-*" harmonisés
- ✅ API inscription fonctionnelle
- ✅ PostgreSQL configuré

---

**Dernière mise à jour**: 25 décembre 2025
**Version**: 2.0.0
**Status**: Phase 3 Complétée ✅ - Prêt pour Phase 4
