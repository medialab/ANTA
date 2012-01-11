-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 21 dic, 2011 at 04:54 PM
-- Versione MySQL: 5.1.41
-- Versione PHP: 5.3.2-1ubuntu4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `anta`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `id_project` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `database` varchar(32) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_project`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `quotas`
--

CREATE TABLE IF NOT EXISTS `quotas` (
  `id_quota` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(2) NOT NULL,
  `request_length` int(11) NOT NULL COMMENT 'length from strlen',
  `response_length` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_quota`),
  KEY `service` (`service`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49443 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `routines`
--

CREATE TABLE IF NOT EXISTS `routines` (
  `id_routine` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'start' COMMENT 'start|die|died',
  PRIMARY KEY (`id_routine`),
  UNIQUE KEY `id_user` (`id_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `rws_entities_duplicates`
--

CREATE TABLE IF NOT EXISTS `rws_entities_duplicates` (
  `id_rws_entity_candidate` int(11) NOT NULL,
  `id_rws_entity_clone` int(11) NOT NULL,
  `ratio` float NOT NULL,
  UNIQUE KEY `id_rws_entity_candidate` (`id_rws_entity_candidate`,`id_rws_entity_clone`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `services`
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
-- Struttura della tabella `threads`
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=74 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `users_projects`
--

CREATE TABLE IF NOT EXISTS `users_projects` (
  `id_user` int(11) NOT NULL,
  `id_project` int(11) NOT NULL,
  UNIQUE KEY `id_user` (`id_user`,`id_project`),
  KEY `id_project` (`id_project`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `routines`
--
ALTER TABLE `routines`
  ADD CONSTRAINT `routines_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Limiti per la tabella `threads`
--
ALTER TABLE `threads`
  ADD CONSTRAINT `threads_ibfk_1` FOREIGN KEY (`id_routine`) REFERENCES `routines` (`id_routine`) ON DELETE CASCADE;
