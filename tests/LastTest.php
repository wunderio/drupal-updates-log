<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class LastTest extends TestCase {

  /**
   * @covers ::LastGet
   * @covers ::LastSet
   */
  public function testNull(): void {
    $m = new UpdatesLog();
    $m->LastSet(NULL);
    $timestamp = $m->LastGet();
    $this->assertNull($timestamp);
  }

  /**
   * @covers ::LastGet
   * @covers ::LastSet
   */
  public function testNumeric(): void {
    $m = new UpdatesLog();
    $now = time();
    $m->LastSet($now);
    $timestamp = $m->LastGet();
    $this->assertIsInt($timestamp);
    $this->assertEquals($now, $timestamp);
  }

}
