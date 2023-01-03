<?php

namespace Drupal\Tests\updates_log\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * The StatusesGet test.
 *
 * @group updates_log
 */
class StatusesGetTest extends KernelTestBase {

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
    /** @var \Drupal\updates_log\UpdatesLog $service */
    $this->updatesLogService = \Drupal::service('updates_log.updates_logger');

  }

  /**
   * @covers ::statusesGet
   */
  public function testStructure(): void {
    $statuses = $this->updatesLogService->statusesGet();
    $this->assertArrayHasKey('drupal', $statuses);
    $this->assertIsArray($statuses['drupal']);
  }

}
