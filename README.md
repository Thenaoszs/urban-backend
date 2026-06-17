# Backend CitoyenApp – Laravel 11

API REST sécurisée avec Laravel Sanctum pour l'application de signalement citoyen.

---

## ⚡ Installation rapide

### 1. Cloner / extraire le projet
```bash
unzip laravel_citoyen_backend.zip
cd laravel_backend
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configurer l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

Éditer `.env` avec vos paramètres de base de données :
```env
DB_DATABASE=citoyen_db
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
APP_URL=http://backend-citoyen.test
```

### 4. Créer la base de données MySQL
```sql
CREATE DATABASE citoyen_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Lancer les migrations + seeder
```bash
php artisan migrate --seed
```

### 6. Créer le lien symbolique storage
```bash
php artisan storage:link
```

### 7. Configurer votre serveur local (Laragon / WAMP / Valet)
- Domaine : `backend-citoyen.test` → dossier `public/`
- Les images seront accessibles via : `http://backend-citoyen.test/storage/signalements/xxx.jpg`

---

## 🔐 Comptes de test (après seeder)

| Rôle          | Email                       | Mot de passe |
|---------------|-----------------------------|--------------|
| Admin         | admin@citoyen.test          | password     |
| Gestionnaire  | gestionnaire@citoyen.test   | password     |
| Citoyen 1     | kofi@citoyen.test           | password     |
| Citoyen 2     | ama@citoyen.test            | password     |

---

## 📁 Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php          # Login, Register, Logout, Me
│   │   ├── SignalementController.php   # CRUD signalements + stats
│   │   └── UserController.php         # Gestion utilisateurs (admin)
│   ├── Middleware/
│   │   ├── CheckRole.php               # Contrôle des rôles (role:admin,gestionnaire)
│   │   └── ForceJsonResponse.php       # Force Accept: application/json
│   └── Requests/
│       ├── LoginRequest.php
│       ├── RegisterRequest.php
│       ├── StoreSignalementRequest.php
│       ├── UpdateStatutRequest.php
│       ├── StoreUserRequest.php
│       └── UpdateUserRequest.php
├── Models/
│   ├── User.php
│   ├── Signalement.php
│   └── ImageSignalement.php
config/
├── cors.php         # Origines autorisées (Flutter + Nuxt)
├── sanctum.php      # Configuration tokens
├── auth.php         # Guards & providers
└── filesystems.php  # Disque public pour storage:link
bootstrap/
└── app.php          # Middlewares + gestion exceptions JSON
database/
├── migrations/      # 4 migrations (users, signalements, images, tokens)
└── seeders/
    └── DatabaseSeeder.php   # Données de test
routes/
├── api.php          # Toutes les routes API
└── web.php          # Health check uniquement
```

---

## 🗺️ Routes API

### Publiques (sans token)
| Méthode | URL             | Description           |
|---------|-----------------|-----------------------|
| POST    | /api/register   | Inscription citoyen   |
| POST    | /api/login      | Connexion             |

### Authentifiées (Bearer token requis)
| Méthode | URL                              | Rôle                    | Description                   |
|---------|----------------------------------|-------------------------|-------------------------------|
| POST    | /api/logout                      | Tous                    | Déconnexion                   |
| GET     | /api/me                          | Tous                    | Profil connecté               |
| PUT     | /api/profile                     | Tous                    | Modifier son profil           |
| GET     | /api/signalements                | Citoyen                 | Mes signalements              |
| POST    | /api/signalements                | Citoyen                 | Créer un signalement          |
| GET     | /api/signalements/{id}           | Citoyen (les siens)     | Détail                        |
| GET     | /api/signalements/all            | Gestionnaire, Admin     | Tous les signalements         |
| PUT     | /api/signalements/{id}/statut    | Gestionnaire, Admin     | Changer le statut             |
| DELETE  | /api/signalements/{id}           | Admin                   | Supprimer                     |
| GET     | /api/stats                       | Gestionnaire, Admin     | Statistiques dashboard        |
| GET     | /api/users                       | Admin                   | Liste utilisateurs            |
| POST    | /api/users                       | Admin                   | Créer utilisateur             |
| GET     | /api/users/{id}                  | Admin                   | Détail utilisateur            |
| PUT     | /api/users/{id}                  | Admin                   | Modifier utilisateur          |
| DELETE  | /api/users/{id}                  | Admin                   | Supprimer utilisateur         |
| PATCH   | /api/users/{id}/toggle-block     | Admin                   | Bloquer / Débloquer           |

---

## 📦 Format des réponses

### Login / Register
```json
{
  "message": "Connexion réussie.",
  "token": "1|abc...",
  "user": {
    "id": 1,
    "nom": "Kofi Ameko",
    "email": "kofi@citoyen.test",
    "role": "citoyen",
    "is_blocked": false,
    "date_creation": "2024-01-01T10:00:00+00:00"
  }
}
```

### Signalement
```json
{
  "id": 1,
  "utilisateur_id": 2,
  "type": "inondation",
  "description": "...",
  "latitude": 6.1375,
  "longitude": 1.2123,
  "statut": "en_cours",
  "date_creation": "2024-01-01T10:00:00+00:00",
  "images": [
    {
      "id": 1,
      "signalement_id": 1,
      "image_path": "signalements/filename.jpg",
      "url": "http://backend-citoyen.test/storage/signalements/filename.jpg"
    }
  ],
  "utilisateur": {
    "id": 2,
    "nom": "Kofi Ameko"
  }
}
```

### Erreur de validation (422)
```json
{
  "message": "Données invalides.",
  "errors": {
    "email": ["Cette adresse email est déjà utilisée."],
    "password": ["Le mot de passe est requis."]
  }
}
```

---

## 🖼️ Upload d'images

- Envoi via `multipart/form-data` avec le champ `images[]`
- Maximum **2 images** par signalement
- Formats acceptés : JPEG, JPG, PNG, WEBP
- Taille max : **5 Mo** par image
- Stockage : `storage/app/public/signalements/`
- URL publique : `http://backend-citoyen.test/storage/signalements/{filename}`

---

## 🛡️ Sécurité

- **Sanctum** : tokens Bearer pour l'API mobile
- **CheckRole** : middleware vérifiant le rôle (`role:admin`, `role:gestionnaire,admin`)
- **ForceJsonResponse** : toutes les erreurs retournent du JSON
- **FormRequest** : validation côté serveur avec messages en français
- **Blocage utilisateur** : les comptes bloqués reçoivent un 403 à la connexion
- **CORS** : configuré pour Flutter (mobile) et Nuxt (web)

---

## 💡 Notes importantes

1. **`php artisan storage:link`** est obligatoire pour que les images soient accessibles publiquement.
2. Pour **Laragon** : le domaine `.test` est configuré automatiquement.
3. Pour **WAMP/XAMPP** : configurer un Virtual Host pointant vers le dossier `public/`.
4. L'application Flutter utilise `http://` (pas HTTPS) → `android:usesCleartextTraffic="true"` requis dans le manifest Android.
