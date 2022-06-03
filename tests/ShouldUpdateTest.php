<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class ShouldUpdateTest extends TestCase {

  /**
   * @covers ::ShouldUpdate
   */
  public function testFirstTime(): void {
    $m = new UpdatesLog();
    $status = $m->ShouldUpdate(time(), NULL);
    $this->assertTrue($status);
  }

  /**
   * @covers ::ShouldUpdate
   */
  public function testImmediately(): void {
    $m = new UpdatesLog();
    $status = $m->ShouldUpdate(time(), time() + 1);
    $this->assertFalse($status);
  }

  /**
   * @covers ::ShouldUpdate
   */
  public function testLater(): void {
    $m = new UpdatesLog();
    $status = $m->ShouldUpdate(time(), time() - 24 * 60 * 60);
    $this->assertTrue($status);
  }

}
