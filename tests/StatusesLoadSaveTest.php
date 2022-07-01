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

    $statuses = $m->StatusesLoad();
    $this->assertEquals([], $statuses);

    $statuses = ['x' => 'y'];
    $m->StatusesSave($statuses);
    $statuses2 = $m->StatusesLoad();
    $this->assertEquals($statuses, $statuses2);

    \Drupal::state()->delete('updates_log.statuses');
  }

}
