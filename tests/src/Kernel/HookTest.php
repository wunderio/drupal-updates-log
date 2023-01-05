<?php

namespace tests\src\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * The Cron hook test.
 *
 * @group updates_log
 */
class HookTest extends KernelTestBase {

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
   * @covers ::updates_log_cron
   */
  public function testExists(): void {
    $status = function_exists('updates_log_cron');
    $this->assertTrue($status);
  }

  /**
   * @covers ::updates_log_cron
   */
  public function testCrash(): void {

    \Drupal::state()->delete(UpdatesLog::TIME_STATE);

    // Make sure we won't crash.
    updates_log_cron();
    $this->assertTrue(TRUE);
  }

}
