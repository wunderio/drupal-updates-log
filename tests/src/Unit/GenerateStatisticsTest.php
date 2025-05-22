<?php

namespace Drupal\Tests\updates_log\Unit;

/**
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 * @group updates_log
 */
class GenerateStatisticsTest extends UpdatesLogTestBase {

  /**
   * @covers ::generateStatistics
   */
  public function testGenerateStatistics(): void {
    $version = "1.2.3";
    $site = 'acme-support-web';
    $env = 'staging';
    $statistics = $this->updatesLog->generateStatistics(
      [
        'drupal' => ['status' => 'CURRENT', 'version_used' => '3.2.1'],
        'x' => ['status' => 'NOT_CURRENT', 'version_used' => 'x'],
        'y' => ['status' => 'NOT_SUPPORTED', 'version_used' => 'x'],
        'z' => ['status' => 'NOT_SECURE', 'version_used' => 'x'],
        'a' => ['status' => 'CURRENT', 'version_used' => 'x'],
        'b' => ['status' => 'CURRENT', 'version_used' => 'x'],
        'c' => ['status' => '???', 'version_used' => 'x'],
      ],
      $version,
      $site,
      $env
    );
    $this->assertEquals(3, $statistics['summary']['CURRENT']);
    $this->assertEquals(1, $statistics['summary']['NOT_CURRENT']);
    $this->assertEquals(1, $statistics['summary']['UNKNOWN']);
    $this->assertCount(5, $statistics['details']);
    $this->assertArrayHasKey('NOT_SECURE', $statistics['details']);
    $this->assertArrayHasKey('NOT_SUPPORTED', $statistics['details']);
    $this->assertEquals($version, $statistics['updates_log']);
    $this->assertEquals($site, $statistics['site']);
    $this->assertEquals($env, $statistics['env']);
    $this->assertEquals('03.02.01', $statistics['drupal']);
  }

  /**
   * @covers ::generateStatistics
   */
  public function testGenerateStatisticsNoDrupalVer(): void {
    $statistics = $this->updatesLog->generateStatistics(
      [],
      '',
      '',
      ''
    );
    $this->assertEquals('???', $statistics['drupal']);
  }

}
