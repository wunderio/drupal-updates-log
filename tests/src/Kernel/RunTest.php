<?php

namespace tests\src\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 *
 * @group updates_log
 */
class RunTest extends KernelTestBase {

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
   * @covers ::Run
   */
  public function testCrash(): void {
    try {
      $this->service->run();
      $this->assertTrue(True);
    } catch (\Exception $exception) {
      $this->fail("Run failed with: " . $exception->getMessage());
    }
  }

}
