<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class StatusesGetTest extends TestCase {

  /**
   * @covers ::StatusesGet
   */
  public function testStructure(): void {
    $m = new UpdatesLog();
    $statuses = $m->StatusesGet();
    $expected = [
      'drupal' => 'CURRENT',
    ];
    $this->assertEquals($expected, $statuses);
  }

}
