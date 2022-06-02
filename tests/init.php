<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

if (empty(getenv('TESTING'))) {

  chdir('/app/drupal/web');

  // Need to enable manually module support.
  // Otherwise module_load_include() fails.
  require_once 'core/includes/module.inc';

  $autoloader = require_once 'autoload.php';

  $request = Request::createFromGlobals();
  $kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
  $kernel->boot();

  // It seems boot does not set current request.
  // Need to set manually.
  // Otherwise update_get_available() fails.
  $container = $kernel->getContainer();
  $container->get('request_stack')->push($request);

  // Define DRUPAL_ROOT if it's not yet defined by bootstrap.
  if (!defined('DRUPAL_ROOT')) {
    define('DRUPAL_ROOT', getcwd());
  }

  // Drupal boot() does not load modules.
  // Need to load them manually.
  \Drupal::moduleHandler()->addModule('updates_log', 'modules/custom/updates_log');
  \Drupal::moduleHandler()->load('updates_log');
  \Drupal::moduleHandler()->addModule('update', 'core/modules/update');
  \Drupal::moduleHandler()->load('update');

  putenv('TESTING=1');

}
