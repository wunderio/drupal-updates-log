<?php

namespace Drupal\Tests\updates_log\Unit;

/**
 * The Statuses Integrate test.
 *
 * @group updates_log
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 */
class StatusesIntegrateTest extends UpdatesLogTestBase {

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateSame(): void {
    $statuses = $this->updatesLog->statusesIntegrate(
      ['x' => ['status' => 'x', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertEquals(['x' => 'x'], $statuses);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateChange(): void {
    $statuses = $this->updatesLog->statusesIntegrate(
      ['x' => ['status' => 'y', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertEquals(['x' => 'y'], $statuses);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateNew(): void {
    $statuses = $this->updatesLog->statusesIntegrate([
      'x' => [
        'status' => 'y',
        'version' => 'x',
      ],
    ], []);
    $this->assertEquals(['x' => 'y'], $statuses);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateOld(): void {
    $statuses = $this->updatesLog->statusesIntegrate([], [
      'x' => [
        'status' => 'y',
        'version' => 'x',
      ],
    ]);
    $this->assertEquals([], $statuses);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateNewQ(): void {
    $statuses = $this->updatesLog->statusesIntegrate([
      'x' => [
        'status' => '???',
        'version' => 'x',
      ],
    ], []);
    $this->assertEquals(['x' => '???'], $statuses);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateChangeQ(): void {
    $statuses = $this->updatesLog->statusesIntegrate(
      ['x' => ['status' => '???', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertEquals(['x' => 'x'], $statuses);
  }

}
