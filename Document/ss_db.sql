/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50709
Source Host           : localhost:3306
Source Database       : ss_db

Target Server Type    : MYSQL
Target Server Version : 50709
File Encoding         : 65001

Date: 2017-08-15 09:57:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for academy
-- ----------------------------
DROP TABLE IF EXISTS `academy`;
CREATE TABLE `academy` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='全部学院表';

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(32) NOT NULL DEFAULT '',
  `password` char(64) NOT NULL DEFAULT '',
  `first_login` tinyint(4) NOT NULL DEFAULT '1',
  `name` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='管理员表';

-- ----------------------------
-- Table structure for admin_academy
-- ----------------------------
DROP TABLE IF EXISTS `admin_academy`;
CREATE TABLE `admin_academy` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(32) NOT NULL DEFAULT '',
  `password` char(64) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(30) NOT NULL DEFAULT '',
  `first_login` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1-未登录过，2-已登录过',
  `academy_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='学院管理员表';

-- ----------------------------
-- Table structure for class
-- ----------------------------
DROP TABLE IF EXISTS `class`;
CREATE TABLE `class` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1-A 2-B 3-村官班',
  `student_sum` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `profession` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1241 DEFAULT CHARSET=utf8 COMMENT='班级表';

-- ----------------------------
-- Table structure for classroom_time
-- ----------------------------
DROP TABLE IF EXISTS `classroom_time`;
CREATE TABLE `classroom_time` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `building` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1-马兰芳教学楼 2-南主楼 3-黄浩川教学楼',
  `number` smallint(6) NOT NULL DEFAULT '0',
  `period` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1-5：周一晚-周五晚， 6-8周六上下晚， 9-11周日上下晚',
  `classroom` varchar(30) NOT NULL DEFAULT '',
  `seat_sum` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14018 DEFAULT CHARSET=utf8 COMMENT='教室可用时间表';

-- ----------------------------
-- Table structure for course
-- ----------------------------
DROP TABLE IF EXISTS `course`;
CREATE TABLE `course` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL DEFAULT '0',
  `code` char(15) NOT NULL DEFAULT '',
  `name` char(30) NOT NULL DEFAULT '',
  `examine_way` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1-考试 2-考查',
  `time_total` tinyint(4) NOT NULL DEFAULT '0',
  `time_theory` tinyint(4) NOT NULL DEFAULT '0',
  `time_practice` tinyint(4) NOT NULL DEFAULT '0',
  `combine_status` varchar(30) NOT NULL DEFAULT '',
  `comment` varchar(200) NOT NULL DEFAULT '',
  `academy_id` smallint(5) NOT NULL DEFAULT '0' COMMENT '上课院系',
  `teacher_id` varchar(20) NOT NULL DEFAULT '',
  `exam_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1-未安排 2-已安排',
  `week_section_ids` varchar(30) NOT NULL DEFAULT '' COMMENT '关联classroom_time表',
  `open_class_academy_id` int(11) NOT NULL DEFAULT '0' COMMENT '开课院系',
  `teacher_require_id` int(8) NOT NULL DEFAULT '0' COMMENT '教师排课要求',
  `semester` tinyint(4) NOT NULL DEFAULT '0' COMMENT '学期1/2',
  `open_class_academy_value` varchar(30) NOT NULL DEFAULT '',
  `have_class_academy_value` varchar(30) NOT NULL DEFAULT '',
  `year` smallint(5) unsigned zerofill NOT NULL DEFAULT '00000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14621 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for exam
-- ----------------------------
DROP TABLE IF EXISTS `exam`;
CREATE TABLE `exam` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `classroom_time_id` int(11) NOT NULL DEFAULT '0',
  `code` char(15) NOT NULL DEFAULT '0',
  `week` tinyint(4) NOT NULL DEFAULT '0' COMMENT '安排考试的周数',
  `period` tinyint(4) NOT NULL DEFAULT '0',
  `class_id` int(11) NOT NULL DEFAULT '0',
  `other_classroom` varchar(20) NOT NULL DEFAULT '',
  `monitor_teacher_name` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31084 DEFAULT CHARSET=utf8 COMMENT='考试时间地点';

