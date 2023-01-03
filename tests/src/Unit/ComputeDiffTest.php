<?php

namespace Drupal\Tests\updates_log\Unit;

/**
 * The Compute Diff test.
 *
 * @group updates_log
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 */
class ComputeDiffTest extends UpdatesLogTestBase {

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffSame(): void {
    $diff = $this->updatesLog->computeDiff(
      ['x' => ['status' => 'x', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertSame([], $diff);

  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffChange(): void {
    $diff = $this->updatesLog->computeDiff(
      ['x' => ['status' => 'x', 'version' => 'x']],
      ['x' => 'y']
    );
    $this->assertSame(['x' => ['old' => 'y', 'new' => 'x']], $diff);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffNew(): void {
    $diff = $this->updatesLog->computeDiff([
      'x' => [
        'status' => 'x',
        'version' => 'x',
      ],
    ], []);
    $this->assertSame(['x' => ['old' => '', 'new' => 'x']], $diff);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffOld(): void {
    $diff = $this->updatesLog->computeDiff([], ['x' => 'x']);
    $this->assertSame([], $diff);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffNewQ(): void {
    $diff = $this->updatesLog->computeDiff([
      'x' => [
        'status' => '???',
        'version' => 'x',
      ],
    ], []);
    $this->assertSame(['x' => ['old' => '', 'new' => '???']], $diff);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffChangeQ(): void {
    $diff = $this->updatesLog->computeDiff(
      ['x' => ['status' => '???', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertSame([], $diff);
  }

}
