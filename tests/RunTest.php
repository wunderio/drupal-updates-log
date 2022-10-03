<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class RunTest extends TestCase {

  /**
   * @covers ::Run
   */
  public function testCrash(): void {
    $m = new UpdatesLog();
    $m->run();
    $this->assertTrue(TRUE);
  }

}
