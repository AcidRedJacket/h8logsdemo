/*
Navicat MySQL Data Transfer

Source Server         : package
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : package

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2025-12-10 21:42:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for packages
-- ----------------------------
DROP TABLE IF EXISTS `packages`;
CREATE TABLE `packages` (
  `id` bigint(20) NOT NULL,
  `personName` varchar(255) NOT NULL,
  `loggedBy` varchar(255) NOT NULL,
  `itemName` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `tracking` varchar(255) DEFAULT NULL,
  `poNumber` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `isTally` enum('Yes','No') NOT NULL,
  `isDamaged` enum('Yes','No') NOT NULL,
  `log_timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for registration_codes
-- ----------------------------
DROP TABLE IF EXISTS `registration_codes`;
CREATE TABLE `registration_codes` (
  `code` varchar(255) NOT NULL COMMENT 'The unique registration code',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 for inactive/used, 1 for active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique user identifier',
  `username` varchar(255) NOT NULL COMMENT 'Unique login identifier',
  `password` varchar(255) NOT NULL COMMENT 'Hashed password using PASSWORD_DEFAULT (PHP)',
  `role` enum('admin','user') NOT NULL DEFAULT 'user' COMMENT 'User role for permissions management',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Timestamp of user creation',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
