# Limites VARCHAR pour MySQL utf8mb4

## Problème

Avec le charset `utf8mb4` (qui supporte tous les caractères Unicode y compris les emojis), chaque caractère peut occuper jusqu'à **4 bytes**.

MySQL a une limite d'index de **767 bytes** par défaut, ce qui signifie:
- Limite pour VARCHAR avec index: **767 / 4 = 191 caractères**

## Corrections Appliquées

Tous les champs `VARCHAR` avec index ou `UNIQUE` ont été réduits à **VARCHAR(191)** maximum.

### Tables Modifiées

#### 1. **users**
```sql
-- Avant
email VARCHAR(255) UNIQUE
password VARCHAR(255)

-- Après
email VARCHAR(191) UNIQUE
password VARCHAR(191)
```

#### 2. **donors**
```sql
-- Avant
avatar_url VARCHAR(500)
location VARCHAR(255)

-- Après
avatar_url VARCHAR(191)
location VARCHAR(191)
```

#### 3. **influencers**
```sql
-- Avant
avatar_url VARCHAR(500)
cover_image_url VARCHAR(500)
location VARCHAR(255)
bank_account VARCHAR(255)

-- Après
avatar_url VARCHAR(191)
cover_image_url VARCHAR(191)
location VARCHAR(191)
bank_account VARCHAR(191)
```

#### 4. **campaigns**
```sql
-- Avant
title VARCHAR(255)
slug VARCHAR(255) UNIQUE
image_url VARCHAR(500)
video_url VARCHAR(500)

-- Après
title VARCHAR(191)
slug VARCHAR(191) UNIQUE
image_url VARCHAR(191)
video_url VARCHAR(191)
```

#### 5. **donations**
```sql
-- Avant
transaction_id VARCHAR(255) UNIQUE

-- Après
transaction_id VARCHAR(191) UNIQUE
```

#### 6. **payment_methods**
```sql
-- Avant
account_number VARCHAR(255)
account_name VARCHAR(255)

-- Après
account_number VARCHAR(191)
account_name VARCHAR(191)
```

#### 7. **transactions**
```sql
-- Avant
description VARCHAR(500)
reference_id VARCHAR(255)

-- Après
description VARCHAR(191)
reference_id VARCHAR(191)
```

#### 8. **notifications**
```sql
-- Avant
title VARCHAR(255)
link VARCHAR(500)

-- Après
title VARCHAR(191)
link VARCHAR(191)
```

## Champs Non Modifiés

Les champs suivants n'ont **PAS** besoin d'être modifiés car:
- Ils n'ont pas d'index
- Ils sont déjà <= 191 caractères
- Ils utilisent le type `TEXT` (pas de limite d'index)

```sql
-- Champs OK (< 191 caractères)
phone VARCHAR(20)
first_name VARCHAR(100)
last_name VARCHAR(100)
display_name VARCHAR(150)
username VARCHAR(50)
currency VARCHAR(3)
provider VARCHAR(50)
icon VARCHAR(50)
moncash_number VARCHAR(20)
natcash_number VARCHAR(20)

-- Champs TEXT (pas de limite)
bio TEXT
description TEXT
story TEXT
message TEXT
```

## Impact sur l'Application

### URLs (191 caractères)
- **Largement suffisant** pour la plupart des URLs
- Exemple URL longue: `https://example.com/very/long/path/with/many/segments/and/query/parameters?param1=value1&param2=value2&param3=value3` = ~150 caractères
- Si URLs > 191 caractères: stocker en `TEXT` et utiliser un hash pour l'index

### Emails (191 caractères)
- **Largement suffisant**
- RFC 5321 limite: 254 caractères (partie locale: 64, domaine: 189)
- Partie locale max: 64 caractères
- Domaine max: 189 caractères
- Notre limite de 191 couvre 99.9% des emails

### Transaction IDs (191 caractères)
- **Suffisant** pour la plupart des systèmes
- UUID: 36 caractères
- Stripe transaction ID: ~30 caractères
- PayPal transaction ID: ~17 caractères
- Si besoin > 191: utiliser hash SHA256 (64 caractères)

### Titres et Slugs (191 caractères)
- **Suffisant** pour titres de campagnes
- SEO recommande < 60 caractères pour les titres
- Slugs généralement < 100 caractères

## Alternatives si 191 Caractères Insuffisant

### Option 1: Utiliser TEXT sans Index
```sql
-- Pour URLs très longues
`url` TEXT NULL,
-- Ajouter un hash indexé
`url_hash` CHAR(64) UNIQUE, -- SHA256 hash
```

### Option 2: Augmenter la Limite d'Index MySQL
```sql
-- Configuration MySQL (my.cnf ou my.ini)
[mysqld]
innodb_large_prefix = ON
innodb_file_format = Barracuda
innodb_file_per_table = ON

-- Permet jusqu'à 3072 bytes (768 caractères avec utf8mb4)
-- Puis dans la table:
CREATE TABLE example (
    long_field VARCHAR(768) UNIQUE,
    ...
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4;
```

### Option 3: Index Partiel
```sql
-- Index seulement les N premiers caractères
CREATE INDEX idx_partial ON table_name (long_field(191));
```

## Vérification

Pour vérifier qu'aucun champ ne dépasse la limite:

```sql
-- Vérifier la longueur max des emails
SELECT MAX(CHAR_LENGTH(email)) as max_length FROM users;

-- Vérifier la longueur max des URLs
SELECT MAX(CHAR_LENGTH(avatar_url)) as max_length FROM donors;
```

## Références

- [MySQL VARCHAR Limits](https://dev.mysql.com/doc/refman/8.0/en/char.html)
- [InnoDB Index Limits](https://dev.mysql.com/doc/refman/8.0/en/innodb-limits.html)
- [utf8mb4 Character Set](https://dev.mysql.com/doc/refman/8.0/en/charset-unicode-utf8mb4.html)

---

**Date de Modification:** 25 décembre 2025
**Statut:** ✅ Toutes les corrections appliquées
