<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class statusesIntegrateTest extends TestCase {

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateSame(): void {
    $m = new UpdatesLog();
    $int = $m->statusesIntegrate(['x' => 'x'], ['x' => 'x']);
    $this->assertEquals(['x' => 'x'], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateChange(): void {
    $m = new UpdatesLog();
    $int = $m->statusesIntegrate(['x' => 'y'], ['x' => 'x']);
    $this->assertEquals(['x' => 'y'], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateNew(): void {
    $m = new UpdatesLog();
    $int = $m->statusesIntegrate(['x' => 'x'], []);
    $this->assertEquals(['x' => 'x'], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateOld(): void {
    $m = new UpdatesLog();
    $int = $m->statusesIntegrate([], ['x' => 'x']);
    $this->assertEquals([], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateNewQ(): void {
    $m = new UpdatesLog();
    $int = $m->statusesIntegrate(['x' => '???'], []);
    $this->assertEquals(['x' => '???'], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateChangeQ(): void {
    $m = new UpdatesLog();
    $int = $m->statusesIntegrate(['x' => '???'], ['x' => 'x']);
    $this->assertEquals(['x' => 'x'], $int);
  }

}
