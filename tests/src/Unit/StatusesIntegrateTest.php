<?php

namespace Drupal\Tests\updates_log\Unit;


/**
 *
 * @group updates_log
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 */
class StatusesIntegrateTest extends UpdatesLogTestBase
{
  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateSame(): void
  {
    $int = $this->updates_log->statusesIntegrate(
      ['x' => ['status' => 'x', 'version' => 'x']],
      ['x' =>  'x']
    );
    $this->assertEquals(['x' => 'x'], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateChange(): void
  {
    $int = $this->updates_log->statusesIntegrate(
      ['x' => ['status' => 'y', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertEquals(['x' => 'y'], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateNew(): void
  {
    $int = $this->updates_log->statusesIntegrate(['x' => ['status' => 'y', 'version' => 'x']], []);
    $this->assertEquals(['x' => 'y'], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateOld(): void
  {
    $int = $this->updates_log->statusesIntegrate([], ['x' => ['status' => 'y', 'version' => 'x']]);
    $this->assertEquals([], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateNewQ(): void
  {
    $int = $this->updates_log->statusesIntegrate(['x' => ['status' => '???', 'version' => 'x']], []);
    $this->assertEquals(['x' => '???'], $int);
  }

  /**
   * @covers ::statusesIntegrate
   */
  public function testStatusesIntegrateChangeQ(): void
  {
    $int = $this->updates_log->statusesIntegrate(
      ['x' => ['status' => '???', 'version' => 'x']],
      ['x' => 'x']
    );
    $this->assertEquals(['x' => 'x'], $int);
  }

}
