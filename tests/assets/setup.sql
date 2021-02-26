DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `contacts_addresses`;
DROP TABLE IF EXISTS `contacts_emails`;
DROP TABLE IF EXISTS `contacts_phones`;
DROP TABLE IF EXISTS `countries`;

-- Create syntax for TABLE 'contacts'
CREATE TABLE `contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `salutation` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `middle_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organisation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_contact_at` date DEFAULT NULL,
  `last_contact_at` date DEFAULT NULL,
  `additional` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'contacts_addresses'
CREATE TABLE `contacts_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contacts_id` int(10) unsigned NOT NULL,
  `default` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `sort` int(10) unsigned NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `country` varchar(35) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `country_id` int(10) unsigned NOT NULL,
  `legal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'contacts_emails'
CREATE TABLE `contacts_emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contacts_id` int(10) unsigned NOT NULL,
  `default` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `sort` int(10) unsigned NOT NULL,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'contacts_phones'
CREATE TABLE `contacts_phones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contacts_id` int(10) unsigned NOT NULL,
  `default` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `sort` int(10) unsigned NOT NULL,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE 'contacts_phones'
CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `contacts` (`id`, `salutation`, `first_name`, `middle_name`, `last_name`, `organisation`, `tags`, `first_contact_at`, `last_contact_at`, `additional`)
VALUES
  (1, 'Ms', 'Fanny', NULL, 'Adams', NULL, NULL, NULL, NULL, NULL),
  (2, 'Mr', 'Joseph', 'D', 'Bloggs', 'SomeCo', NULL, NULL, NULL, NULL),
  (3, 'Mrs', 'Josephine', NULL, 'Bloggs', 'SomeCo', NULL, NULL, NULL, NULL),
  (4, 'Mr', 'Bob', NULL, 'Dobalina', 'OtherCo', NULL, NULL, NULL, NULL);

INSERT INTO `contacts_addresses` (`id`, `contacts_id`, `default`, `sort`, `name`, `address`, `postcode`, `country`, `country_id`, `legal`)
VALUES
  (1, 2, 1, 0, 'Work', 'Desk 43\nSomeCo\nSometon', '50M3 T0WN', 'Someland', 1, NULL),
  (2, 3, 1, 0, 'Work', 'Desk 42\nSomeCo\nSometon', '50M3 T0WN', 'Someland', 2, NULL),
  (3, 3, 0, 0, 'Home', '50 Some Street\nSometon', '50M3 5T3T', 'Someland', 2, NULL),
  (4, 4, 1, 0, 'Home', '100 Other Street\nOtherton', '0T3 RTN', 'Otherplace', 4, NULL);

INSERT INTO `contacts_emails` (`id`, `contacts_id`, `default`, `sort`, `name`, `address`)
VALUES
  (1, 1, 1, 0, 'Personal', 'fanny@adams.com'),
  (2, 2, 1, 0, 'Work', 'mr.j.bloggs@someco.com'),
  (3, 3, 1, 0, 'Work', 'mrs.j.bloggs@someco.com'),
  (4, 4, 1, 0, 'Personal', 'bob.dobalina@gmail.com'),
  (5, 4, 0, 0, 'Work', 'b.dobalina@otherco.com');

INSERT INTO `contacts_phones` (`id`, `contacts_id`, `default`, `sort`, `name`, `number`)
VALUES
  (1, 1, 1, 0, 'Mobile', '456 789 321'),
  (2, 2, 1, 0, 'Work', '123 456 789'),
  (3, 2, 0, 1, 'Mobile', '789 654 321'),
  (4, 3, 1, 0, 'Mobile', '789 321 654');
  
INSERT INTO `countries` (`id`, `code`, `name`)
VALUES
  (1, 'EN', 'England'),
  (2, 'WA', 'Wales'),
  (3, 'SC', 'Scotland'),
  (4, 'IR', 'Ireland');

