# Navigation Structure - JaKòb

## 📁 Structure des Dossiers

```
jakob-development/
├── index.html (redirect vers pages/auth/splash.html)
├── db.php
├── test-db.php
├── INDEX.md
├── NAVIGATION.md
│
├── api/
│   └── inscription.php
│
├── assets/
│   └── css/
│       └── design-system.js
│
└── pages/
    ├── auth/
    │   ├── splash.html (onboarding/welcome)
    │   ├── login.html
    │   └── signup.html (inscription-donateur)
    │
    ├── main/
    │   ├── home.html (main dashboard)
    │   ├── explore.html (impact stories)
    │   ├── creator-profile.html (profil créateur)
    │   ├── campaign-details.html
    │   ├── donation.html
    │   └── payment-success.html
    │
    ├── user/
    │   ├── profile.html (user profile & settings)
    │   ├── wallet.html
    │   └── notifications.html
    │
    ├── creator/
    │   └── my-campaigns.html (gestion projets)
    │
    └── error/
        ├── 404.html
        └── offline.html
```

---

## 🔗 Chemins de Navigation

### Pages d'Authentification (`pages/auth/`)

**splash.html**
- → `signup.html` (Get Started button)
- → `../main/home.html` (Explore as Guest)
- → `login.html` (Sign In link)

**login.html**
- ← `splash.html` (back button)
- → `../main/home.html` (after successful login)
- → `signup.html` (Sign Up link)

**signup.html**
- → API: `../../api/inscription.php` (form submit)
- → `login.html` (Sign In link)
- → `../main/home.html` (after successful signup)

---

### Pages Principales (`pages/main/`)

**home.html** (Hub Central)
Navigation Bottom:
- `home.html` (active)
- `explore.html`
- `../user/wallet.html`
- `../user/profile.html`

Contenus:
- Creator cards → `creator-profile.html`
- Categories → filtres/search
- FAB button → `donation.html`

**explore.html** (Impact Stories)
Navigation Bottom:
- `home.html`
- `explore.html` (active)
- `../user/wallet.html`
- `../user/profile.html`

Contenus:
- Story cards → `creator-profile.html`
- Quick impact button → `donation.html`

**creator-profile.html**
- ← `home.html` or `explore.html` (back)
- → `donation.html` (Support button)
- → `campaign-details.html` (view campaigns)

**campaign-details.html**
- ← `../creator/my-campaigns.html` (back)
- → `donation.html` (Support Campaign button)
- → `creator-profile.html` (creator name link)

**donation.html**
- ← Previous page (back button)
- → `payment-success.html` (after donation submit)

**payment-success.html**
- → `home.html` (Done button)
- Share/Like/Receipt buttons (à implémenter)

---

### Pages Utilisateur (`pages/user/`)

**profile.html**
Navigation Bottom:
- `../main/home.html`
- `../main/explore.html`
- `wallet.html`
- `profile.html` (active)

Menu Links:
- Personal Information → (à créer)
- `wallet.html` (Payment Methods)
- `notifications.html`
- Language Settings → (à créer)
- Help Center → (à créer)
- About JaKòb → (à créer)

**wallet.html**
Navigation Bottom:
- `../main/home.html`
- `../main/explore.html`
- `wallet.html` (active)
- `profile.html`

Actions:
- Add/Send/Receive/Exchange → (fonctionnalités à implémenter)
- Payment method cards → détails (à créer)
- Transaction items → détails (à créer)

**notifications.html**
- ← `profile.html` (back)
- Notification items → pages correspondantes
- Navigation Bottom:
  - `../main/home.html`
  - `../main/explore.html`
  - `wallet.html`
  - `profile.html`

---

### Pages Créateur (`pages/creator/`)

**my-campaigns.html**
- `../user/notifications.html` (notification icon)
- Campaign cards → `../main/campaign-details.html`
- Filtres: All/Active/Draft/Completed

---

### Pages d'Erreur (`pages/error/`)

**404.html**
- → `../main/home.html` (Go to Home)
- ← Previous page (Go Back)
- → `../user/profile.html` (Contact Support)

**offline.html**
- Refresh button (reload page)
- → `../main/home.html` (Go to Home)
- Auto-redirect when online

---

## 🎯 Flux Utilisateur Principal

```
1. Première Visite
   index.html
   ↓
   pages/auth/splash.html
   ↓
   [Choice]
   ├→ Get Started → signup.html → home.html
   ├→ Sign In → login.html → home.html
   └→ Explore as Guest → home.html

2. Navigation Principale (Bottom Nav)
   home.html ←→ explore.html ←→ wallet.html ←→ profile.html

3. Parcours de Don
   home.html / explore.html
   ↓
   creator-profile.html
   ↓
   donation.html
   ↓
   payment-success.html
   ↓
   home.html

4. Gestion Campagnes (Créateurs)
   profile.html
   ↓
   my-campaigns.html
   ↓
   campaign-details.html
   ↓
   [edit/stats/supporters]

5. Profil & Paramètres
   profile.html
   ├→ wallet.html
   ├→ notifications.html
   ├→ Personal Info (à créer)
   └→ Settings (à créer)
```

---

## 📝 Notes Importantes

### Chemins Relatifs
- De `auth/` vers `main/`: `../main/filename.html`
- De `auth/` vers `user/`: `../user/filename.html`
- De `main/` vers `user/`: `../user/filename.html`
- De `user/` vers `main/`: `../main/filename.html`
- Vers API: `../../api/endpoint.php`

### Navigation Bottom Standard
Toutes les pages principales doivent avoir:
```html
<nav class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[90%] max-w-[360px]...">
    <a href="../main/home.html">Home</a>
    <a href="../main/explore.html">Explore</a>
    <a href="../user/wallet.html">Wallet</a>
    <a href="../user/profile.html">Profile</a>
</nav>
```

### Icônes Active State
- Page active: `bg-primary/20` + `text-primary`
- Page inactive: `text-white/50 group-hover:text-white`

---

## ✅ Statut des Pages

### Complètes avec Navigation ✓
- [x] splash.html
- [x] login.html
- [x] signup.html
- [x] home.html
- [x] explore.html
- [x] wallet.html
- [x] profile.html
- [x] notifications.html
- [x] campaign-details.html
- [x] donation.html
- [x] payment-success.html
- [x] 404.html
- [x] offline.html

### Partielles (liens à compléter)
- [ ] creator-profile.html (manque certains liens internes)
- [ ] my-campaigns.html (manque bottom nav)

### À Créer
- [ ] Personal information edit
- [ ] Language settings
- [ ] Help center
- [ ] About page
- [ ] Transaction details
- [ ] Payment method management
- [ ] Search/Filter pages

---

**Dernière mise à jour**: 25 décembre 2025
**Version**: 2.0.0 (Restructuration complète)
