<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class computeDiffTest extends TestCase {

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffSame(): void {
    $m = new UpdatesLog();
    $int = $m->computeDiff(['x' => 'x'], ['x' => 'x']);
    $this->assertEquals([], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffChange(): void {
    $m = new UpdatesLog();
    $int = $m->computeDiff(['x' => 'y'], ['x' => 'x']);
    $this->assertEquals(['x' => ['old' => 'x', 'new' => 'y']], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffNew(): void {
    $m = new UpdatesLog();
    $int = $m->computeDiff(['x' => 'x'], []);
    $this->assertEquals(['x' => ['old' => '', 'new' => 'x']], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffOld(): void {
    $m = new UpdatesLog();
    $int = $m->computeDiff([], ['x' => 'x']);
    $this->assertEquals([], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffNewQ(): void {
    $m = new UpdatesLog();
    $int = $m->computeDiff(['x' => '???'], []);
    $this->assertEquals(['x' => ['old' => '', 'new' => '???']], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffChangeQ(): void {
    $m = new UpdatesLog();
    $int = $m->computeDiff(['x' => '???'], ['x' => 'x']);
    $this->assertEquals([], $int);
  }

}
