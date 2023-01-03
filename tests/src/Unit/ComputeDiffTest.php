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
    $int = $this->updatesLog->computeDiff(
      ['x' => ['status' => 'x', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertSame([], $int);

  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffChange(): void {
    $int = $this->updatesLog->computeDiff(
      ['x' => ['status' => 'x', 'version' => 'x']],
      ['x' => 'y']
    );
    $this->assertSame(['x' => ['old' => 'y', 'new' => 'x']], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffNew(): void {
    $int = $this->updatesLog->computeDiff([
      'x' => [
        'status' => 'x',
        'version' => 'x',
      ],
    ], []);
    $this->assertSame(['x' => ['old' => '', 'new' => 'x']], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffOld(): void {
    $int = $this->updatesLog->computeDiff([], ['x' => 'x']);
    $this->assertSame([], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffNewQ(): void {
    $int = $this->updatesLog->computeDiff([
      'x' => [
        'status' => '???',
        'version' => 'x',
      ],
    ], []);
    $this->assertSame(['x' => ['old' => '', 'new' => '???']], $int);
  }

  /**
   * @covers ::computeDiff
   */
  public function testComputeDiffChangeQ(): void {
    $int = $this->updatesLog->computeDiff(
      ['x' => ['status' => '???', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertSame([], $int);
  }

}
