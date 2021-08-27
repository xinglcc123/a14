/*
 Navicat MySQL Data Transfer

 Source Server         : 本機
 Source Server Type    : MySQL
 Source Server Version : 100413
 Source Host           : localhost:3306
 Source Schema         : a14

 Target Server Type    : MySQL
 Target Server Version : 100413
 File Encoding         : 65001

 Date: 05/08/2021 01:17:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tmp_a14_account
-- ----------------------------
DROP TABLE IF EXISTS `tmp_a14_account`;
CREATE TABLE `tmp_a14_account`  (
  `uid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` int(11) NULL DEFAULT NULL,
  `useracc` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `wallat` decimal(15, 2) NULL DEFAULT NULL,
  `cp` decimal(15, 2) NULL DEFAULT NULL,
  `ty` decimal(15, 2) NULL DEFAULT NULL,
  `bb` decimal(15, 2) NULL DEFAULT NULL,
  `ag` decimal(15, 2) NULL DEFAULT NULL,
  `ibc` decimal(15, 2) NULL DEFAULT NULL,
  `pt` decimal(15, 2) NULL DEFAULT NULL,
  `mwg` decimal(15, 2) NULL DEFAULT NULL,
  `lebo` decimal(15, 2) NULL DEFAULT NULL,
  `ds` decimal(15, 2) NULL DEFAULT NULL,
  `ab` decimal(15, 2) NULL DEFAULT NULL,
  `pp` decimal(15, 2) NULL DEFAULT NULL,
  `cmd` decimal(15, 2) NULL DEFAULT NULL,
  `vg` decimal(15, 2) NULL DEFAULT NULL,
  `vgs` decimal(15, 2) NULL DEFAULT NULL,
  `cq` decimal(15, 2) NULL DEFAULT NULL,
  `bc` decimal(15, 2) NULL DEFAULT NULL,
  `bg` decimal(15, 2) NULL DEFAULT NULL,
  `png` decimal(15, 2) NULL DEFAULT NULL,
  `jdb` decimal(15, 2) NULL DEFAULT NULL,
  `fg` decimal(15, 2) NULL DEFAULT NULL,
  `ky` decimal(15, 2) NULL DEFAULT NULL,
  `nw` decimal(15, 2) NULL DEFAULT NULL,
  `lg` decimal(15, 2) NULL DEFAULT NULL,
  `dt` decimal(15, 2) NULL DEFAULT NULL,
  `wm` decimal(15, 2) NULL DEFAULT NULL,
  `sc` decimal(15, 2) NULL DEFAULT NULL,
  `bsp` decimal(15, 2) NULL DEFAULT NULL,
  `ebet` decimal(15, 2) NULL DEFAULT NULL,
  `sg` decimal(15, 2) NULL DEFAULT NULL,
  `pg` decimal(15, 2) NULL DEFAULT NULL,
  `mgp` decimal(15, 2) NULL DEFAULT NULL,
  `th` decimal(15, 2) NULL DEFAULT NULL,
  `ogp` decimal(15, 2) NULL DEFAULT NULL,
  `tn` decimal(15, 2) NULL DEFAULT NULL,
  `balance` decimal(15, 2) NOT NULL,
  `mobile` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `email` varchar(127) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `wechat` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `qqskype` varchar(63) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `username` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `bank` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `banknum` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `bankaddress` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `province` varchar(63) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `city` varchar(63) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `registed_at` datetime(0) NULL DEFAULT NULL,
  `lastlogin_at` datetime(0) NULL DEFAULT NULL,
  `last_d_date` datetime(0) NULL DEFAULT NULL,
  `last_w_date` datetime(0) NULL DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NULL DEFAULT 0,
  `frozen` tinyint(3) UNSIGNED NULL DEFAULT 0,
  `birthday` date NULL DEFAULT NULL,
  `deposit` decimal(15, 2) NULL DEFAULT NULL,
  `deposit_count` int(11) NOT NULL DEFAULT 0,
  `withdrawal` decimal(15, 2) NULL DEFAULT NULL,
  `withdrawal_count` int(11) NOT NULL DEFAULT 0,
  `level` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `remark` text CHARACTER SET utf8 COLLATE utf8_bin NULL,
  `activebet` decimal(16, 2) NULL DEFAULT NULL,
  `payout` decimal(15, 2) NULL DEFAULT NULL,
  `chinese_nickname` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `english_nickname` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `check_login` tinyint(1) UNSIGNED NULL DEFAULT 0,
  `check_ccl` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `check_detail` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `back_tag` tinyint(1) NOT NULL DEFAULT 0,
  `check_d_w` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `upperagent` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `check_ccl_all` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  PRIMARY KEY (`uid`) USING BTREE,
  UNIQUE INDEX `useracc`(`useracc`) USING BTREE,
  UNIQUE INDEX `userid`(`userid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1014354 CHARACTER SET = utf8 COLLATE = utf8_bin ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
