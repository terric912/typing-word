CREATE TABLE `account` (
  `uid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auth` tinyint(1) NOT NULL DEFAULT '0',
  `class` smallint NOT NULL DEFAULT '0',
  `num` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `k` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `v` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rank` (
  `uid` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LV` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` int NOT NULL DEFAULT '0',
  `times` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
