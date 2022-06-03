<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class RefreshTest extends TestCase {

  /**
   * @covers ::Refresh
   */
  public function testCrash(): void {
    $m = new UpdatesLog();
    $m->Refresh();
    $this->assertTrue(TRUE);
  }

}
