<?php
/**
 * SuiteCRM config overrides — do not edit config.php directly.
 * This file is safe to commit and will not be overwritten by upgrades.
 */

// Suppress PHP 8 "undefined array key" warning in SugarController::showException()
$sugar_config['stackTrace'] = false;

// Register Vietnamese language pack
$sugar_config['languages']['vi_VN'] = 'Tiếng Việt';

// Whitelist EC2 IP to prevent false CSRF positives
$sugar_config['http_referer']['list'][] = '18.136.195.116';
$sugar_config['http_referer']['actions'] = array(
    'index', 'ListView', 'DetailView', 'EditView', 'oauth', 'authorize',
    'Authenticate', 'Login', 'SupportPortal', 'HaravanSettings',
);
