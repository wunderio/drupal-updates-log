<?php

namespace tests\src\Unit;

use Drupal\Tests\updates_log\Unit\UpdatesLogTestBase;

/**
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 * @group updates_log
 */
class GenerateStatisticsTest extends UpdatesLogTestBase {

  /**
   * @covers ::generateStatistics
   */
  public function testGenerateStatistics(): void {
    $statistics = $this->updatesLog->generateStatistics(
      [
        'x' => ['status' => 'NOT_CURRENT', 'version' => 'x'],
        'y' => ['status' => 'NOT_SUPPORTED', 'version' => 'x'],
        'z' => ['status' => 'NOT_SECURE', 'version' => 'x'],
        'a' => ['status' => 'CURRENT', 'version' => 'x'],
        'b' => ['status' => 'CURRENT', 'version' => 'x'],
        'c' => ['status' => '???', 'version' => 'x'],

      ]
    );
    $this->assertEquals(2, $statistics['summary']['CURRENT']);
    $this->assertEquals(1, $statistics['summary']['NOT_CURRENT']);
    $this->assertEquals(1, $statistics['summary']['UNKNOWN']);
    $this->assertCount(4, $statistics['details']);

  }

}
