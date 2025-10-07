CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archived_at` datetime DEFAULT NULL,
  `reason_archive` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `last_active` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `creator_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `max_quantity` int NOT NULL,
  `price` float NOT NULL,
  `visibility` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `creator_id` (`creator_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `auth_groups_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_group` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_groups_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `auth_identities_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret2` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_identities_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `auth_logins_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `auth_logins_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `buyers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `ticket_id` int NOT NULL,
  `bought_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `archived_at` datetime DEFAULT NULL,
  `reason_archive` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `archived` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  KEY `buyers_ibfk_1` (`user_id`),
  CONSTRAINT `buyers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `buyers_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2726 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `tickets_with_buyers` AS select `t`.`id` AS `id`,`t`.`creator_id` AS `creator_id`,`t`.`created_at` AS `created_at`,`t`.`deleted_at` AS `deleted_at`,`t`.`deleted` AS `deleted`,`t`.`title` AS `title`,`t`.`starts_at` AS `starts_at`,`t`.`ends_at` AS `ends_at`,`t`.`max_quantity` AS `max_quantity`,`t`.`price` AS `price`,`t`.`visibility` AS `visibility`,`t`.`image` AS `image`,coalesce(`b`.`ticket_count`,0) AS `buyer_count` from (`tickets` `t` left join (select `buyers`.`ticket_id` AS `ticket_id`,count(0) AS `ticket_count` from `buyers` where (`buyers`.`archived` = 0) group by `buyers`.`ticket_id`) `b` on((`t`.`id` = `b`.`ticket_id`)));
