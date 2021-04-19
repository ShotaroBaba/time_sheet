-- A table for storing a user's time sheets. This records amounts of employees' time which
-- they spend on doing their job.
-- TOD: All tables needs to be encrypted.

CREATE TABLE `user` (
  `user_id` int PRIMARY KEY,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255),
  `lastname` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
);

CREATE TABLE `time_sheet` (
  `time_id` int PRIMARY KEY,
  `user_id` int NOT NULL,
  `time` datetime NOT NULL,
  `state` varchar(255) NOT NULL
);

CREATE TABLE `user_password` (
  `user_id` int,
  `salt` varchar(50) NOT NULL,
  `password` varchar(256) NOT NULL
);

ALTER TABLE `user` ADD FOREIGN KEY (`user_id`) REFERENCES `user_password` (`user_id`);

ALTER TABLE `time_sheet` ADD FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);