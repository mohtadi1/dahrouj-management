-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 09 avr. 2026 à 22:46
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
-- Base de données : `dahrouj_textile`
--

-- --------------------------------------------------------

--
-- Structure de la table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('asset','liability','equity','revenue','expense') NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `balance` decimal(15,3) DEFAULT 0.000,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `accounts`
--

INSERT INTO `accounts` (`id`, `account_code`, `account_name`, `account_type`, `parent_id`, `balance`, `description`, `status`, `created_at`) VALUES
(1, '1000', 'Actifs', 'asset', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(2, '1100', 'Trésorerie', 'asset', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(3, '1110', 'Caisse', 'asset', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(4, '1120', 'Banque', 'asset', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(5, '1200', 'Clients', 'asset', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(6, '1300', 'Stock', 'asset', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(7, '2000', 'Passifs', 'liability', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(8, '2100', 'Fournisseurs', 'liability', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(9, '2200', 'Dettes fiscales', 'liability', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(10, '3000', 'Capitaux', 'equity', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(11, '3100', 'Capital social', 'equity', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(12, '4000', 'Produits', 'revenue', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(13, '4100', 'Ventes', 'revenue', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(14, '5000', 'Charges', 'expense', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(15, '5100', 'Achats', 'expense', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(16, '5200', 'Frais généraux', 'expense', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(17, '5300', 'Salaires', 'expense', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59'),
(18, '5400', 'Transport', 'expense', NULL, 0.000, NULL, 'active', '2026-04-07 14:56:59');

-- --------------------------------------------------------

--
-- Structure de la table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: admin', '127.0.0.1', '2026-04-07 15:38:49'),
(2, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: admin', '127.0.0.1', '2026-04-07 15:38:55'),
(3, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: admin', '127.0.0.1', '2026-04-07 15:40:12'),
(4, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: admin', '127.0.0.1', '2026-04-07 15:40:42'),
(5, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: admin', '127.0.0.1', '2026-04-07 15:43:37'),
(6, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: daii', '127.0.0.1', '2026-04-07 15:46:59'),
(7, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: daii', '127.0.0.1', '2026-04-07 15:47:44'),
(8, 2, 'Connexion réussie', 'user', 2, NULL, '127.0.0.1', '2026-04-07 15:51:11'),
(9, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-07 15:55:24'),
(10, 2, 'Connexion réussie', 'user', 2, NULL, '127.0.0.1', '2026-04-07 16:13:46'),
(11, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-07 18:27:28'),
(12, 2, 'Création client', 'customer', 1, NULL, '::1', '2026-04-07 18:41:29'),
(13, 2, 'Création article', 'article', 1, NULL, '::1', '2026-04-07 18:42:37'),
(14, 2, 'Création commande', 'order', 1, NULL, '::1', '2026-04-07 18:43:44'),
(15, 2, 'Création fournisseur', 'partner', 1, NULL, '::1', '2026-04-07 18:45:53'),
(16, 2, 'Création achat', 'purchase', 1, NULL, '::1', '2026-04-07 18:46:33'),
(17, 2, 'Création dépense', 'expense', 1, NULL, '::1', '2026-04-07 18:49:26'),
(18, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-07 18:50:09'),
(19, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-07 18:50:34'),
(20, 2, 'Création utilisateur', 'user', 3, NULL, '::1', '2026-04-07 18:51:00'),
(21, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-07 18:51:13'),
(22, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: attig', '::1', '2026-04-07 18:51:23'),
(23, 3, 'Connexion réussie', 'user', 3, NULL, '::1', '2026-04-07 18:51:33'),
(24, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-07 19:17:10'),
(25, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-07 20:14:14'),
(26, 3, 'Connexion réussie', 'user', 3, NULL, '::1', '2026-04-07 20:14:29'),
(27, 3, 'Déconnexion', 'user', 3, NULL, '::1', '2026-04-08 09:04:58'),
(28, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-08 09:05:16'),
(29, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 13:17:46'),
(30, 3, 'Connexion réussie', 'user', 3, NULL, '::1', '2026-04-09 13:17:53'),
(31, 3, 'Déconnexion', 'user', 3, NULL, '::1', '2026-04-09 13:19:19'),
(32, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 13:19:31'),
(33, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 13:47:48'),
(34, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 13:50:08'),
(35, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 13:56:12'),
(36, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 13:56:18'),
(37, 2, 'Création utilisateur', 'user', 4, NULL, '::1', '2026-04-09 13:59:31'),
(38, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 13:59:36'),
(39, 4, 'Connexion réussie', 'user', 4, NULL, '::1', '2026-04-09 13:59:48'),
(40, 4, 'Mise à jour du profil', 'user', 4, NULL, '::1', '2026-04-09 14:00:13'),
(41, 4, 'Déconnexion', 'user', 4, NULL, '::1', '2026-04-09 14:08:11'),
(42, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: daii', '::1', '2026-04-09 14:08:20'),
(43, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 14:08:32'),
(44, 2, 'Création commande', 'order', 2, NULL, '::1', '2026-04-09 14:10:09'),
(45, 2, 'Modification commande', 'order', 2, NULL, '::1', '2026-04-09 14:11:01'),
(46, 2, 'Modification commande', 'order', 1, NULL, '::1', '2026-04-09 14:11:34'),
(47, 2, 'Modification article', 'article', 1, NULL, '::1', '2026-04-09 14:12:08'),
(48, 2, 'Modification article', 'article', 1, NULL, '::1', '2026-04-09 14:12:26'),
(49, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 14:17:25'),
(50, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: admin', '::1', '2026-04-09 14:17:32'),
(51, 1, 'Connexion réussie', 'user', 1, NULL, '::1', '2026-04-09 14:17:36'),
(52, 1, 'Création commande', 'order', 3, NULL, '::1', '2026-04-09 14:19:28'),
(53, 1, 'Modification commande', 'order', 3, NULL, '::1', '2026-04-09 14:21:18'),
(54, 1, 'Modification article', 'article', 1, NULL, '::1', '2026-04-09 14:21:47'),
(55, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: daii', '127.0.0.1', '2026-04-09 14:26:36'),
(56, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: daii', '127.0.0.1', '2026-04-09 14:26:40'),
(57, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: daii', '127.0.0.1', '2026-04-09 14:26:46'),
(58, NULL, 'Tentative de connexion échouée', 'user', NULL, 'Username: daii', '127.0.0.1', '2026-04-09 14:26:56'),
(59, 2, 'Connexion réussie', 'user', 2, NULL, '127.0.0.1', '2026-04-09 14:27:09'),
(60, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 14:33:51'),
(61, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 15:04:02'),
(62, 1, 'Connexion réussie', 'user', 1, NULL, '::1', '2026-04-09 15:04:09'),
(63, 1, 'Déconnexion', 'user', 1, NULL, '::1', '2026-04-09 15:04:15'),
(64, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 15:04:23'),
(65, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 15:10:00'),
(66, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 15:13:49'),
(67, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 16:33:45'),
(68, 1, 'Connexion réussie', 'user', 1, NULL, '::1', '2026-04-09 16:33:54'),
(69, 1, 'Déconnexion', 'user', 1, NULL, '::1', '2026-04-09 16:34:19'),
(70, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 16:34:26'),
(71, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 17:24:05'),
(72, 4, 'Connexion réussie', 'user', 4, NULL, '::1', '2026-04-09 17:24:11'),
(73, 4, 'Déconnexion', 'user', 4, NULL, '::1', '2026-04-09 17:24:14'),
(74, 4, 'Connexion réussie', 'user', 4, NULL, '::1', '2026-04-09 17:24:23'),
(75, 4, 'Déconnexion', 'user', 4, NULL, '::1', '2026-04-09 17:24:49'),
(76, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 17:24:54'),
(77, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 17:25:14'),
(78, 4, 'Connexion réussie', 'user', 4, NULL, '::1', '2026-04-09 17:25:21'),
(79, 4, 'Déconnexion', 'user', 4, NULL, '::1', '2026-04-09 17:25:30'),
(80, 3, 'Connexion réussie', 'user', 3, NULL, '::1', '2026-04-09 17:25:36'),
(81, 3, 'Déconnexion', 'user', 3, NULL, '::1', '2026-04-09 17:26:02'),
(82, 3, 'Connexion réussie', 'user', 3, NULL, '::1', '2026-04-09 17:26:12'),
(83, 3, 'Déconnexion', 'user', 3, NULL, '::1', '2026-04-09 17:27:58'),
(84, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 17:28:06'),
(85, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 17:28:21'),
(86, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 17:28:28'),
(87, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 17:28:52'),
(88, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 17:28:58'),
(89, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 17:34:15'),
(90, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 17:34:22'),
(91, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 17:35:59'),
(92, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 17:36:04'),
(93, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 18:35:37'),
(94, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 18:40:50'),
(95, 3, 'Connexion réussie', 'user', 3, NULL, '::1', '2026-04-09 18:40:58'),
(96, 3, 'Déconnexion', 'user', 3, NULL, '::1', '2026-04-09 18:41:39'),
(97, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 18:41:50'),
(98, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 19:14:04'),
(99, 2, 'Connexion réussie', 'user', 2, NULL, '::1', '2026-04-09 19:14:15'),
(100, 2, 'Déconnexion', 'user', 2, NULL, '::1', '2026-04-09 19:17:26'),
(101, 3, 'Connexion réussie', 'user', 3, NULL, '::1', '2026-04-09 19:17:34');

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `purchase_price` decimal(15,3) DEFAULT 0.000,
  `sale_price` decimal(15,3) DEFAULT 0.000,
  `wholesale_price` decimal(15,3) DEFAULT 0.000,
  `quantity` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 10,
  `unit` varchar(20) DEFAULT 'piece',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`id`, `code`, `name`, `description`, `category_id`, `purchase_price`, `sale_price`, `wholesale_price`, `quantity`, `min_stock`, `unit`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, '0001', 'chemise', 'chemise carre', 4, 14.000, 20.000, 16.000, 5, 5, 'piece', '69d7b3caefe42_amira-kirin-hookah-rose-11563158765swdhxsfg4c-removebg-preview.png', 'active', '2026-04-07 18:42:37', '2026-04-09 14:21:47');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tissus', 'Tous types de tissus textiles', NULL, 'active', '2026-04-07 14:56:59', '2026-04-07 14:56:59'),
(2, 'Fil', 'Fils pour couture et tricot', NULL, 'active', '2026-04-07 14:56:59', '2026-04-07 14:56:59'),
(3, 'Accessoires', 'Accessoires textiles et mercerie', NULL, 'active', '2026-04-07 14:56:59', '2026-04-07 14:56:59'),
(4, 'Vêtements', 'Vêtements en gros', NULL, 'active', '2026-04-07 14:56:59', '2026-04-07 14:56:59'),
(5, 'Linge de maison', 'Draps, serviettes, etc.', NULL, 'active', '2026-04-07 14:56:59', '2026-04-07 14:56:59');

-- --------------------------------------------------------

--
-- Structure de la table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `company_name` varchar(200) DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone2` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'Tunisia',
  `tax_number` varchar(50) DEFAULT NULL,
  `registration_number` varchar(50) DEFAULT NULL,
  `credit_limit` decimal(15,3) DEFAULT 0.000,
  `balance` decimal(15,3) DEFAULT 0.000,
  `status` enum('active','inactive') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `customers`
--

INSERT INTO `customers` (`id`, `code`, `company_name`, `contact_name`, `email`, `phone`, `phone2`, `address`, `city`, `country`, `tax_number`, `registration_number`, `credit_limit`, `balance`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'CLI-2026-000001', 'ste sallemi', 'salem', 'salem@gmail.com', '+21628853280', '', 'rue hamee', 'monastir', 'Tunisia', '185d25', '157885', 50000.000, 0.000, 'active', '', '2026-04-07 18:41:28', '2026-04-07 18:41:28');

-- --------------------------------------------------------

--
-- Structure de la table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `delivery_number` varchar(50) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `delivery_date` date DEFAULT curdate(),
  `expected_date` date DEFAULT NULL,
  `actual_date` date DEFAULT NULL,
  `status` enum('pending','in_transit','delivered','returned') DEFAULT 'pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `transport_cost` decimal(15,3) DEFAULT 0.000,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `delivery_items`
--

CREATE TABLE `delivery_items` (
  `id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `order_item_id` int(11) DEFAULT NULL,
  `quantity_delivered` int(11) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `expense_number` varchar(50) NOT NULL,
  `expense_date` date DEFAULT curdate(),
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,3) NOT NULL,
  `payment_method` enum('cash','check','transfer') DEFAULT 'cash',
  `partner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','paid') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `expenses`
--

INSERT INTO `expenses` (`id`, `expense_number`, `expense_date`, `category`, `description`, `amount`, `payment_method`, `partner_id`, `user_id`, `status`, `created_at`) VALUES
(1, 'DEP-2026-000001', '2026-04-07', 'Salaires', 'ste dahrouj', 2800.000, 'cash', 1, 2, 'approved', '2026-04-07 18:49:26');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` date DEFAULT curdate(),
  `delivery_date` date DEFAULT NULL,
  `subtotal` decimal(15,3) DEFAULT 0.000,
  `discount` decimal(15,3) DEFAULT 0.000,
  `tax_rate` decimal(5,2) DEFAULT 19.00,
  `tax_amount` decimal(15,3) DEFAULT 0.000,
  `total` decimal(15,3) DEFAULT 0.000,
  `paid_amount` decimal(15,3) DEFAULT 0.000,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `payment_method` enum('cash','check','transfer','credit') DEFAULT 'cash',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `user_id`, `order_date`, `delivery_date`, `subtotal`, `discount`, `tax_rate`, `tax_amount`, `total`, `paid_amount`, `status`, `payment_status`, `payment_method`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'CMD-2026-000001', 1, 2, '2026-04-07', '2026-05-07', 20.000, 0.000, 19.00, 3.800, 23.800, 0.000, 'cancelled', 'unpaid', 'cash', '', '2026-04-07 18:43:44', '2026-04-09 14:11:34'),
(2, 'CMD-2026-000002', 1, 2, '2026-04-09', '2026-04-10', 1800.000, 0.000, 19.00, 342.000, 2142.000, 0.000, 'confirmed', 'paid', 'cash', '', '2026-04-09 14:10:09', '2026-04-09 14:11:01'),
(3, 'CMD-2026-000003', 1, 1, '2026-04-09', '2026-04-10', 20.000, 0.000, 19.00, 3.800, 23.800, 0.000, 'cancelled', 'unpaid', 'cash', '', '2026-04-09 14:19:28', '2026-04-09 14:21:18');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,3) NOT NULL,
  `discount` decimal(15,3) DEFAULT 0.000,
  `total` decimal(15,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `article_id`, `quantity`, `unit_price`, `discount`, `total`) VALUES
(1, 1, 1, 1, 20.000, 0.000, 20.000),
(2, 2, 1, 90, 20.000, 0.000, 1800.000),
(3, 3, 1, 1, 20.000, 0.000, 20.000);

-- --------------------------------------------------------

--
-- Structure de la table `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone2` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'Tunisia',
  `tax_number` varchar(50) DEFAULT NULL,
  `registration_number` varchar(50) DEFAULT NULL,
  `partner_type` enum('supplier','transporter','other') DEFAULT 'supplier',
  `balance` decimal(15,3) DEFAULT 0.000,
  `status` enum('active','inactive') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `partners`
--

INSERT INTO `partners` (`id`, `code`, `company_name`, `contact_name`, `email`, `phone`, `phone2`, `address`, `city`, `country`, `tax_number`, `registration_number`, `partner_type`, `balance`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'FRS-2026-000001', 'ste attig', 'salem', 'salem@gmail.com', '+21628853281', NULL, 'masr', 'cairo', 'Tunisia', NULL, NULL, 'supplier', 0.000, 'active', '', '2026-04-07 18:45:53', '2026-04-07 18:45:53');

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_number` varchar(50) NOT NULL,
  `payment_date` date DEFAULT curdate(),
  `entity_type` enum('customer','partner') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `reference_type` enum('order','purchase','expense','other') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `amount` decimal(15,3) NOT NULL,
  `payment_method` enum('cash','check','transfer','credit') DEFAULT 'cash',
  `check_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT curdate(),
  `expected_date` date DEFAULT NULL,
  `subtotal` decimal(15,3) DEFAULT 0.000,
  `discount` decimal(15,3) DEFAULT 0.000,
  `tax_amount` decimal(15,3) DEFAULT 0.000,
  `total` decimal(15,3) DEFAULT 0.000,
  `paid_amount` decimal(15,3) DEFAULT 0.000,
  `status` enum('pending','ordered','received','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `purchases`
--

INSERT INTO `purchases` (`id`, `purchase_number`, `partner_id`, `user_id`, `purchase_date`, `expected_date`, `subtotal`, `discount`, `tax_amount`, `total`, `paid_amount`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ACH-2026-000001', 1, 2, '2026-04-07', '2026-05-07', 700.000, 0.000, 133.000, 833.000, 0.000, 'pending', 'unpaid', '', '2026-04-07 18:46:33', '2026-04-07 18:46:33');

-- --------------------------------------------------------

--
-- Structure de la table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,3) NOT NULL,
  `discount` decimal(15,3) DEFAULT 0.000,
  `total` decimal(15,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `article_id`, `quantity`, `unit_price`, `discount`, `total`) VALUES
(1, 1, 1, 50, 14.000, 0.000, 700.000);

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `transaction_date` date DEFAULT curdate(),
  `account_id` int(11) DEFAULT NULL,
  `reference_type` enum('order','purchase','expense','income','transfer','other') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `debit` decimal(15,3) DEFAULT 0.000,
  `credit` decimal(15,3) DEFAULT 0.000,
  `description` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','manager','employee') DEFAULT 'employee',
  `status` enum('active','inactive') DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `role`, `status`, `avatar`, `last_login`, `created_at`, `updated_at`, `profile_photo`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'admin@dahrouj.tn', NULL, 'admin', 'active', NULL, '2026-04-09 16:33:54', '2026-04-07 14:56:59', '2026-04-09 16:33:54', NULL),
(2, 'daii', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'daii', 'admin@dahrouj.tn', NULL, 'admin', 'active', NULL, '2026-04-09 19:14:15', '2026-04-07 15:46:37', '2026-04-09 19:14:15', '/dahrouj-management/includes/uploads/profiles/user_2_1775756153.jpg'),
(3, 'mohamed', '$2y$10$0Dmz475TeRmTKlf.v4MbE.MYU.EQOGUalQMRhhLXybdAesPGs2S3.', 'attig', 'salem@gmail.com', NULL, 'employee', 'active', NULL, '2026-04-09 19:17:34', '2026-04-07 18:51:00', '2026-04-09 19:17:34', '/dahrouj-management/includes/uploads/profiles/user_3_1775755553.png'),
(4, 'iyed', '$2y$10$VEI6fxUtslu/fvc2tahwX.tOLIoANuwH5lt7Nf8latAugepQK5J/y', 'azaiez panda', '02daddy@gmail.com', '', 'employee', 'active', NULL, '2026-04-09 17:25:21', '2026-04-09 13:59:31', '2026-04-09 17:25:21', '/dahrouj-management/includes/uploads/profiles/user_4_1775755472.jpg');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_code` (`account_code`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Index pour la table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Index pour la table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_number` (`delivery_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `partner_id` (`partner_id`);

--
-- Index pour la table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_id` (`delivery_id`),
  ADD KEY `order_item_id` (`order_item_id`);

--
-- Index pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_number` (`expense_number`),
  ADD KEY `partner_id` (`partner_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_number` (`purchase_number`),
  ADD KEY `partner_id` (`partner_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_number` (`transaction_number`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT pour la table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `delivery_items`
--
ALTER TABLE `delivery_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD CONSTRAINT `delivery_items_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_items_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
