<?php

function newHeaderTable($code)
{
    return "CREATE TABLE `op_".$code."_headers` (
	`id` INT(10) UNSIGNED NOT NULL,
	`ad_user_id` INT(10) UNSIGNED NOT NULL,
	`ad_retailer_product_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ad_retailer_city_agency_id` INT(10) UNSIGNED NOT NULL,
	`type` ENUM('Q','I') NOT NULL COLLATE 'utf8_unicode_ci',
	`policy_number` VARCHAR(140) NOT NULL COLLATE 'utf8_unicode_ci',
	`issue_number` BIGINT(20) UNSIGNED NOT NULL,
	`quote_number` BIGINT(20) UNSIGNED NOT NULL,
	`prefix` TEXT(65535) NOT NULL COLLATE 'utf8_unicode_ci',
	`pre_printed` TINYINT(1) NOT NULL DEFAULT '0',
	`pre_printed_number` VARCHAR(140) NOT NULL COLLATE 'utf8_unicode_ci',
	`ad_plan_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`bill_name` VARCHAR(140) NOT NULL COLLATE 'utf8_unicode_ci',
	`bill_dni` VARCHAR(30) NOT NULL COLLATE 'utf8_unicode_ci',
	`premium` DOUBLE(20,2) NOT NULL,
	`annual_premium` DOUBLE(20,2) NOT NULL,
	`total_premium` DOUBLE(20,2) NOT NULL,
	`currency` ENUM('BS','USD') NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`payment_method` ENUM('CO','DA','PT','AN','elp') NOT NULL COLLATE 'utf8_unicode_ci',
	`period` ENUM('Y','S','M') NOT NULL COLLATE 'utf8_unicode_ci',
	`term` INT(10) UNSIGNED NOT NULL,
	`type_term` ENUM('Y','M','W','D') NOT NULL COLLATE 'utf8_unicode_ci',
	`issued` TINYINT(1) NOT NULL DEFAULT '0',
	`date_issue` TIMESTAMP NULL DEFAULT NULL,
	`canceled` TINYINT(1) NOT NULL DEFAULT '0',
	`pledged` TINYINT(1) NOT NULL DEFAULT '0',
	`pledged_number` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`case_number` VARCHAR(140) NOT NULL COLLATE 'utf8_unicode_ci',
	`amount_pledged` DOUBLE(20,2) NOT NULL,
	`file` TEXT(65535) NOT NULL COLLATE 'utf8_unicode_ci',
	`copy` INT(10) UNSIGNED NOT NULL,
	`read` TINYINT(1) NOT NULL DEFAULT '0',
	`ad_certificate_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`warranty` TINYINT(1) NOT NULL DEFAULT '0',
	`validity_start` TIMESTAMP NULL DEFAULT NULL,
	`validity_end` TIMESTAMP NULL DEFAULT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `op_".$code."_headers_ad_user_id_foreign` (`ad_user_id`) USING BTREE,
	INDEX `op_".$code."_headers_ad_plan_id_foreign` (`ad_plan_id`) USING BTREE,
	INDEX `op_".$code."_headers_ad_retailer_product_id_foreign` (`ad_retailer_product_id`) USING BTREE,
	INDEX `op_".$code."_headers_ad_certificate_id_foreign` (`ad_certificate_id`) USING BTREE,
	CONSTRAINT `op_".$code."_headers_ad_certificate_id_foreign` FOREIGN KEY (`ad_certificate_id`) REFERENCES `ad_certificates` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `op_".$code."_headers_ad_plan_id_foreign` FOREIGN KEY (`ad_plan_id`) REFERENCES `ad_plans` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `op_".$code."_headers_ad_retailer_product_id_foreign` FOREIGN KEY (`ad_retailer_product_id`) REFERENCES `ad_retailer_products` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `op_".$code."_headers_ad_user_id_foreign` FOREIGN KEY (`ad_user_id`) REFERENCES `ad_users` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);";
}

