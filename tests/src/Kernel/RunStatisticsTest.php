<?php

namespace Drupal\Tests\updates_log\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * Test statistics handling.
 *
 * @group updates_log
 */
class RunStatisticsTest extends KernelTestBase {

  /**
   * The UpdatesLog service.
   *
   * @var \Drupal\updates_log\UpdatesLog
   */
  private UpdatesLog $updatesLogService;

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'update',
    'updates_log',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['updates_log']);
    $this->updatesLogService = \Drupal::service('updates_log.updates_logger');
  }

  /**
   * @covers ::runStatistics
   */
  public function testEnvCondition(): void {
    \Drupal::state()->set(
        UpdatesLog::STATISTICS_TIME_STATE,
        time()
    );
    putenv('UPDATES_LOG_TEST=1');
    $status = $this->updatesLogService->runStatistics([], time());
    $this->assertNotEquals(1, $status);
  }

  /**
   * @covers ::runStatistics
   */
  public function testTimeCondition(): void {
    \Drupal::state()->set(
        UpdatesLog::STATISTICS_TIME_STATE,
        time() - (60 * 60 * 24)
    );
    putenv('UPDATES_LOG_TEST');
    $status = $this->updatesLogService->runStatistics([], time());
    $this->assertNotEquals(1, $status);
  }

  /**
   * @covers ::runStatistics
   */
  public function testBothCondition(): void {
    \Drupal::state()->set(
        UpdatesLog::STATISTICS_TIME_STATE,
        time() - (60 * 60 * 24)
    );
    putenv('UPDATES_LOG_TEST=1');
    $status = $this->updatesLogService->runStatistics([], time());
    $this->assertNotEquals(1, $status);
  }

  /**
   * @covers ::runStatistics
   */
  public function testNoCondition(): void {
    \Drupal::state()->set(
        UpdatesLog::STATISTICS_TIME_STATE,
        time()
    );
    putenv('UPDATES_LOG_TEST');
    $status = $this->updatesLogService->runStatistics([], time());
    $this->assertEquals(1, $status);
  }

  /**
   * @covers ::runStatistics
   */
  public function testUnknown(): void {
    \Drupal::state()->set(
        UpdatesLog::STATISTICS_TIME_STATE,
        time() - (60 * 60 * 24)
    );
    putenv('UPDATES_LOG_TEST=1');
    $statuses = [
      'projX' => [
        'status' => '???',
        'version_used' => '1.2.3',
      ],
    ];
    $status = $this->updatesLogService->runStatistics($statuses, time());
    $this->assertEquals(2, $status);
  }

  /**
   * @covers ::runStatistics
   */
  public function testNotUnknown(): void {
    \Drupal::state()->set(
        UpdatesLog::STATISTICS_TIME_STATE,
        time() - (60 * 60 * 24)
    );
    putenv('UPDATES_LOG_TEST=1');
    $statuses = [
      'projX' => [
        'status' => 'CURRENT',
        'version_used' => '1.2.3',
      ],
    ];
    $status = $this->updatesLogService->runStatistics($statuses, time());
    $this->assertNotEquals(2, $status);
  }

  /**
   * @covers ::runStatistics
   */
  public function testSuccess(): void {
    $time = time();
    \Drupal::state()->set(
        UpdatesLog::STATISTICS_TIME_STATE,
        $time - (60 * 60 * 24)
    );
    putenv('UPDATES_LOG_TEST=1');
    $statuses = [
      'projX' => [
        'status' => 'CURRENT',
        'version_used' => '1.2.3',
      ],
    ];
    $status = $this->updatesLogService->runStatistics($statuses, $time);
    $this->assertEquals(0, $status);

    $time_updated = \Drupal::state()->get(
        UpdatesLog::STATISTICS_TIME_STATE
    );
    $this->assertEquals($time, $time_updated);

  }

}
