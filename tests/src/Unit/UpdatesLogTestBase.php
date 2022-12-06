<?php
namespace Drupal\Tests\updates_log\Unit;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\State;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\update\UpdateManagerInterface;
use Drupal\update\UpdateProcessorInterface;
use Drupal\updates_log\UpdatesLog;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class UpdatesLogTestBase extends UnitTestCase {

  protected UpdatesLog $updates_log;

  protected State $state;


  protected function setUp() :void
  {
    parent::setUp();
    $this->state = new State(new KeyValueMemoryFactory());

    $logger = $this->prophesize('Drupal\Core\Logger\LoggerChannelInterface');
    foreach (get_class_methods(LoggerInterface::class) as $logger_method) {
      $logger->{$logger_method}(Argument::cetera())->will(function () {
        \Drupal::state()->set('cron_test.message_logged', TRUE);
      });
    }

    $state = $this->prophesize(State::class);
    $state->get('update.last_check', 0)->willReturn(time());

    $logger_factory = $this->prophesize(LoggerChannelFactoryInterface::class);
    $logger_factory->get(Argument::exact('updates_log'))->willReturn($logger->reveal());
    $update_manager = $this->prophesize(UpdateManagerInterface::class);
    $update_processor = $this->prophesize(UpdateProcessorInterface::class);


    \Drupal::setContainer(new ContainerBuilder());
    \Drupal::getContainer()->set('logger.factory', $logger_factory->reveal());
    \Drupal::getContainer()->set('state', $state);



    $this->updates_log = new UpdatesLog($state->reveal(), $logger_factory->reveal(), $update_manager->reveal(), $update_processor->reveal());
  }
}

