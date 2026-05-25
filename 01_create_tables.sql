-- ============================================================================
-- SCRIPT DDL : Création de la base de données "gestion_memoires"
-- Architecture MVC PHP Vanilla avec Class Table Inheritance pour l'héritage
-- Moteur : InnoDB | Charset : utf8mb4
-- ============================================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gestion_memoires_uatm
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE gestion_memoires_uatm;

-- ============================================================================
-- 1. DICTIONNAIRES DE BASE (Tables de référence)
-- ============================================================================

-- Table des filières
CREATE TABLE filiere (
  id_filiere INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  libelle VARCHAR(255) NOT NULL UNIQUE,
  description TEXT,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Dictionnaire des filières d''études' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des centres (universités/instituts)
CREATE TABLE centre (
  id_centre INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  libelle VARCHAR(255) NOT NULL UNIQUE,
  adresse VARCHAR(512),
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Dictionnaire des centres d''études' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des niveaux d'études
CREATE TABLE niveau_etude (
  id_niveau INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  libelle VARCHAR(100) NOT NULL UNIQUE,
  ordre INT,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Dictionnaire des niveaux d''études (L1, L2, L3, M1, M2, etc.)' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des années académiques
CREATE TABLE annee_academique (
  id_annee INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  annee INT NOT NULL UNIQUE,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Dictionnaire des années académiques' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des statuts de mémoire
CREATE TABLE statut_memoire (
  id_statut INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  libelle VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Dictionnaire des statuts (Brouillon, En cours de validation, Validé, Rejeté, Archivé)' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des rôles de jury
CREATE TABLE role_jury (
  id_role INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  libelle VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Dictionnaire des rôles de jury (Directeur, Co-Directeur, Examinateur)' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. HÉRITAGE UTILISATEURS (Class Table Inheritance)
-- ============================================================================

-- Table mère : Utilisateur (base pour tous les rôles)
CREATE TABLE utilisateur (
  id_user INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL COMMENT 'Hachage bcrypt via password_hash()',
  type_utilisateur ENUM('etudiant', 'professeur', 'de') NOT NULL,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_type (type_utilisateur)
) ENGINE=InnoDB COMMENT='Table mère pour l''héritage des utilisateurs' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table fille : Étudiant
CREATE TABLE etudiant (
  id_user INT UNSIGNED PRIMARY KEY,
  matricule VARCHAR(50) NOT NULL UNIQUE,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES utilisateur(id_user) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Spécialisation étudiant' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table fille : Professeur
CREATE TABLE professeur (
  id_user INT UNSIGNED PRIMARY KEY,
  grade VARCHAR(100) NOT NULL COMMENT 'Docteur, Maitre-assistant, Assistant, etc.',
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES utilisateur(id_user) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Spécialisation professeur' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table fille : Directeur d'Études (DE)
CREATE TABLE de (
  id_user INT UNSIGNED PRIMARY KEY,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES utilisateur(id_user) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Spécialisation directeur d''études' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. VERROU DE SCOLARITÉ (Association étudiant-centre-filière-année-niveau)
-- ============================================================================

CREATE TABLE inscription (
  id_inscription INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_user INT UNSIGNED NOT NULL,
  id_filiere INT UNSIGNED NOT NULL,
  id_centre INT UNSIGNED NOT NULL,
  id_annee INT UNSIGNED NOT NULL,
  id_niveau INT UNSIGNED NOT NULL,
  date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES utilisateur(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_filiere) REFERENCES filiere(id_filiere) ON DELETE RESTRICT,
  FOREIGN KEY (id_centre) REFERENCES centre(id_centre) ON DELETE RESTRICT,
  FOREIGN KEY (id_annee) REFERENCES annee_academique(id_annee) ON DELETE RESTRICT,
  FOREIGN KEY (id_niveau) REFERENCES niveau_etude(id_niveau) ON DELETE RESTRICT,
  UNIQUE KEY uk_inscription (id_user, id_filiere, id_centre, id_annee)
) ENGINE=InnoDB COMMENT='Verrou de scolarité : enregistrement de l''inscription d''un étudiant' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. CŒUR MÉTIER : MÉMOIRES
-- ============================================================================

CREATE TABLE memoire (
  id_memoire INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  theme VARCHAR(512) NOT NULL,
  resume LONGTEXT NOT NULL,
  fichier_path VARCHAR(512) COMMENT 'Chemin relatif au dossier uploads',
  date_depot TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  nb_likes INT UNSIGNED DEFAULT 0,
  nb_commentaires INT UNSIGNED DEFAULT 0,
  id_statut INT UNSIGNED NOT NULL DEFAULT 1,
  date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_statut) REFERENCES statut_memoire(id_statut) ON DELETE RESTRICT,
  INDEX idx_statut (id_statut),
  INDEX idx_date_depot (date_depot)
) ENGINE=InnoDB COMMENT='Mémoires soumis par les étudiants' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. TABLES D'ASSOCIATION (Classes d'association)
-- ============================================================================

-- Table de liaison : Déposition de mémoire (Qui a déposé quel mémoire)
CREATE TABLE deposer (
  id_user INT UNSIGNED NOT NULL,
  id_memoire INT UNSIGNED NOT NULL,
  date_action TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user, id_memoire),
  FOREIGN KEY (id_user) REFERENCES utilisateur(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_memoire) REFERENCES memoire(id_memoire) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Association : Étudiant dépose un mémoire' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison : Likes (Qui aime quel mémoire)
CREATE TABLE liker (
  id_user INT UNSIGNED NOT NULL,
  id_memoire INT UNSIGNED NOT NULL,
  date_like TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user, id_memoire),
  FOREIGN KEY (id_user) REFERENCES utilisateur(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_memoire) REFERENCES memoire(id_memoire) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Association : Utilisateur aime un mémoire' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison : Commentaires (Avec id propre, car plusieurs commentaires par utilisateur/mémoire)
CREATE TABLE commenter (
  id_com INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_user INT UNSIGNED NOT NULL,
  id_memoire INT UNSIGNED NOT NULL,
  contenu LONGTEXT NOT NULL,
  date_pub TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES utilisateur(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_memoire) REFERENCES memoire(id_memoire) ON DELETE CASCADE,
  INDEX idx_memoire_date (id_memoire, date_pub)
) ENGINE=InnoDB COMMENT='Association : Commentaires sur un mémoire (plusieurs par utilisateur autorisés)' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison : Évaluation de mémoire par jury (Professeur évalue un mémoire avec un rôle)
CREATE TABLE evaluer (
  id_user_prof INT UNSIGNED NOT NULL,
  id_memoire INT UNSIGNED NOT NULL,
  id_role INT UNSIGNED NOT NULL,
  date_eval TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user_prof, id_memoire, id_role),
  FOREIGN KEY (id_user_prof) REFERENCES utilisateur(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_memoire) REFERENCES memoire(id_memoire) ON DELETE CASCADE,
  FOREIGN KEY (id_role) REFERENCES role_jury(id_role) ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Association : Professeur évalue un mémoire dans un rôle spécifique' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. INSERTIONS DE DONNÉES DE BASE (Dictionnaires)
-- ============================================================================

-- Niveaux d'études
INSERT INTO niveau_etude (libelle, ordre) VALUES 
  ('Licence 1', 1),
  ('Licence 2', 2),
  ('Licence 3', 3),
  ('Master 1', 4),
  ('Master 2', 5);

-- Statuts de mémoire
INSERT INTO statut_memoire (libelle, description) VALUES 
  ('Brouillon', 'En cours de rédaction'),
  ('En attente de validation', 'Soumis pour révision'),
  ('Validé', 'Accepté par le jury'),
  ('Rejeté', 'Non conforme aux critères'),
  ('Archivé', 'Mémoire ancien');

-- Rôles de jury
INSERT INTO role_jury (libelle, description) VALUES 
  ('Directeur', 'Encadrant principal'),
  ('Co-Directeur', 'Encadrant secondaire'),
  ('Examinateur', 'Membre du jury');

-- ============================================================================
-- FIN DU SCRIPT
-- ============================================================================
