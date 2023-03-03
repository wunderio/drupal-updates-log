<?php

namespace tests\src\Kernel;

use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * Tests detacing the website env (dev, stage, prod, etc).
 *
 * @group updates_log
 */
class GetEnvTest extends KernelTestBase {

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

    putenv("ENVIRONMENT_NAME");
    putenv("WKV_SITE_ENV");

    new Settings([]);
  }

  /**
   * @covers ::getEnv
   */
  public function testGetEnvSettingsUle(): void {
    $value = 'xxx';
    new Settings(['updates_log_env' => $value]);
    $env = $this->updatesLogService->getEnv();
    $this->assertEquals($value, $env);
  }

  /**
   * @covers ::getEnv
   */
  public function testGetEnvSettingsSei(): void {
    $value = 'xxx';
    new Settings(['simple_environment_indicator' => "#aabbcc $value"]);
    $env = $this->updatesLogService->getEnv();
    $this->assertEquals($value, $env);
  }

  /**
   * @covers ::getEnv
   */
  public function testGetEnvEnvironmentName(): void {
    $value = 'xxx';
    putenv("ENVIRONMENT_NAME=$value");
    $env = $this->updatesLogService->getEnv();
    $this->assertEquals($value, $env);
  }

  /**
   * @covers ::getEnv
   */
  public function testGetEnvWkvSiteEnv(): void {
    $value = 'xxx';
    putenv("WKV_SITE_ENV=$value");
    $env = $this->updatesLogService->getEnv();
    $this->assertEquals($value, $env);
  }

  /**
   * @covers ::getEnv
   */
  public function testGetEnvUnknown(): void {
    $value = 'unknown';
    putenv("ENVIRONMENT_NAME");
    putenv("WKV_SITE_ENV");
    $env = $this->updatesLogService->getEnv();
    $this->assertEquals($value, $env);
  }

}
