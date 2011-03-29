-- this is different from the main vega.sql :
-- 1. absolutely nothing is in
-- 2. removes all existing tables automatically before creation


-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- G�n�r� le : Sam 23 Janvier 2010 � 17:12
-- Version du serveur: 5.1.37
-- Version de PHP: 5.2.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de donn�es: `vega_test`
--

-- --------------------------------------------------------

--
-- Structure de la table `primitive_prm`
--

DROP TABLE IF EXISTS `primitive_prm`;
CREATE TABLE IF NOT EXISTS `primitive_prm` (
  `id_prm` int(11) NOT NULL AUTO_INCREMENT,
  `name_prm` varchar(150) NOT NULL,
  `sqltype_prm` varchar(150) NOT NULL,
  PRIMARY KEY (`id_prm`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `primitive_prm`
--

INSERT INTO `primitive_prm` VALUES(1, 'Simple text', '');
INSERT INTO `primitive_prm` VALUES(2, 'Number', '');
INSERT INTO `primitive_prm` VALUES(3, 'Date', '');
INSERT INTO `primitive_prm` VALUES(4, 'Formatted text', '');
INSERT INTO `primitive_prm` VALUES(5, 'Yes/No', '');
INSERT INTO `primitive_prm` VALUES(6, 'Url', '');

-- --------------------------------------------------------

--
-- Structure de la table `scope_custom_scc`
--

DROP TABLE IF EXISTS `scope_custom_scc`;
CREATE TABLE IF NOT EXISTS `scope_custom_scc` (
  `id_scp_scc` int(11) NOT NULL,
  `id_usr_scc` int(11) NOT NULL,
  `role_scc` enum('editor','viewer') NOT NULL,
  PRIMARY KEY (`id_scp_scc`,`id_usr_scc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `scope_custom_scc`
--

INSERT INTO `scope_custom_scc` VALUES(1, 1, 'editor');
INSERT INTO `scope_custom_scc` VALUES(2, 1, 'editor');

-- --------------------------------------------------------

--
-- Structure de la table `scope_scp`
--

DROP TABLE IF EXISTS `scope_scp`;
CREATE TABLE IF NOT EXISTS `scope_scp` (
  `id_scp` int(11) NOT NULL AUTO_INCREMENT,
  `inherit_from_scp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_scp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `scope_scp`
--

INSERT INTO `scope_scp` VALUES(1, NULL);
INSERT INTO `scope_scp` VALUES(2, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_usr`
--

DROP TABLE IF EXISTS `user_usr`;
CREATE TABLE IF NOT EXISTS `user_usr` (
  `id_usr` int(11) NOT NULL AUTO_INCREMENT,
  `id_vg_usr` int(11) DEFAULT NULL,
  `email_usr` varchar(40) NOT NULL,
  `pass_usr` varchar(40) NOT NULL,
  `salt_usr` varchar(40) NOT NULL,
  `pseudo_usr` varchar(40) NOT NULL,
  `account_status_usr` varchar(40) DEFAULT NULL,
  `token_usr` varchar(255) DEFAULT NULL,
  `date_created_usr` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usr`),
  KEY `fk_vg_usr` (`id_vg_usr`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Contenu de la table `user_usr`
--

INSERT INTO `user_usr` VALUES(1, 1, 'public@getvega.com', '80fced5eaac3847c5a61165305e80425ced8be68', 'a3b148229ff7109975e26d969fa7c9cd8e258d26', 'public', 'validated', 1234, '2009-04-14 17:02:38');
INSERT INTO `user_usr` VALUES(2, 2, 'ayn_nian@hotmail.com', '80fced5eaac3847c5a61165305e80425ced8be68', 'a3b148229ff7109975e26d969fa7c9cd8e258d26', 'andres', 'validated', 1234, '2009-04-14 17:02:38');

-- --------------------------------------------------------

--
-- Structure de la table `vegalinktype_vlt`
--

DROP TABLE IF EXISTS `vegalinktype_vlt`;
CREATE TABLE IF NOT EXISTS `vegalinktype_vlt` (
  `id_vlt` int(11) NOT NULL AUTO_INCREMENT,
  `name_vlt` varchar(40) DEFAULT NULL,
  `unique_from_vlt` varchar(5) DEFAULT NULL,
  `unique_to_vlt` varchar(5) DEFAULT NULL,
  `unidirectionnal_vlt` tinyint(1) DEFAULT NULL,
  `unique_vlt` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_vlt`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `vegalinktype_vlt`
--

INSERT INTO `vegalinktype_vlt` VALUES(1, 'vegaproperty', NULL, NULL, 1, 0);
INSERT INTO `vegalinktype_vlt` VALUES(2, 'generic', NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `vegalink_property_vlp`
--

DROP TABLE IF EXISTS `vegalink_property_vlp`;
CREATE TABLE IF NOT EXISTS `vegalink_property_vlp` (
  `id_vgl_vlp` int(11) NOT NULL,
  `id_prp_vlp` int(11) NOT NULL,
  `position_vlp` int(11) NOT NULL COMMENT 'this position is used when this property is a list of values',
  KEY `fk_prp_vlp` (`id_prp_vlp`),
  KEY `fk_vgl_vlp` (`id_vgl_vlp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `vegalink_property_vlp`
--


-- --------------------------------------------------------

--
-- Structure de la table `vegalink_vgl`
--

DROP TABLE IF EXISTS `vegalink_vgl`;
CREATE TABLE IF NOT EXISTS `vegalink_vgl` (
  `id_vgl` int(11) NOT NULL AUTO_INCREMENT,
  `from_id_vg_vgl` int(11) NOT NULL,
  `from_rv_vg_vgl` int(11) NOT NULL,
  `to_id_vg_vgl` int(11) DEFAULT NULL,
  `id_vlt_vgl` int(11) NOT NULL,
  PRIMARY KEY (`id_vgl`),
  KEY `fk_vlt_vgl` (`id_vlt_vgl`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `vegalink_vgl`
--

INSERT INTO `vegalink_vgl` VALUES(1, 2, 1, 1, 2);


-- --------------------------------------------------------

--
-- Structure de la table `vegaproperty_prp`
--

DROP TABLE IF EXISTS `vegaproperty_prp`;
CREATE TABLE IF NOT EXISTS `vegaproperty_prp` (
  `id_prp` int(11) NOT NULL AUTO_INCREMENT,
  `id_vgt_prp` int(11) NOT NULL,
  `rv_vgt_prp` int(11) NOT NULL,
  `name_prp` varchar(150) NOT NULL,
  `type_id_prm_prp` int(11) DEFAULT NULL,
  `type_id_vgt_prp` int(11) DEFAULT NULL,
  `position_prp` int(11) NOT NULL,
  PRIMARY KEY (`id_prp`,`id_vgt_prp`,`rv_vgt_prp`),
  KEY `fk_vgt_prp` (`type_id_vgt_prp`),
  KEY `fk_prm_prp` (`type_id_prm_prp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

--
-- Contenu de la table `vegaproperty_prp`
--

INSERT INTO `vegaproperty_prp` (`id_prp`, `id_vgt_prp`, `rv_vgt_prp`, `name_prp`, `type_id_prm_prp`, `type_id_vgt_prp`, `position_prp`) VALUES
(1, 7, 1, 'first name', 1, NULL, 0),
(2, 7, 1, 'last name', 1, NULL, 1),
(3, 7, 1, 'picture', 1, NULL, 2),
(4, 7, 1, 'email', 1, NULL, 3),
(5, 7, 1, 'phone', 1, NULL, 4),
(6, 7, 1, 'address', 1, NULL, 5),
(7, 7, 1, 'website 2', 1, NULL, 6),
(8, 7, 1, 'birthday', 3, NULL, 7),
(9, 7, 1, 'notes', 4, NULL, 8),
(10, 7, 1, 'id Google', 1, NULL, 9);
-- --------------------------------------------------------

--
-- Structure de la table `vegatype_vgt`
--

DROP TABLE IF EXISTS `vegatype_vgt`;
CREATE TABLE IF NOT EXISTS `vegatype_vgt` (
  `id_vgt` int(11) NOT NULL AUTO_INCREMENT,
  `rv_vgt` int(11) NOT NULL DEFAULT '1',
  `status_vgt` enum('disabled','enabled','deleted') NOT NULL DEFAULT 'enabled',
  `name_vgt` varchar(150) NOT NULL,
  `fqn_vgt` varchar(150) NOT NULL,
  `id_usr_vgt` int(11) NOT NULL DEFAULT '0',
  `id_scp_vgt` int(11) NOT NULL,
  `date_created_vgt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vgt`,`rv_vgt`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Contenu de la table `vegatype_vgt`
--
INSERT INTO `vegatype_vgt` (`id_vgt`, `rv_vgt`, `status_vgt`, `name_vgt`, `fqn_vgt`, `id_usr_vgt`, `id_scp_vgt`, `date_created_vgt`) VALUES
(7, 1, 'enabled', 'Contact', 'Mira_Core_Contact', 0, 0, '2010-01-08 12:01:27');

-- --------------------------------------------------------

--
-- Structure de la table `vega_vg`
--

DROP TABLE IF EXISTS `vega_vg`;
CREATE TABLE IF NOT EXISTS `vega_vg` (
  `id_vg` int(11) NOT NULL AUTO_INCREMENT,
  `rv_vg` int(11) NOT NULL,
  `id_vgt_vg` int(11) NOT NULL,
  `rv_vgt_vg` int(11) NOT NULL DEFAULT '1',
  `id_usr_vg` int(11) NOT NULL DEFAULT '0',
  `id_scp_vg` int(11) NOT NULL,
  `name_vg` varchar(200) NOT NULL,
  `status_vg` enum('disabled','enabled','deleted') NOT NULL DEFAULT 'enabled',
  `date_created_vg` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vg`,`rv_vg`),
  KEY `fk_usr_vg` (`id_usr_vg`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `vega_vg`
--
INSERT INTO `vega_vg` (`id_vg`, `rv_vg`, `id_vgt_vg`, `rv_vgt_vg`, `id_usr_vg`, `id_scp_vg`, `name_vg`, `status_vg`, `date_created_vg`) VALUES
(1, 1, 7, 1, 0, 1, 'Public', 'enabled', '2010-01-08 12:27:27');
INSERT INTO `vega_vg` (`id_vg`, `rv_vg`, `id_vgt_vg`, `rv_vgt_vg`, `id_usr_vg`, `id_scp_vg`, `name_vg`, `status_vg`, `date_created_vg`) VALUES
(2, 1, 7, 1, 0, 1, 'Andres PENA', 'enabled', '2010-01-08 12:27:27');

--
-- Structure de la table `vega_7`
--

CREATE TABLE IF NOT EXISTS `vega_7` (
  `id_vg` int(11) DEFAULT NULL,
  `rv_vg` int(11) DEFAULT NULL,
  `1` varchar(200) DEFAULT NULL,
  `2` varchar(200) DEFAULT NULL,
  `3` varchar(200) DEFAULT NULL,
  `4` varchar(200) DEFAULT NULL,
  `5` varchar(200) DEFAULT NULL,
  `6` varchar(200) DEFAULT NULL,
  `7` varchar(200) DEFAULT NULL,
  `8` varchar(200) DEFAULT NULL,
  `9` varchar(200) DEFAULT NULL,
  `10` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `vega_7`
--

INSERT INTO `vega_7` (`id_vg`, `rv_vg`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`) VALUES
(1, 1, 'Public', 'Public', NULL, 'public@getvega.com', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `vega_7` (`id_vg`, `rv_vg`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`) VALUES
(2, 1, 'andres', 'pena', NULL, 'ayn_nian@hotmail.com', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------