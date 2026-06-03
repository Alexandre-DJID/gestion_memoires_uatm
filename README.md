# Plateforme de Gestion des Mémoires - UATM

## 1. Contexte du Projet

Ce projet a été réalisé dans le cadre de la formation en ingénierie logicielle (Filière Système Informatique et Logiciel - SIL) à l'UATM Gasa Formation. Il s'agit d'une application web conçue pour dématérialiser, centraliser et sécuriser le processus de dépôt, d'évaluation et d'archivage des mémoires de fin d'études au sein de l'établissement.

## 2. Architecture Logicielle

Afin de démontrer une maîtrise approfondie des principes de conception logicielle, l'application repose sur une architecture **MVC (Modèle-Vue-Contrôleur) native**. Ce choix technique, réalisé sans l'utilisation de frameworks externes, garantit une séparation stricte des responsabilités (Separation of Concerns).

```text
gestion_memoires_uatm/
├── app/                  # Cœur de l'application (Logique métier)
│   ├── controllers/      # Intermédiaires traitant les requêtes et reliant Modèles et Vues
│   ├── models/           # Entités et abstraction de l'accès aux données (Requêtes SQL)
│   └── views/            # Interfaces utilisateur organisées par modules (admin, auth, dashboard...)
├── config/               # Fichiers de paramétrage (Variables globales, accès BD)
├── core/                 # Moteur de base de l'application (Routeur principal, utilitaires)
├── database/             # Scripts SQL de création et de gestion de la base de données
├── public/               # Document Root (Seul point d'entrée exposé au réseau)
│   ├── assets/           # Ressources statiques (CSS, JS, Images)
│   ├── uploads/          # Stockage sécurisé des fichiers PDF soumis par les utilisateurs
│   ├── index.php         # Front Controller interceptant toutes les requêtes
│   └── .htaccess         # Règles de réécriture d'URL et politique de sécurité Apache
├── DATABASE.md           # Documentation détaillée du schéma relationnel
├── INFRASTRUCTURE.md     # Spécifications de déploiement
└── README.md             # Présentation générale du projet
```

## 3. Sécurité et Bonnes Pratiques

Dans le respect des standards de développement web modernes, plusieurs mécanismes de sécurité ont été implémentés au cœur de l'application :

* **Point d'entrée unique (Front Controller) :** L'accès direct aux fichiers PHP est bloqué. Le serveur web pointe exclusivement vers le répertoire `/public`. Le fichier `index.php` et le `.htaccess` se chargent de l'aiguillage.
* **Prévention des Injections SQL :** L'interaction avec la base de données s'effectue via l'extension PDO (PHP Data Objects), avec une utilisation systématique des requêtes préparées (`prepare` et `execute`).
* **Protection XSS (Cross-Site Scripting) :** Toutes les données affichées sur les interfaces clientes sont assainies au préalable via la fonction `htmlspecialchars(ENT_QUOTES, 'UTF-8')`.
* **Cryptographie des mots de passe :** Les identifiants sont sécurisés en base de données grâce au hachage algorithmique natif de PHP (`password_hash` et `password_verify`).

## 4. Mécanisme de Routage

L'application intègre un routeur personnalisé capable d'extraire dynamiquement les paramètres des URL pour les transmettre aux contrôleurs correspondants.

**Exemple de traitement d'une requête :**
1. Le client effectue une requête vers `/memoires/afficher/12`.
2. Le fichier `.htaccess` redirige la requête vers `public/index.php`.
3. Le Routeur analyse l'URL, identifie la route correspondante et extrait l'identifiant (`12`).
4. Le routeur instancie le `MemoireController` et fait appel à sa méthode `afficher($id)`.

## 5. Conventions de Code

Afin d'assurer la maintenabilité et la lisibilité du code par l'équipe de développement, les standards de nommage suivants ont été adoptés :
* **Classes (Contrôleurs, Modèles, Core)** : `PascalCase` (ex: `MemoireController.php`)
* **Méthodes et propriétés** : `camelCase` (ex: `getMemoiresValides()`)
* **Vues et colonnes SQL** : `snake_case` (ex: `memoire_detail.php`, `date_creation`)

## 6. Guide d'Installation (Environnement d'évaluation)

### Prérequis techniques
* Serveur web Apache avec le module `mod_rewrite` activé.
* PHP 8.0 ou supérieur.
* SGBD MySQL ou MariaDB.

### Procédure de déploiement local
1. **Clonage :** Placer le répertoire du projet dans le dossier racine du serveur web local (ex: `htdocs` pour XAMPP).
2. **Base de données :** Importer le script SQL de structure fourni dans le répertoire `/database` via phpMyAdmin ou en ligne de commande.
3. **Configuration :** Éditer le fichier `config/database.php` pour y renseigner les identifiants de l'environnement local.
4. **Droits d'accès :** S'assurer que le serveur web dispose des droits d'écriture sur le répertoire `public/uploads/memoires/`.
5. **Lancement :** Accéder à l'application via le navigateur en ciblant le dossier public (ex: `http://localhost/gestion_memoires_uatm/public`).