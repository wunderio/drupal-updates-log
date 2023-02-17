<?php

namespace tests\src\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * Tests fetching the version of updates_log.
 *
 * @group updates_log
 */
class GetVersionTest extends KernelTestBase {

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
  }

  /**
   * @covers ::getVersion
   */
  public function testGetVersion(): void {
    $version = $this->updatesLogService->getVersion();
    $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $version);
  }

}
