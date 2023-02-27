<?php

namespace tests\src\Kernel;

use Drupal\Core\Database\Connection;
use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 * @group updates_log
 */
class LogUnknownTest extends KernelTestBase {

  /**
   * The UpdatesLog service.
   *
   * @var \Drupal\updates_log\UpdatesLog
   */
  private UpdatesLog $updatesLogService;

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private Connection $db;

  /**
   * The modules to load to run the test.
   *
   * @var array
   */
  protected static $modules = [
    'update',
    'updates_log',
    'dblog',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['updates_log']);
    $this->installSchema('dblog', ['watchdog']);
    $this->updatesLogService = \Drupal::service('updates_log.updates_logger');
    $this->db = \Drupal::database();
  }

  /**
   * @covers ::logDiff
   */
  public function testLogUnknown(): void {

    $this->db->truncate('watchdog')->execute();
    $this->updatesLogService->logUnknown();
    $query = $this->db->query("select * from {watchdog}");
    $result = $query->fetchAll();
    $log = reset($result);

    $this->assertEquals('updates_log', $log->type);
    $this->assertEquals(4, $log->severity);
    $this->assertGreaterThan(time() - 5, $log->timestamp);
  }

}
