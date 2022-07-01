<?php

/**
 * @file
 * This is a settings.php for testing _ping.php functionality.
 */

declare(strict_types = 1);

if (empty($databases)) {
  $databases = [];
}
/** @var array<string, array<string, string>> $databases */

if (empty($settings)) {
  $settings = [];
}
/**@var array $settings */

if (empty($conf)) {
  $conf = [];
}
/**@var array $conf */

if (empty($config)) {
  $config = [];
}
/**@var array $conf */

/** @var string */
$info = getenv('LANDO_INFO');
/** @var object */
$info = json_decode($info);
/** @var object{creds: object, internal_connection: object} */
$db = $info->mariadb;
$databases['default']['default'] = [
  'collation' => 'utf8mb4_general_ci',
  'database' => $db->creds->database,
  'driver' => 'mysql',
  'host' => $db->internal_connection->host,
  'password' => $db->creds->password,
  'port' => $db->internal_connection->port,
  'prefix' => '',
  'username' => $db->creds->user,
];

// Make status page happy.
$settings['trusted_host_patterns'] = ['^'];

// Update needs to be able to save temporary files.
$conf['file_temporary_path'] = '/tmp';

$config['updates_log']['diff'] = TRUE;

// @codingStandardsIgnoreStart
// Ignore settings added by Drupal install below this line.
