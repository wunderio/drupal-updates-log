<?php

namespace Drupal\Tests\updates_log\Unit;


/**
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 * @group updates_log
 */
class ShouldUpdateTest extends UpdatesLogTestBase {

  /**
   * @covers ::ShouldUpdate
   */
  public function testFirstTime(): void {
    $status = $this->updates_log->shouldUpdate(time(), NULL);
    $this->assertTrue($status);
  }

  /**
   * @covers ::ShouldUpdate
   */
  public function testImmediately(): void {

    $status = $this->updates_log->shouldUpdate(time(), time() + 1);
    $this->assertFalse($status);
  }

  /**
   * @covers ::ShouldUpdate
   */
  public function testLater(): void {
    $status = $this->updates_log->shouldUpdate(time(), time() - 24 * 60 * 60);
    $this->assertTrue($status);
  }

  /**
   * @covers ::ShouldUpdate
   */
  public function testTestEnv(): void {
    putenv('UPDATES_LOG_TEST=1');
    $status = $this->updates_log->shouldUpdate(time(), time() + 1);
    $this->assertTrue($status);
  }

}
