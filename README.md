# WeCare — Logiciel de gestion pour les services à la personne

WeCare est une plateforme web SaaS dédiée aux structures d'aide à domicile (SAAD, SSIAD, SPASAD, CCAS, mandataires, prestataires…). Elle centralise la gestion des bénéficiaires, la planification des interventions, le suivi des intervenants et la communication entre les différents acteurs.

---

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | PHP 8.4 · Symfony 8.0 |
| ORM | Doctrine ORM 3 + Migrations |
| Base de données | MySQL 8.0 |
| Frontend | Twig · CSS custom · JavaScript vanilla |
| Tests | PHPUnit 13 |
| Serveur de dev | Symfony CLI |

---

## Prérequis

- PHP >= 8.4
- Composer
- MySQL 8.0
- Symfony CLI

---

## Installation

```bash
# 1. Cloner le dépôt
git clone <url-du-repo>
cd WeCareApp

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données
# Copier et adapter le fichier d'environnement
cp .env .env.local
# Modifier DATABASE_URL dans .env.local :
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/WeCare?serverVersion=8.0"

# 4. Créer la base de données et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Charger les données de démonstration
php bin/console doctrine:fixtures:load

# 6. Lancer le serveur de développement
symfony serve --no-tls
```

Accès : http://127.0.0.1:8000

---

## Structure du projet

```
src/
├── Controller/
│   ├── Admin/          # Dashboard, patients, soignants, planning, map, notifications, paramètres
│   ├── Api/            # Endpoints API internes
│   ├── Beneficiaire/   # Espace bénéficiaire / aidant
│   ├── Intervenant/    # Espace intervenant
│   ├── LandingController.php
│   └── LoginController.php
├── Entity/
│   ├── Utilisateur.php       # Base commune (Admin, Intervenant, Bénéficiaire, Aidant)
│   ├── Administrateur.php
│   ├── Intervenant.php
│   ├── Beneficiaire.php
│   ├── Aidant.php
│   ├── Intervention.php
│   ├── Planning.php
│   ├── Incident.php
│   ├── CompteRendu.php
│   ├── Message.php
│   ├── Notification.php
│   ├── Entreprise.php
│   ├── Abonnement.php
│   └── PlanTarifaire.php
├── Enum/               # 12 enums PHP 8.1+ (statuts, types, rôles…)
├── DataFixtures/       # Jeux de données de démonstration
└── Security/
templates/
├── admin/              # Layout et vues administration
├── intervenant/        # Layout et vues espace intervenant
├── landing/            # Pages publiques (accueil, fonctionnalités, tarifs, contact)
└── security/           # Connexion
tests/
└── Unit/
    └── WeCareUnitTest.php   # 55 tests · 120 assertions (enums + entités)
```

---

## Espaces utilisateurs

WeCare propose 3 espaces distincts selon le rôle :

| Espace | URL | Rôle |
|---|---|---|
| Administration | `/admin/dashboard` | Administrateur |
| Intervenant | `/intervenant/dashboard` | Intervenant |
| Bénéficiaire / Aidant | `/beneficiaire/...` | Bénéficiaire · Aidant |

---

## Comptes de démonstration

> ⚠️ **Note** : Les mots de passe sont stockés en clair dans les fixtures et dans ce README, ce qui n'est clairement pas une bonne pratique de sécurité. Faute de temps avant l'oral, nous n'avons pas eu d'autre choix pour faciliter la démonstration. Cela sera corrigé après la soutenance (hachage fort, variables d'environnement, suppression des credentials du dépôt).

Exemples de comptes pour tester chaque espace :

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur (exemple) | admin@wecare.fr | admin123 |
| Intervenant (exemple) | leo@wecare.fr | interv123 |
| Bénéficiaire (exemple) | simone@mail.fr | patient123 |

---

## Tests

```bash
php bin/phpunit
```

Les tests unitaires couvrent les 12 enums et les 4 entités principales (Utilisateur, Intervenant, Bénéficiaire, Intervention) — aucune base de données requise.

---

## Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Réinitialiser complètement la base de données
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Lister les routes
php bin/console debug:router

# Afficher les entités Doctrine
php bin/console doctrine:mapping:info
```

---

## Licence

Projet propriétaire — tous droits réservés.
