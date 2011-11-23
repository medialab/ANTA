ALTER TABLE `anta_{{user}}`.`rws_entities` DROP INDEX `content` ,
ADD UNIQUE `content` ( `content` , `service` , `pid` ) 

CREATE TABLE IF NOT EXISTS  `anta_{{user}}`.`graphs` (
				`id_graph` INT NOT NULL ,
				`engine` VARCHAR( 64 ) NOT NULL COMMENT  'the engine used, e.g tina | simple gexf',
				`description` VARCHAR( 200 ) NOT NULL ,
				`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
				`localUrl` TEXT NOT NULL ,
				`status` INT NOT NULL COMMENT  'ok | ko',
				`error` TEXT NOT NULL COMMENT  'if status = ko, describe the error',
				INDEX (  `date` ,  `status` )
				) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

-- ALTER TABLE `anta_{{user}}`.`graphs`  ADD `session_hash` VARCHAR(64) NOT NULL AFTER `engine`,  ADD UNIQUE (`session_hash`) ;


-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeu 16 Juin 2011 à 13:40
-- Version du serveur: 5.1.37
-- Version de PHP: 5.2.10-2ubuntu6.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";# MySQL n'a retourné aucun enregistrement.


--
-- Base de données: `anta_iddri`
--

-- --------------------------------------------------------

--
-- Structure de la vue `documents_tags_distribution`
-- '''removed'''
-- DROP VIEW documents_tags_distribution;# MySQL n'a retourné aucun enregistrement.

-- CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `documents_tags_distribution` AS select t.`id_tag`, `t`.`content` AS `content`,count(distinct `documents_tags`.`id_document`) AS `number_of_documents`,`t`.`id_category` AS `id_category`,`c`.`content` AS `category` from ((`documents_tags` join `tags` `t` on((`documents_tags`.`id_tag` = `t`.`id_tag`))) join `categories` `c` on((`t`.`id_category` = `c`.`id_category`))) group by `documents_tags`.`id_tag` order by `t`.`id_category`,count(distinct `documents_tags`.`id_document`) desc;# MySQL n'a retourné aucun enregistrement.


--
-- VIEW  `documents_tags_distribution`
-- Données: aucune
--# MySQL n'a retourné aucun enregistrement.
