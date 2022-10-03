<?php

use \Drupal\updates_log\UpdatesLog;
use \PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Drupal\updats_log\UpdatesLog
 */
class StatusesLoadSaveTest extends TestCase {

  /**
   * @covers ::StatusesLoad
   * @covers ::StatusesSave
   */
  public function testStatusesLoadSave(): void {

    $m = new UpdatesLog();

    \Drupal::state()->delete('updates_log.statuses');

    $statuses = $m->statusesLoad();
    $this->assertEquals([], $statuses);

    $statuses = ['x' => 'y'];
    $m->statusesSave($statuses);
    $statuses2 = $m->statusesLoad();
    $this->assertEquals($statuses, $statuses2);

    \Drupal::state()->delete('updates_log.statuses');
  }

}