function newDetailTable($code)
{
    return "CREATE TABLE `op_".$code."_details` (
	`id` INT(10) UNSIGNED NOT NULL,
	`op_".$code."_header_id` INT(10) UNSIGNED NOT NULL,
	`op_client_id` INT(10) UNSIGNED NOT NULL,
	`insured_value` DOUBLE(20,2) NOT NULL,
	`currency` ENUM('BS','USD') NOT NULL COLLATE 'utf8_unicode_ci',
	`client_code` VARCHAR(30) NOT NULL COLLATE 'utf8_unicode_ci',
	`taker_name` VARCHAR(140) NOT NULL COLLATE 'utf8_unicode_ci',
	`taker_dni` VARCHAR(30) NOT NULL COLLATE 'utf8_unicode_ci',
	`holder` TINYINT(1) NOT NULL DEFAULT '0',
	`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `op_".$code."_details_op_".$code."_header_id_foreign` (`op_".$code."_header_id`) USING BTREE,
	INDEX `op_".$code."_details_op_client_id_foreign` (`op_client_id`) USING BTREE,
	CONSTRAINT `op_".$code."_details_op_client_id_foreign` FOREIGN KEY (`op_client_id`) REFERENCES `op_clients` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `op_".$code."_details_op_".$code."_header_id_foreign` FOREIGN KEY (`op_".$code."_header_id`) REFERENCES `op_".$code."_headers` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);";
}

function newResponseTable($code)
{
    return "CREATE TABLE `op_".$code."_responses` (
	`id` INT(10) UNSIGNED NOT NULL,
	`op_".$code."_detail_id` INT(10) UNSIGNED NOT NULL,
	`response` MEDIUMTEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`observation` LONGTEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `op_".$code."_responses_op_".$code."_detail_id_foreign` (`op_".$code."_detail_id`) USING BTREE,
	CONSTRAINT `op_".$code."_responses_op_".$code."_detail_id_foreign` FOREIGN KEY (`op_".$code."_detail_id`) REFERENCES `op_".$code."_details` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);";
}

function newCollectionTable($code)
{
    return "CREATE TABLE `op_".$code."_collections` (
	`id` INT(10) UNSIGNED NOT NULL,
	`op_".$code."_header_id` INT(10) UNSIGNED NOT NULL,
	`fee_number` BIGINT(20) UNSIGNED NOT NULL,
	`fee_date` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`deadline` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`fee_amount` DOUBLE(20,2) NOT NULL,
	`charged` TINYINT(1) NOT NULL DEFAULT '0',
	`transaction_number` INT(10) UNSIGNED NOT NULL,
	`transaction_date` TIMESTAMP NULL DEFAULT NULL,
	`transaction_amount` DOUBLE(20,2) NOT NULL,
	`reason` INT(11) NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `op_".$code."_collections_op_".$code."_header_id_foreign` (`op_".$code."_header_id`) USING BTREE,
	CONSTRAINT `op_".$code."_collections_op_".$code."_header_id_foreign` FOREIGN KEY (`op_".$code."_header_id`) REFERENCES `op_".$code."_headers` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);";
}

function newCancellationTable($code)
{
    return "CREATE TABLE `op_".$code."_cancellations` (
	`id` INT(10) UNSIGNED NOT NULL,
	`op_".$code."_header_id` INT(10) UNSIGNED NOT NULL,
	
	`ad_user_id` INT(10) UNSIGNED NOT NULL,
	`reason` MEDIUMTEXT NOT NULL COLLATE 'utf8_unicode_ci',
	`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `op_".$code."_cancellations_op_".$code."_header_id_foreign` (`op_".$code."_header_id`) USING BTREE,
	INDEX `op_".$code."_cancellations_ad_user_id_foreign` (`ad_user_id`) USING BTREE,
	
	
	CONSTRAINT `op_".$code."_cancellations_ad_user_id_foreign` FOREIGN KEY (`ad_user_id`) REFERENCES `ad_users` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `op_".$code."_cancellations_op_".$code."_header_id_foreign` FOREIGN KEY (`op_".$code."_header_id`) REFERENCES `op_".$code."_headers` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);
";
}

function newBeneficiaryTable($code)
{
    return "CREATE TABLE `op_".$code."_beneficiaries` (
	`id` INT(10) UNSIGNED NOT NULL,
	`op_".$code."_detail_id` INT(10) UNSIGNED NOT NULL,
	`coverage` ENUM('AP','VI','SP','CO','CV') NOT NULL COLLATE 'utf8_unicode_ci',
	`first_name` VARCHAR(60) NOT NULL COLLATE 'utf8_unicode_ci',
	`last_name` VARCHAR(60) NOT NULL COLLATE 'utf8_unicode_ci',
	`mother_last_name` VARCHAR(60) NOT NULL COLLATE 'utf8_unicode_ci',
	`dni` VARCHAR(15) NOT NULL COLLATE 'utf8_unicode_ci',
	`extension` VARCHAR(4) NOT NULL COLLATE 'utf8_unicode_ci',
	`age` INT(10) UNSIGNED NOT NULL,
	`percentage` INT(10) UNSIGNED NOT NULL,
	`relationship` VARCHAR(140) NOT NULL COLLATE 'utf8_unicode_ci',
	`participation` DOUBLE(5,2) NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `op_".$code."_beneficiaries_op_".$code."_detail_id_foreign` (`op_".$code."_detail_id`) USING BTREE,
	CONSTRAINT `op_".$code."_beneficiaries_op_".$code."_detail_id_foreign` FOREIGN KEY (`op_".$code."_detail_id`) REFERENCES `op_".$code."_details` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);
";
}