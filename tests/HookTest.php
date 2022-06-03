<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

class HookTest extends TestCase {

  /**
   * @covers updates_log_cron
   */
  public function testExists(): void {
    $status = function_exists('updates_log_cron');
    $this->assertTrue($status);
  }

  /**
   * @covers updates_log_cron
   */
  public function testRun(): void {

    $m = new UpdatesLog();
    $m->LastSet(NULL);

    // Make sure we wont crash.
    updates_log_cron();
    $this->assertTrue(TRUE);
  }

}
