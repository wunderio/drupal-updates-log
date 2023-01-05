<?php

namespace tests\src\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * Tests that UpdatesLog does not crash during full run.
 *
 * @group updates_log
 */
class RunTest extends KernelTestBase {

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
   * @covers ::Run
   */
  public function testCrash(): void {
    try {
      $this->updatesLogService->run();
      $this->assertTrue(TRUE);
    }
    catch (\Exception $exception) {
      $this->fail("Run failed with: " . $exception->getMessage());
    }
  }

}
