<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class isDiffModeTest extends TestCase {

  /**
   * @covers ::getDiffMode
   */
  public function testIsDiffMode(): void {
    $m = new UpdatesLog();
    $status = $m->isDiffMode();
    $this->assertTrue($status);
  }

}
