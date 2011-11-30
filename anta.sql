-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Mer 30 Novembre 2011 à 13:09
-- Version du serveur: 5.1.37
-- Version de PHP: 5.2.10-2ubuntu6.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `anta`
--

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `id_project` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `database` varchar(32) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_project`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `quotas`
--

CREATE TABLE IF NOT EXISTS `quotas` (
  `id_quota` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(2) NOT NULL,
  `request_length` int(11) NOT NULL COMMENT 'length from strlen',
  `response_length` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_quota`),
  KEY `service` (`service`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33022 ;

-- --------------------------------------------------------

--
-- Structure de la table `routines`
--

CREATE TABLE IF NOT EXISTS `routines` (
  `id_routine` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'start' COMMENT 'start|die|died',
  PRIMARY KEY (`id_routine`),
  UNIQUE KEY `id_user` (`id_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

CREATE TABLE IF NOT EXISTS `services` (
  `id_service` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(2) NOT NULL,
  `name` varchar(50) NOT NULL,
  `url` text NOT NULL,
  `table_prefix` varchar(3) NOT NULL,
  PRIMARY KEY (`id_service`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Structure de la table `threads`
--

CREATE TABLE IF NOT EXISTS `threads` (
  `id_thread` int(11) NOT NULL AUTO_INCREMENT,
  `id_routine` int(11) NOT NULL,
  `type` varchar(2) NOT NULL,
  `order` int(11) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'ready' COMMENT 'ready | done | working',
  PRIMARY KEY (`id_thread`),
  UNIQUE KEY `type_routine` (`type`,`id_routine`),
  KEY `type` (`type`,`order`,`status`),
  KEY `id_routine` (`id_routine`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=128 ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL,
  `realname` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'researcher',
  `salt` varchar(32) NOT NULL,
  `passwd` varchar(32) NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `name` (`name`,`email`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=88 ;

-- --------------------------------------------------------

--
-- Structure de la table `users_projects`
--

CREATE TABLE IF NOT EXISTS `users_projects` (
  `id_user` int(11) NOT NULL,
  `id_project` int(11) NOT NULL,
  UNIQUE KEY `id_user` (`id_user`,`id_project`),
  KEY `id_project` (`id_project`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `routines`
--
ALTER TABLE `routines`
  ADD CONSTRAINT `routines_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `threads`
--
ALTER TABLE `threads`
  ADD CONSTRAINT `threads_ibfk_1` FOREIGN KEY (`id_routine`) REFERENCES `routines` (`id_routine`) ON DELETE CASCADE;

--
-- Contraintes pour la table `users_projects`
--
ALTER TABLE `users_projects`
  ADD CONSTRAINT `users_projects_ibfk_2` FOREIGN KEY (`id_project`) REFERENCES `projects` (`id_project`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_projects_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;
