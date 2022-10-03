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
    $m->lastSet(NULL);
    $timestamp = $m->lastGet();
    $this->assertNull($timestamp);
  }

  /**
   * @covers ::LastGet
   * @covers ::LastSet
   */
  public function testNumeric(): void {
    $m = new UpdatesLog();
    $now = time();
    $m->lastSet($now);
    $timestamp = $m->lastGet();
    $this->assertIsInt($timestamp);
    $this->assertEquals($now, $timestamp);
  }

}
