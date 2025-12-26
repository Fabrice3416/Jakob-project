import os

# Définition de la structure du projet et du contenu des fichiers
project_files = {
    # ---------------------------------------------------------
    # 1. BASE DE DONNÉES (SQL)
    # ---------------------------------------------------------
    "database.sql": """
-- SCHEMA DE BASE DE DONNEES JAKOB
-- A importer dans PostgreSQL

-- 1. TABLE UTILISATEURS
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    telephone VARCHAR(20) UNIQUE NOT NULL,
    prenom VARCHAR(50), -- Optionnel
    nom VARCHAR(50),    -- Optionnel
    is_creator BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABLE TRANSACTIONS
CREATE TABLE IF NOT EXISTS transactions_jakob (
    id BIGSERIAL PRIMARY KEY,
    user_id INTEGER, -- NULL si anonyme
    recipient_id INTEGER NOT NULL REFERENCES users(id),
    montant_brut NUMERIC(12,2) NOT NULL CHECK (montant_brut >= 50),
    platform_fee NUMERIC(12,2) NOT NULL DEFAULT 0,
    canal VARCHAR(20) CHECK (canal IN ('MONCASH', 'NATCASH')),
    reference_externe VARCHAR(100) UNIQUE,
    statut VARCHAR(20) DEFAULT 'PENDING',
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- DONNEES DE TEST
INSERT INTO users (username, telephone, is_creator) 
VALUES ('megantheestallion', '50900000000', TRUE)
ON CONFLICT DO NOTHING;
""",

    # ---------------------------------------------------------
    # 2. CONFIGURATION BACKEND
    # ---------------------------------------------------------
    "db.php": """<?php
// db.php
// Configuration sécurisée de la base de données
$host = 'localhost';
$db   = 'jakob_db';
$user = 'postgres'; 
$pass = 'ton_mot_de_passe'; // <--- CHANGE CECI
$port = "5432";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\\PDOException $e) {
    http_response_code(500);
    // En prod : error_log($e->getMessage());
    die(json_encode(["error" => "Erreur de connexion BDD. Vérifiez db.php"]));
}
?>""",

    # ---------------------------------------------------------
    # 3. API (LOGIQUE MÉTIER)
    # ---------------------------------------------------------
    "api/inscription.php": """<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../db.php';

$input = json_decode(file_get_contents("php://input"), true);

// Validation
if (empty($input['username']) || empty($input['telephone'])) {
    http_response_code(400);
    echo json_encode(["error" => "Username et Téléphone requis."]);
    exit;
}

// Nettoyage
$username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $input['username']));
$phone = preg_replace('/[^0-9]/', '', $input['telephone']);
if (strlen($phone) == 8) $phone = '509' . $phone;

try {
    // Vérification doublons
    $check = $pdo->prepare("SELECT id FROM users WHERE telephone = ? OR username = ?");
    $check->execute([$phone, $username]);
    if ($check->fetch()) {
        throw new Exception("Ce numéro ou ce nom d'utilisateur est déjà pris.");
    }

    // Insertion
    $stmt = $pdo->prepare("INSERT INTO users (username, telephone, active) VALUES (?, ?, TRUE)");
    $stmt->execute([$username, $phone]);

    http_response_code(201);
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>""",

    "api/don.php": """<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once '../db.php';

$input = json_decode(file_get_contents("php://input"), true);

if (empty($input['createurId']) || empty($input['montant']) || empty($input['canal'])) {
    http_response_code(400);
    echo json_encode(["error" => "Données incomplètes."]);
    exit;
}

$fee = round($input['montant'] * 0.05, 2);

try {
    // Vérif créateur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND is_creator = TRUE");
    $stmt->execute([$input['createurId']]);
    if (!$stmt->fetch()) throw new Exception("Créateur introuvable.");

    // Insertion Transaction
    $sql = "INSERT INTO transactions_jakob 
            (recipient_id, montant_brut, platform_fee, canal, reference_externe, statut) 
            VALUES (?, ?, ?, ?, ?, 'PENDING')";
    
    $pdo->prepare($sql)->execute([
        $input['createurId'],
        $input['montant'],
        $fee,
        $input['canal'],
        $input['idempotencyKey']
    ]);

    // Succès
    echo json_encode([
        "success" => true,
        "payment_url" => "thanks.html" 
    ]);

} catch (Exception $e) {
    // Idempotence
    if ($e instanceof PDOException && $e->getCode() == '23505') {
         echo json_encode(["success" => true, "payment_url" => "thanks.html"]);
         exit;
    }
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}
?>""",

    # ---------------------------------------------------------
    # 4. FRONTEND (HTML/PHP/CSS)
    # ---------------------------------------------------------
    "style.css": """/* style.css - Version Complète */
:root {
  --main-color: #2b7a78;
  --accent-color: #f7c59f;
  --text-dark: #17252a;
  --text-light: #def2f1;
  --white: #ffffff;
}

body {
  font-family: 'Segoe UI', sans-serif;
  background-color: var(--white);
  color: var(--text-dark);
  margin: 0; padding: 0;
  line-height: 1.6;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background-color: var(--main-color);
  color: var(--text-light);
}

.logo-container { display: flex; align-items: center; gap: 1rem; }
.logo { width: 50px; height: 50px; border-radius: 50%; }
.site-title { font-size: 1.5rem; }
.menu-button { background: none; border: none; color: var(--text-light); font-size: 2rem; cursor: pointer; }

/* Formulaires */
.form-container, .profile-container {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  text-align: center;
}

input, select, button {
  width: 100%;
  padding: 12px;
  margin: 8px 0;
  border-radius: 8px;
  border: 1px solid #ddd;
  box-sizing: border-box;
}

.submit-btn, .validate-button {
  background-color: var(--main-color);
  color: white;
  border: none;
  font-weight: bold;
  cursor: pointer;
}
.submit-btn:hover { opacity: 0.9; }

/* Profil */
.orbit-container { text-align: center; margin-top: 20px; }
.profile-pic { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid var(--main-color); }
.suggested-amounts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
.suggested-amounts button { background: #eef; border: 1px solid #ccc; color: #333; }

/* --- AJOUTS FONCTIONNELS (Alertes) --- */
.alert {
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 1rem;
  text-align: center;
  font-weight: 600;
  font-size: 0.9rem;
}
.success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

/* Inputs spéciaux */
.input-phone, select { border: 2px solid #ddd; outline: none; }
.input-phone:focus { border-color: var(--main-color); }

.username-group { position: relative; width: 100%; margin: 0.5rem 0; }
.username-group span { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #555; font-weight: bold; }
.username-group input { padding-left: 35px !important; }
""",

    "inscription-donateur.html": """<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inscription Donateur</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="header">
    <div class="logo-container">
      <img src="https://tse1.mm.bing.net/th/id/OIP.j5cceZXE70QB1K-TcN3-PQHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3" alt="Logo" class="logo" />
      <h1 class="site-title">JaKòb</h1>
    </div>
    <button class="menu-button" onclick="window.location.href='menu.html'">&#9776;</button>
  </header>

  <form id="registerForm" class="form-container">
    <h2>Créer un compte</h2>
    <div id="msgBox" class="alert" style="display:none;"></div>

    <div class="username-group">
        <span>@</span>
        <input type="text" id="username" placeholder="nom_utilisateur" required autocomplete="off" />
    </div>

    <input type="tel" id="telephone" placeholder="Téléphone (Ex: 37000000)" required />
    <label style="display:block; text-align:left; font-size:0.9rem;">
        <input type="checkbox" style="width:auto;" required /> J'accepte les conditions
    </label>
    
    <button type="submit" id="btnSubmit" class="submit-btn">S’inscrire</button>
  </form>

  <script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnSubmit');
        const msg = document.getElementById('msgBox');
        
        btn.disabled = true; btn.innerText = "Traitement..."; msg.style.display = 'none';

        try {
            const res = await fetch('api/inscription.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    username: document.getElementById('username').value,
                    telephone: document.getElementById('telephone').value
                })
            });
            const data = await res.json();
            if(!res.ok) throw new Error(data.error || "Erreur serveur");

            msg.className = "alert success"; msg.innerText = "✅ Bienvenue !"; msg.style.display = 'block';
            setTimeout(() => window.location.href = "thanks.html", 1500);

        } catch (err) {
            msg.className = "alert error"; msg.innerText = "⚠️ " + err.message; msg.style.display = 'block';
            btn.disabled = false; btn.innerText = "S’inscrire";
        }
    });
  </script>
</body>
</html>""",

    "profil.php": """<?php
require_once 'db.php';
$id = $_GET['id'] ?? 1; // ID par défaut

try {
    $stmt = $pdo->prepare("SELECT id, username, prenom, nom FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    // Fallback si user non trouvé (pour éviter crash démo)
    if (!$user) { 
        $user = ['id' => 0, 'username' => 'Inconnu', 'prenom' => '', 'nom' => '']; 
        $displayName = "Utilisateur Introuvable";
    } else {
        $displayName = $user['username'] ? "@" . $user['username'] : $user['prenom'];
    }
} catch (Exception $e) { die("Erreur système"); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil de <?php echo htmlspecialchars($displayName); ?></title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="header">
    <div class="logo-container">
      <img src="https://tse1.mm.bing.net/th/id/OIP.j5cceZXE70QB1K-TcN3-PQHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3" alt="Logo" class="logo" />
      <h1 class="site-title">JaKòb</h1>
    </div>
    <button class="menu-button" onclick="window.location.href='menu.html'">&#9776;</button>
  </header>

  <main>
    <div class="orbit-container">
      <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($displayName); ?>&background=random&size=256" alt="Profil" class="profile-pic" />
    </div>

    <div class="profile-container">
      <h3 class="profile-name"><?php echo htmlspecialchars($displayName); ?></h3>
      <p class="profile-bio">Merci pour votre soutien ! 🙏🏽</p>

      <div id="donationMsg" class="alert" style="display:none;"></div>
      <input type="hidden" id="createurId" value="<?php echo $user['id']; ?>">

      <div class="amount-section">
        <input type="number" id="customAmount" placeholder="Montant (Gourdes)" class="input-phone" min="50" />
        
        <select id="canalSelect" class="input-phone" style="margin-top:5px; background:white;">
            <option value="" disabled selected>Choisir Paiement</option>
            <option value="MONCASH">MonCash</option>
            <option value="NATCASH">NatCash</option>
        </select>

        <div class="suggested-amounts">
           <button onclick="document.getElementById('customAmount').value=100">100 G</button>
           <button onclick="document.getElementById('customAmount').value=250">250 G</button>
           <button onclick="document.getElementById('customAmount').value=500">500 G</button>
           <button onclick="document.getElementById('customAmount').value=1000">1000 G</button>
        </div>
        
        <button id="btnPay" class="validate-button" onclick="processDonation()">ENVOYER LE DON</button>
      </div>
    </div>
  </main>

  <script>
    const idempotencyKey = 'uuid-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

    async function processDonation() {
        const amount = document.getElementById('customAmount').value;
        const canal = document.getElementById('canalSelect').value;
        const btn = document.getElementById('btnPay');
        const msg = document.getElementById('donationMsg');

        if(amount < 50) { alert("Minimum 50 Gourdes"); return; }
        if(!canal) { alert("Choisissez MonCash ou NatCash"); return; }

        btn.disabled = true; btn.innerText = "Traitement..."; msg.style.display = 'none';

        try {
            const res = await fetch('api/don.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    createurId: document.getElementById('createurId').value,
                    montant: amount,
                    canal: canal,
                    idempotencyKey: idempotencyKey
                })
            });
            const data = await res.json();
            if(!res.ok) throw new Error(data.error || "Erreur");

            window.location.href = data.payment_url; 

        } catch(e) {
            msg.className = "alert error"; msg.innerText = "❌ " + e.message; msg.style.display = 'block';
            btn.disabled = false; btn.innerText = "ENVOYER";
        }
    }
  </script>
</body>
</html>""",

    "thanks.html": """<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Merci</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="form-container" style="margin-top: 100px;">
    <h2>🎉 Opération Réussie ! 🎉</h2>
    <p>Merci pour votre confiance.</p>
    <br>
    <a href="index.html" class="validate-button" style="display:inline-block; text-decoration:none;">Retour Accueil</a>
  </div>
</body>
</html>""",

    "menu.html": """<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Menu</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="header">
    <div class="logo-container">
      <h1 class="site-title">JaKòb</h1>
    </div>
    <button class="menu-button" onclick="window.location.href='index.html'">✖</button>
  </header>
  <main style="padding: 2rem;">
    <ul style="list-style:none; padding:0; font-size:1.2rem; line-height:2.5;">
      <li><a href="profil.php?id=1" style="text-decoration:none; color:#333;">👤 Mon Profil (Démo ID 1)</a></li>
      <li><a href="inscription-donateur.html" style="text-decoration:none; color:#333;">📝 Inscription</a></li>
      <li onclick="alert('Bientôt disponible')">⚙️ Paramètres</li>
      <li><a href="index.html" style="text-decoration:none; color:red;">Déconnexion</a></li>
    </ul>
  </main>
</body>
</html>""",

    "index.html": """<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Accueil - JaKòb</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="header">
    <div class="logo-container">
       <img src="https://tse1.mm.bing.net/th/id/OIP.j5cceZXE70QB1K-TcN3-PQHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3" alt="Logo" class="logo" />
      <h1 class="site-title">JaKòb</h1>
    </div>
    <button class="menu-button" onclick="window.location.href='menu.html'">&#9776;</button>
  </header>

  <main style="text-align:center; padding:2rem;">
    <h2>Bienvenue sur JaKòb</h2>
    <p>La plateforme de soutien aux créateurs Haïtiens.</p>
    
    <div style="margin-top:2rem;">
        <a href="inscription-donateur.html" class="validate-button" style="display:block; text-decoration:none; margin-bottom:1rem;">Créer un compte</a>
        <a href="profil.php?id=1" class="validate-button" style="display:block; text-decoration:none; background:#f7c59f; color:#333;">Voir un profil démo</a>
    </div>
  </main>
</body>
</html>"""
}

# Création des fichiers
def create_project():
    print("🚀 Création du projet Jakob...")
    for filepath, content in project_files.items():
        # Créer les dossiers si nécessaire (ex: 'api/')
        directory = os.path.dirname(filepath)
        if directory and not os.path.exists(directory):
            os.makedirs(directory)
        
        # Écrire le fichier
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"✅ Fichier créé : {filepath}")
    
    print("\\n🎉 Projet généré avec succès !")
    print("👉 N'oublie pas de configurer ton mot de passe dans 'db.php'.")
    print("👉 Importe 'database.sql' dans PostgreSQL.")

if __name__ == "__main__":
    create_project()