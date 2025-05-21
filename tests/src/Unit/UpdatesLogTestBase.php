<?php

namespace Drupal\Tests\updates_log\Unit;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\State\State;
use Drupal\Tests\UnitTestCase;
use Drupal\update\UpdateManagerInterface;
use Drupal\update\UpdateProcessorInterface;
use Drupal\updates_log\UpdatesLog;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * @coversDefaultClass \Drupal\updates_log\UpdatesLog
 * @group updates_log
 */
abstract class UpdatesLogTestBase extends UnitTestCase {

  /**
   * The deprecated prophet workaround.
   *
   * @var \Prophecy\Prophet
   */
  private $prophet;

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

    $this->prophet = new Prophet();

    // Create mock classes to construct UpdatesLog.
    $logger = $this->prophet->prophesize(LoggerChannelInterface::class);
    $state = $this->prophet->prophesize(State::class);
    $logger_factory = $this->prophet->prophesize(LoggerChannelFactoryInterface::class);
    $update_manager = $this->prophet->prophesize(UpdateManagerInterface::class);
    $update_processor = $this->prophet->prophesize(UpdateProcessorInterface::class);
    $module_extension_list = $this->prophet->prophesize(ExtensionList::class);
    $module_handler = $this->prophet->prophesize(ModuleHandler::class);
    $config_factory = $this->prophet->prophesize(ConfigFactoryInterface::class);
    $time_if = $this->prophet->prophesize(TimeInterface::class);

    // When doing \Drupal::logger('updates_log') return the mock logger.
    // @codingStandardsIgnoreStart
    /** @phpstan-ignore-next-line */
    $logger_factory->get(Argument::exact('updates_log'))
      ->willReturn($logger->reveal());
    // @codingStandardsIgnoreEnd

    // Mock the State->get() function.
    // @codingStandardsIgnoreStart
    /** @phpstan-ignore-next-line */
    $state->get('update.last_check', 0)->willReturn(time());
    // @codingStandardsIgnoreEnd

    $this->updatesLog = new UpdatesLog(
      $state->reveal(),
      $logger_factory->reveal(),
      $update_manager->reveal(),
      $update_processor->reveal(),
      $module_extension_list->reveal(),
      $module_handler->reveal(),
      $config_factory->reveal(),
      $time_if->reveal(),
    );
  }

  /**
   * The test teardown.
   */
  protected function tearDown(): void {
    $this->prophet->checkPredictions();
  }

}