-- ----------------------------
-- Table structure for holiday
-- ----------------------------
DROP TABLE IF EXISTS `holiday`;
CREATE TABLE `holiday` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `week` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `weekday` varchar(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='节假日表';

-- ----------------------------
-- Table structure for public_course
-- ----------------------------
DROP TABLE IF EXISTS `public_course`;
CREATE TABLE `public_course` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(30) NOT NULL DEFAULT '',
  `semester` tinyint(255) NOT NULL DEFAULT '0',
  `period` tinyint(255) NOT NULL DEFAULT '0' COMMENT '排课时间段',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='公共课';

-- ----------------------------
-- Table structure for reexam
-- ----------------------------
DROP TABLE IF EXISTS `reexam`;
CREATE TABLE `reexam` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `open_class_academy` tinyint(4) NOT NULL DEFAULT '0',
  `have_class_academy` tinyint(4) NOT NULL DEFAULT '0',
  `class` varchar(10) NOT NULL DEFAULT '',
  `student_id` varchar(12) NOT NULL DEFAULT '',
  `student_name` varchar(6) NOT NULL DEFAULT '',
  `code` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(20) NOT NULL DEFAULT '',
  `semester` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `teacher_account` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=660 DEFAULT CHARSET=utf8 COMMENT='补考表';

-- ----------------------------
-- Table structure for select_book
-- ----------------------------
DROP TABLE IF EXISTS `select_book`;
CREATE TABLE `select_book` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `profession` char(30) NOT NULL DEFAULT '',
  `open_class_academy` varchar(20) NOT NULL DEFAULT '',
  `have_class_academy` varchar(20) NOT NULL DEFAULT '',
  `class_name` char(11) NOT NULL DEFAULT '',
  `code` char(15) NOT NULL DEFAULT '',
  `name` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8 COMMENT='开课一览表';

-- ----------------------------
-- Table structure for teacher
-- ----------------------------
DROP TABLE IF EXISTS `teacher`;
CREATE TABLE `teacher` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(25) NOT NULL DEFAULT '',
  `account` char(15) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '123456',
  `position` varchar(30) NOT NULL DEFAULT '',
  `academy_id` tinyint(4) NOT NULL DEFAULT '0',
  `academy_value` char(20) NOT NULL DEFAULT '',
  `email` varchar(30) NOT NULL DEFAULT '',
  `first_login` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10809 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for teacher_existing_class
-- ----------------------------
DROP TABLE IF EXISTS `teacher_existing_class`;
CREATE TABLE `teacher_existing_class` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `existing_class` varchar(1500) NOT NULL DEFAULT '',
  `teacher_id` smallint(6) NOT NULL DEFAULT '0',
  `semester` tinyint(255) NOT NULL DEFAULT '0',
  `year` smallint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=366 DEFAULT CHARSET=utf8 COMMENT='教师普教上课时间';

-- ----------------------------
-- Table structure for teacher_require
-- ----------------------------
DROP TABLE IF EXISTS `teacher_require`;
CREATE TABLE `teacher_require` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `check_way` char(7) NOT NULL DEFAULT '',
  `time_media` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `time_in_class` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `require` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4941 DEFAULT CHARSET=utf8 COMMENT='教师排课要求表';

-- ----------------------------
-- Table structure for week_section
-- ----------------------------
DROP TABLE IF EXISTS `week_section`;
CREATE TABLE `week_section` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `classroom_time_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联classroom_time',
  `use_weeks` varchar(100) NOT NULL DEFAULT '',
  `section_num` tinyint(4) NOT NULL DEFAULT '0' COMMENT '节数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16729 DEFAULT CHARSET=utf8 COMMENT='周数节数表，跟教室可用时间表关联';

-- ----------------------------
-- Table structure for work_quality_gather
-- ----------------------------
DROP TABLE IF EXISTS `work_quality_gather`;
CREATE TABLE `work_quality_gather` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `academy_value` varchar(30) NOT NULL DEFAULT '',
  `academy_id` int(20) NOT NULL DEFAULT '0',
  `class` varchar(50) NOT NULL DEFAULT '',
  `student_sum` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `paper_undergraduate` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '论文人数（本）',
  `paper_special` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '论文人数（专）',
  `year` char(4) NOT NULL DEFAULT '0',
  `semester` tinyint(10) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `examine_way` varchar(10) NOT NULL DEFAULT '',
  `time_total` tinyint(4) NOT NULL DEFAULT '0',
  `time_theory` tinyint(4) NOT NULL DEFAULT '0',
  `time_practice` tinyint(4) NOT NULL DEFAULT '0',
  `teacher_name` varchar(20) NOT NULL DEFAULT '0',
  `teacher_position` varchar(20) NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `work_quality` varchar(20) NOT NULL DEFAULT '',
  `work_quality_sum` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101505 DEFAULT CHARSET=utf8 COMMENT='工作量汇总表';
SET FOREIGN_KEY_CHECKS=1;
