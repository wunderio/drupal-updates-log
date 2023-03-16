<?php

namespace Drupal\Tests\updates_log\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * Test diff handling.
 *
 * @group updates_log
 */
class RunDiffTest extends KernelTestBase {

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
   * @covers ::runDiff
   */
  public function testNoChanges(): void {

    // Old.
    $statuses_old = ['projX' => 'a'];
    \Drupal::state()->set(
        UpdatesLog::STATUSES_STATE,
        $statuses_old
    );

    // New.
    $statuses_new = ['projX' => ['status' => 'a']];

    $this->updatesLogService->runDiff($statuses_new);

    $statuses_updated = \Drupal::state()->get(
        UpdatesLog::STATUSES_STATE
    );

    $this->assertEquals($statuses_old, $statuses_updated);
  }

  /**
   * @covers ::runDiff
   */
  public function testYesChanges(): void {

    // Old.
    $statuses_old = ['projX' => 'a'];
    \Drupal::state()->set(
        UpdatesLog::STATUSES_STATE,
        $statuses_old
    );

    // New.
    $statuses_new = ['projX' => ['status' => 'b']];

    $this->updatesLogService->runDiff($statuses_new);

    $statuses_updated = \Drupal::state()->get(
        UpdatesLog::STATUSES_STATE
    );

    // Expected.
    $statuses_expected = ['projX' => 'b'];

    $this->assertEquals($statuses_expected, $statuses_updated);
  }

}
