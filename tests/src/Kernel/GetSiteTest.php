<?php

namespace Drupal\Tests\updates_log\Kernel;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * Tests that fetching the version of updates_log.
 *
 * @group updates_log
 */
class GetSiteTest extends KernelTestBase {

  /**
   * The UpdatesLog service.
   *
   * @var \Drupal\updates_log\UpdatesLog
   */
  private UpdatesLog $updatesLogService;

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'update',
    'updates_log',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();
    $this->installConfig(['updates_log']);
    $this->updatesLogService = \Drupal::service('updates_log.updates_logger');

    putenv("PROJECT_NAME");
    putenv("HOSTNAME");
    putenv("DRUSH_OPTIONS_URI");
    new Settings([]);
  }

  /**
   * @covers ::getSite
   */
  public function testGetSiteProjectName(): void {
    $value = 'xxx';
    putenv("PROJECT_NAME=$value");
    $site = $this->updatesLogService->getSite();
    $this->assertEquals($value, $site);
  }

  /**
   * @covers ::getSite
   */
  public function testGetSiteSettings(): void {
    $value = 'xxx';
    new Settings(['updates_log_site' => $value]);
    $site = $this->updatesLogService->getSite();
    $this->assertEquals($value, $site);
  }

  /**
   * @covers ::getSite
   */
  public function testGetSiteHostname(): void {
    $value = 'xxx';
    putenv("HOSTNAME=$value");
    $site = $this->updatesLogService->getSite();
    $this->assertEquals($value, $site);
  }

  /**
   * @covers ::getSite
   */
  public function testGetSiteDrushOptionsUri(): void {
    $value = 'xxx';
    putenv("DRUSH_OPTIONS_URI=https://$value/yyy");
    $site = $this->updatesLogService->getSite();
    $this->assertEquals($value, $site);
  }

  /**
   * @covers ::getSite
   */
  public function testGetSiteUnknown(): void {
    $value = 'unknown';
    putenv("PROJECT_NAME");
    putenv("HOSTNAME=$value");
    putenv("DRUSH_OPTIONS_URI");
    $site = $this->updatesLogService->getSite();
    $this->assertEquals($value, $site);
  }

}
