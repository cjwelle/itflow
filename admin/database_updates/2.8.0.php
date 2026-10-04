<?php

/*
 * JustTechs extension: verified-domain client portal onboarding.
 * Included by admin/database_updates.php - do not access directly.
 */

defined('FROM_DB_UPDATER') || die("Direct file access is not allowed");

mysqli_query($mysqli, "CREATE TABLE IF NOT EXISTS `identity_provider_accounts` (
    `identity_provider_account_id` int(11) NOT NULL AUTO_INCREMENT,
    `identity_provider` varchar(32) NOT NULL,
    `identity_issuer` varchar(255) NOT NULL,
    `identity_subject` varchar(255) NOT NULL,
    `identity_user_id` int(11) NOT NULL,
    `identity_verified_email` varchar(200) DEFAULT NULL,
    `identity_created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `identity_last_login_at` datetime DEFAULT NULL,
    `identity_revoked_at` datetime DEFAULT NULL,
    PRIMARY KEY (`identity_provider_account_id`),
    UNIQUE KEY `identity_issuer_subject` (`identity_issuer`,`identity_subject`),
    KEY `identity_user_id` (`identity_user_id`),
    CONSTRAINT `identity_provider_accounts_user_fk` FOREIGN KEY (`identity_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

mysqli_query($mysqli, "CREATE TABLE IF NOT EXISTS `client_domain_claims` (
    `client_domain_claim_id` int(11) NOT NULL AUTO_INCREMENT,
    `domain_name` varchar(253) NOT NULL,
    `domain_client_id` int(11) NOT NULL,
    `domain_verification_token` varchar(128) NOT NULL,
    `domain_verified_at` datetime DEFAULT NULL,
    `domain_created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `domain_revoked_at` datetime DEFAULT NULL,
    PRIMARY KEY (`client_domain_claim_id`),
    UNIQUE KEY `client_domain_claim_unique` (`domain_name`),
    KEY `domain_client_id` (`domain_client_id`),
    CONSTRAINT `client_domain_claims_client_fk` FOREIGN KEY (`domain_client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
