-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 06 mai 2025 à 01:08
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ngo_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `actions`
--

CREATE TABLE `actions` (
  `id` int(11) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `lieu` varchar(100) NOT NULL,
  `statut` enum('planifie','en_cours','termine') DEFAULT 'planifie',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `actions`
--

INSERT INTO `actions` (`id`, `titre`, `description`, `date_debut`, `date_fin`, `lieu`, `statut`, `date_creation`) VALUES
(1, 'Plantation d\'arbres', 'Campagne de reforestation dans la région parisienne', '2024-04-15', NULL, 'Paris', 'planifie', '2025-05-05 14:14:13'),
(2, 'Collecte de fonds', 'Événement caritatif pour financer nos projets', '2024-05-20', NULL, 'Lyon', 'planifie', '2025-05-05 14:14:13'),
(3, 'Formation bénévoles', 'Session de formation pour les nouveaux bénévoles', '2024-03-10', NULL, 'Marseille', 'en_cours', '2025-05-05 14:14:13'),
(4, 'Plantation d\'arbres', 'Campagne de reforestation dans la région parisienne', '2024-04-15', NULL, 'Paris', 'planifie', '2025-05-05 14:15:01'),
(5, 'Collecte de fonds', 'Événement caritatif pour financer nos projets', '2024-05-20', NULL, 'Lyon', 'planifie', '2025-05-05 14:15:01'),
(6, 'Formation bénévoles', 'Session de formation pour les nouveaux bénévoles', '2024-03-10', NULL, 'Marseille', 'en_cours', '2025-05-05 14:15:01');

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('super_admin','admin') DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`, `role`) VALUES
(3, 'HUGUE', 'admin@gmail.com', '$2y$10$Qw8by2wKNZlRiEmdpV1A1uh3Tw5Pg8mFkb.pzrDAZlagduw9LmeWy', '2025-05-05 18:14:54', 'super_admin');

-- --------------------------------------------------------

--
-- Structure de la table `membres`
--

CREATE TABLE `membres` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `pays` varchar(50) NOT NULL,
  `ville` varchar(50) NOT NULL,
  `quartier` varchar(100) NOT NULL,
  `adresse` text NOT NULL,
  `type_membre` enum('volontaire','donateur','membre_actif') NOT NULL,
  `message` text DEFAULT NULL,
  `date_inscription` datetime NOT NULL,
  `statut` enum('en_attente','approuve','rejete') DEFAULT 'en_attente',
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participants_actions`
--

CREATE TABLE `participants_actions` (
  `id` int(11) NOT NULL,
  `membre_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `actions`
--
ALTER TABLE `actions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `membres`
--
ALTER TABLE `membres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `participants_actions`
--
ALTER TABLE `participants_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `membre_id` (`membre_id`),
  ADD KEY `action_id` (`action_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `actions`
--
ALTER TABLE `actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `membres`
--
ALTER TABLE `membres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `participants_actions`
--
ALTER TABLE `participants_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `participants_actions`
--
ALTER TABLE `participants_actions`
  ADD CONSTRAINT `participants_actions_ibfk_1` FOREIGN KEY (`membre_id`) REFERENCES `membres` (`id`),
  ADD CONSTRAINT `participants_actions_ibfk_2` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
