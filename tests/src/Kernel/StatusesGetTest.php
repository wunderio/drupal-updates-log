<?php

namespace Drupal\Tests\updates_log\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;


/**
 *
 * @group updates_log
 */
class StatusesGetTest extends KernelTestBase {

  private UpdatesLog $service;

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
    $this->service = \Drupal::service('updates_log.updates_logger');

  }

  /**
   * @covers ::statusesGet
   */
  public function testStructure(): void {
    $statuses = $this->service->statusesGet();
    $this->assertArrayHasKey('drupal', $statuses);
    $this->assertIsArray($statuses['drupal']);
  }

}
