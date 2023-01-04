<?php

namespace Drupal\Tests\updates_log\Unit;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\State;
use Drupal\Tests\UnitTestCase;
use Drupal\update\UpdateManagerInterface;
use Drupal\update\UpdateProcessorInterface;
use Drupal\updates_log\UpdatesLog;
use Prophecy\Argument;
use Drupal\Core\Logger\LoggerChannelInterface;

/**
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 * @group updates_log
 */
class UpdatesLogTestBase extends UnitTestCase {

  /**
   * The UpdatesLog service.
   *
   * @var \Drupal\updates_log\UpdatesLog
   */
  protected UpdatesLog $updatesLog;

  /**
   * Setup for testing UpdatesLog.
   */
  protected function setUp(): void {
    parent::setUp();

    // Create mock classes to construct UpdatesLog.
    $logger = $this->prophesize(LoggerChannelInterface::class);
    $state = $this->prophesize(State::class);
    $logger_factory = $this->prophesize(LoggerChannelFactoryInterface::class);
    $update_manager = $this->prophesize(UpdateManagerInterface::class);
    $update_processor = $this->prophesize(UpdateProcessorInterface::class);

    // When doing \Drupal::logger('updates_log') return the mock logger.
    $logger_factory->get(Argument::exact('updates_log'))
      ->willReturn($logger->reveal());

    // Mock the State->get() function.
    $state->get('update.last_check', 0)->willReturn(time());

    $this->updatesLog = new UpdatesLog($state->reveal(), $logger_factory->reveal(), $update_manager->reveal(), $update_processor->reveal());
  }

}
