<?php

use \PHPUnit\Framework\TestCase;

class HookTest extends TestCase {

  /**
   * @covers updates_log_cron
   */
  public function testHook(): void {
    $status = function_exists('updates_log_cron');
    $this->assertTrue($status);
  }

}
