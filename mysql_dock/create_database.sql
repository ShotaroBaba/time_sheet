-- A table for storing a user's time sheets. This records amounts of employees' time which
-- they spend on doing their job.
-- TOD: All tables needs to be encrypted.
START TRANSACTION;
DROP DATABASE IF EXISTS `time_sheet`;

CREATE DATABASE IF NOT EXISTS `time_sheet` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `time_sheet`;

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` BIGINT PRIMARY KEY auto_increment,
  `first_name` VARCHAR(128) NOT NULL,
  `middle_name` VARCHAR(128),
  `last_name` VARCHAR(128) NOT NULL,
  `address` VARCHAR(256) NOT NULL,
  `phone_number` VARCHAR(48) NOT NULL,
  `employee_type_id` INT DEFAULT 1 NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `state` VARCHAR(16) DEFAULT 'left_work' NOT NULL,
  UNIQUE (`first_name`, `middle_name`, `last_name`, `phone_number`),
  UNIQUE (`email`)
);

CREATE TABLE IF NOT EXISTS `time_sheet` (
  `time_id` BIGINT auto_increment,
  `user_id` BIGINT NOT NULL,
  `employee_type_id` INT,
  `time` datetime NOT NULL,
  `state` VARCHAR(16) NOT NULL,
  PRIMARY KEY (`time_id`)
);

-- Check user access history and put it on the record.
-- random pseudo id is generated using PHP random function
CREATE TABLE IF NOT EXISTS `user_log` (
  `session_id` VARCHAR(64) NOT NULL,
  `user_id` BIGINT NOT NULL,
  `login_time`  DATETIME(6) NOT NULL,
  `url` TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS `occupation` (
  `employee_type_id` INT auto_increment,
  `occupation_type` VARCHAR(64) NOT NULL,
  `issue_time` DATETIME(6) DEFAULT '2000-01-01 00:00:00' NOT NULL,
  `wage` INT NOT NULL,
  PRIMARY KEY (`employee_type_id`),
  UNIQUE(`occupation_type`)
);

-- Insert default occupation.
INSERT INTO `occupation` (`occupation_type`,
            `wage`) VALUES ('OCCUPATION_NOT_SELECTED', 0);

-- Different salt is created for different users.
CREATE TABLE IF NOT EXISTS `user_secret` (
  `password_id` BIGINT auto_increment,
  `user_id` BIGINT NOT NULL,
  `salt` VARCHAR(50) NOT NULL,
  `email_verification_token` VARCHAR(128) NOT NULL,
  `verified` BOOLEAN NOT NULL DEFAULT 0,
  `password` VARCHAR(256) NOT NULL,
  PRIMARY KEY (`password_id`)
);

ALTER TABLE `user_secret` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
ALTER TABLE `user_log` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
ALTER TABLE `time_sheet` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);
ALTER TABLE `time_sheet` ADD FOREIGN KEY (`employee_type_id`) REFERENCES `occupation` (`employee_type_id`);
ALTER TABLE `user` ADD FOREIGN KEY (`employee_type_id`) REFERENCES `occupation` (`employee_type_id`);

-- Change users' database permissions
GRANT SELECT, INSERT, UPDATE ON `time_sheet`.* TO 'time_sheet_client'@'%';
GRANT ALL PRIVILEGES ON `time_sheet`.* TO 'time_sheet_admin'@'%' WITH GRANT OPTION;

COMMIT;