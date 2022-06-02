<?php

use \PHPUnit\Framework\TestCase;

class ShouldUpdateTest extends TestCase {

  /**
   * @covers updates_log_business_should_update
   */
  public function testFirstTime(): void {
    $status = updates_log_business_should_update(time(), NULL);
    $this->assertTrue($status);
  }

  /**
   * @covers updates_log_business_should_update
   */
  public function testImmediately(): void {
    $status = updates_log_business_should_update(time(), time() + 1);
    $this->assertFalse($status);
  }

  /**
   * @covers updates_log_business_should_update
   */
  public function testLater(): void {
    $status = updates_log_business_should_update(time(), time() - 24 * 60 * 60);
    $this->assertTrue($status);
  }

}
