/*
 Navicat MySQL Data Transfer

 Source Server         : 本機
 Source Server Type    : MySQL
 Source Server Version : 100414
 Source Host           : localhost:3306
 Source Schema         : hg9

 Target Server Type    : MySQL
 Target Server Version : 100414
 File Encoding         : 65001

 Date: 12/10/2020 16:21:00
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tmp_hg9_account
-- ----------------------------
CREATE TABLE `tmp_h28_account`  (
  `uid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` int(11) NULL DEFAULT NULL,
  `useracc` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `wallat` decimal(15, 2) NULL DEFAULT NULL,
  `SP` decimal(15, 2) NULL DEFAULT NULL,
  `SS` decimal(15, 2) NULL DEFAULT NULL,
  `BT` decimal(15, 2) NULL DEFAULT NULL,
  `FY` decimal(15, 2) NULL DEFAULT NULL,
  `TT` decimal(15, 2) NULL DEFAULT NULL,
  `XG` decimal(15, 2) NULL DEFAULT NULL,
  `VR` decimal(15, 2) NULL DEFAULT NULL,
  `AG` decimal(15, 2) NULL DEFAULT NULL,
  `BB` decimal(15, 2) NULL DEFAULT NULL,
  `AB` decimal(15, 2) NULL DEFAULT NULL,
  `DS` decimal(15, 2) NULL DEFAULT NULL,
  `BV` decimal(15, 2) NULL DEFAULT NULL,
  `SG` decimal(15, 2) NULL DEFAULT NULL,
  `EB` decimal(15, 2) NULL DEFAULT NULL,
  `OP` decimal(15, 2) NULL DEFAULT NULL,
  `PT` decimal(15, 2) NULL DEFAULT NULL,
  `PM` decimal(15, 2) NULL DEFAULT NULL,
  `HB` decimal(15, 2) NULL DEFAULT NULL,
  `YG` decimal(15, 2) NULL DEFAULT NULL,
  `PS` decimal(15, 2) NULL DEFAULT NULL,
  `CQ` decimal(15, 2) NULL DEFAULT NULL,
  `MP` decimal(15, 2) NULL DEFAULT NULL,
  `DB` decimal(15, 2) NULL DEFAULT NULL,
  `PP` decimal(15, 2) NULL DEFAULT NULL,
  `SE` decimal(15, 2) NULL DEFAULT NULL,
  `RG` decimal(15, 2) NULL DEFAULT NULL,
  `VG` decimal(15, 2) NULL DEFAULT NULL,
  `KY` decimal(15, 2) NULL DEFAULT NULL,
  `LE` decimal(15, 2) NULL DEFAULT NULL,
  `AI` decimal(15, 2) NULL DEFAULT NULL,
  `FH` decimal(15, 2) NULL DEFAULT NULL,
  `HT` decimal(15, 2) NULL DEFAULT NULL,
  `JJ` decimal(15, 2) NULL DEFAULT NULL,
  `CL` decimal(15, 2) NULL DEFAULT NULL,
  `HL` decimal(15, 2) NULL DEFAULT NULL,
  `AK` decimal(15, 2) NULL DEFAULT NULL,
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
  `deposit` decimal(15, 2) NULL,
  `deposit_count` int(11) NOT NULL DEFAULT 0,
  `withdrawal` decimal(15, 2) NULL,
  `withdrawal_count` int(11) NOT NULL DEFAULT 0,
  `level` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
  `remark` text CHARACTER SET utf8 COLLATE utf8_bin NULL,
  `activebet` decimal(16, 2) NULL,
  `payout` decimal(15, 2) NULL,
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
