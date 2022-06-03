<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class LogTest extends TestCase {

  /**
   * @covers ::Log
   */
  public function testLog(): void {
    $m = new UpdatesLog();
    $statuses = ['module' => 'STATUS'];
    $m->Log($statuses);

    $database = \Drupal::database();
    $query = $database->query("SELECT * FROM {watchdog} order by wid desc limit 1");
    $result = $query->fetchAll();
    $log = reset($result);

    $this->assertEquals('updates_log', $log->type);
    $this->assertEquals('("project":"@project","status":"@status")', $log->message);
    $this->assertEquals('a:2:{s:8:"@project";s:6:"module";s:7:"@status";s:6:"STATUS";}', $log->variables);
    $this->assertEquals(6, $log->severity);
    $this->assertGreaterThan(time() - 5, $log->timestamp);
  }

}
