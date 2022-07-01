<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class LogDiffTest extends TestCase {

  /**
   * @covers ::LogDiff
   */
  public function testLogDiff(): void {

    $statuses = ['drupal' => ['new' => 'new', 'old' => 'old']];

    $m = new UpdatesLog();
    $database = \Drupal::database();
    $database->truncate('watchdog')->execute();
    $m->LogDiff($statuses);
    $query = $database->query("select * from {watchdog}");
    $result = $query->fetchAll();
    $log = reset($result);

    $this->assertEquals('updates_log', $log->type);
    $this->assertEquals('("project":"@project","old":"@old","new":"@new")', $log->message);
    $this->assertEquals('a:3:{s:8:"@project";s:6:"drupal";s:4:"@new";s:3:"new";s:4:"@old";s:3:"old";}', $log->variables);
    $this->assertEquals(6, $log->severity);
    $this->assertGreaterThan(time() - 5, $log->timestamp);
  }

}
