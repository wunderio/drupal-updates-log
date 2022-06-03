<?php

/*
* We have a problem with bootstrapping.
*
* If Drupal is boostrapped by en external script (phpunit)
* then execution of update_refresh()
* messes up the database.
* The projects' information cannot be updated anymore,
* neither manually or programmatically.
*
* Possible alternatives:
* * https://hibern8.wordpress.com/2018/09/25/drupal-8-bootstrap-from-external-script/
* * Rip off from from Drush
*/

use Drupal\Core\DrupalKernel;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
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

  $container = $kernel->getContainer();

  // It seems boot does not set current request.
  // Need to set manually.
  // Otherwise update_get_available() fails.
  $container->get('request_stack')->push($request);

  // Drupal boot() does not load modules.
  // Need to load them manually.
  \Drupal::moduleHandler()->addModule('updates_log', 'modules/custom/updates_log');
  \Drupal::moduleHandler()->load('updates_log');
  \Drupal::moduleHandler()->addModule('update', 'core/modules/update');
  \Drupal::moduleHandler()->load('update');

  putenv('TESTING=1');

}
