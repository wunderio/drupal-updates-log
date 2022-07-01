<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class LogPlainTest extends TestCase {

  /**
   * @covers ::LogPlain
   */
  public function testLogPlain(): void {

    $statuses = ['drupal' => 'new'];

    $m = new UpdatesLog();
    $database = \Drupal::database();
    $database->truncate('watchdog')->execute();
    $m->LogPlain($statuses);
    $query = $database->query("select * from {watchdog}");
    $result = $query->fetchAll();
    $log = reset($result);

    $this->assertEquals('updates_log', $log->type);
    $this->assertEquals('("project":"@project","status":"@status")', $log->message);
    $this->assertEquals('a:2:{s:8:"@project";s:6:"drupal";s:7:"@status";s:3:"new";}', $log->variables);
    $this->assertEquals(6, $log->severity);
    $this->assertGreaterThan(time() - 5, $log->timestamp);
  }

}
