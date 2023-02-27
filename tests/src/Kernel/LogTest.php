<?php

namespace tests\src\Kernel;

use Drupal\Core\Database\Connection;
use Drupal\KernelTests\KernelTestBase;
use Drupal\updates_log\UpdatesLog;

/**
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 * @group updates_log
 */
class LogTest extends KernelTestBase {

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
  public function testLogDiff(): void {

    $statuses = ['drupal' => ['new' => 'new', 'old' => 'old']];

    $this->db->truncate('watchdog')->execute();
    $this->updatesLogService->logDiff($statuses);
    $query = $this->db->query("select * from {watchdog}");
    $result = $query->fetchAll();
    $log = reset($result);

    $this->assertEquals('updates_log', $log->type);
    $this->assertEquals('updates_log=@placeholder==', $log->message);
    $this->assertEquals('a:1:{s:12:"@placeholder";s:44:"{"project":"drupal","old":"old","new":"new"}";}', $log->variables);
    $this->assertEquals(6, $log->severity);
    $this->assertGreaterThan(time() - 5, $log->timestamp);
  }

  /**
   * @covers ::logStatistics
   */
  public function testLogStatistics(): void {
    $time = time();
    $statistics = [
      "updates_log" => "2.0",
      "last_check_epoch" => $time,
      "last_check_human" => gmdate('Y-m-d\Th:i:sZT', $time),
      "last_check_ago" => 1,
      "summary" => [
        "CURRENT" => 2,
        "NOT_CURRENT" => 1,
        "NOT_SECURE" => 0,
        "NOT_SUPPORTED" => 1,
        "REVOKED" => 0,
        "UNKNOWN" => 1,
      ],
      'details' => [
        'x' => ['status' => 'NOT_CURRENT', 'version_used' => 'x'],
        'y' => ['status' => 'NOT_SUPPORTED', 'version_used' => 'x'],
        'z' => ['status' => 'NOT_SECURE', 'version_used' => 'x'],
        'a' => ['status' => 'CURRENT', 'version_used' => 'x'],
        'b' => ['status' => 'CURRENT', 'version_used' => 'x'],
        'c' => ['status' => '???', 'version_used' => 'x'],
      ],
    ];

    $this->db->truncate('watchdog')->execute();
    $this->updatesLogService->logStatistics($statistics);
    $query = $this->db->query("select * from {watchdog}");
    $result = $query->fetchAll();
    $log = reset($result);

    $this->assertEquals('updates_log', $log->type);
    $this->assertEquals('updates_log_statistics=@placeholder==', $log->message);
    $this->assertEquals('a:1:{s:12:"@placeholder";s:497:"{"updates_log":"2.0","last_check_epoch":' . $time . ',"last_check_human":"' . gmdate('Y-m-d\Th:i:sZT', $time) . '","last_check_ago":1,"summary":{"CURRENT":2,"NOT_CURRENT":1,"NOT_SECURE":0,"NOT_SUPPORTED":1,"REVOKED":0,"UNKNOWN":1},"details":{"x":{"status":"NOT_CURRENT","version_used":"x"},"y":{"status":"NOT_SUPPORTED","version_used":"x"},"z":{"status":"NOT_SECURE","version_used":"x"},"a":{"status":"CURRENT","version_used":"x"},"b":{"status":"CURRENT","version_used":"x"},"c":{"status":"???","version_used":"x"}}}";}', $log->variables);
    $this->assertEquals(6, $log->severity);
    $this->assertGreaterThan(time() - 5, $log->timestamp);
  }

}
