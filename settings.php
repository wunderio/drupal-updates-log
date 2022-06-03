<?php

/**
 * @file
 * This is a settings.php for testing _ping.php functionality.
 */

declare(strict_types = 1);

if (empty($databases)) {
  $databases = [];
}
/**@var array $databases */

/** @var string */
$info = getenv('LANDO_INFO');
/** @var object */
$info = json_decode($info);
/** @var object{creds: object, internal_connection: object} */
$db = $info->mariadb;
/** @psalm-suppress MixedArrayAssignment, MixedArrayAccess */
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

// @codingStandardsIgnoreLine DrupalPractice.Commenting.CommentEmptyLine.SpacingAfter
// Ignore settings added by Drupal install below this line.
