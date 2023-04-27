<?php

namespace Drupal\Tests\updates_log\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * Test refresh handling.
 *
 * @group updates_log
 */
class RefreshTest extends KernelTestBase {

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
    $this->installConfig(['update']);
    $this->updatesLogService = \Drupal::service('updates_log.updates_logger');
  }

  /**
   * @covers ::refresh
   */
  public function testRefreshOld(): void {

    \Drupal::state()->set('update.last_check', 0);

    $this->updatesLogService->refresh();
    $statuses = $this->updatesLogService->statusesGet();

    $this->assertGreaterThan(0, count($statuses));
  }

  /**
   * @covers ::refresh
   */
  public function testRefreshNew(): void {

    \Drupal::state()->set('update.last_check', time());

    $this->updatesLogService->refresh();
    $statuses = $this->updatesLogService->statusesGet();

    $this->assertGreaterThan(0, count($statuses));
  }

}
