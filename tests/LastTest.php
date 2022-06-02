<?php

use \PHPUnit\Framework\TestCase;

class LastTest extends TestCase {

  /**
   * @covers updates_log_business_last_get
   * @covers updates_log_business_last_set
   */
  public function testNull(): void {
    updates_log_business_last_set(NULL);
    $timestamp = updates_log_business_last_get();
    $this->assertNull($imestamp);
  }

  /**
   * @covers updates_log_business_last_get
   * @covers updates_log_business_last_set
   */
  public function testNumeric(): void {
    $now = time();
    updates_log_business_last_set($now);
    $timestamp = updates_log_business_last_get();
    $this->assertIsInt($timestamp);
    $this->assertEquals($now, $timestamp);
  }

}
