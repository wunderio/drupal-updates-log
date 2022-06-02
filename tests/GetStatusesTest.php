<?php

use \PHPUnit\Framework\TestCase;

class GetStatusesTest extends TestCase {

  /**
   * @covers updates_log_cron
   */
  public function testStructure(): void {
    $statuses = updates_log_storage_get_statuses();
    $expected = [
      'drupal' => 'CURRENT',
    ];
    $this->assertEquals($expected, $statuses);
  }

}
