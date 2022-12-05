<?php

/**
 * @file
 * Updates Log class.
 */

declare(strict_types=1);

namespace Drupal\updates_log;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\State;
use Drupal\update\UpdateManagerInterface;
use Drupal\update\UpdateProcessorInterface;


class UpdatesLog {

  public const TIME_STATE = 'updates_log.last';

  public const STATUSES_STATE = 'updates_log.statuses';

  private State $state;

  private LoggerChannelInterface $logger;

  /**
   * Last updates_log result.
   *
   * @var array|null
   */
  private ?array $lastStatuses;

  /**
   * Last time updates_log was ran.
   *
   * @var int|null
   */
  private ?int $lastRan;

  /**
   * Last time updates were checked.
   *
   * @var int
   */
  private int $lastUpdated;

  private UpdateManagerInterface $updateManager;

  private UpdateProcessorInterface $updateProcessor;


  public function __construct(State $state, LoggerChannelFactoryInterface $loggerChannelFactory, UpdateManagerInterface $updateManager, UpdateProcessorInterface $updateProcessor) {
    $this->state = $state;
    $this->logger = $loggerChannelFactory->get('updates_log');
    $this->updateManager = $updateManager;
    $this->updateProcessor = $updateProcessor;
    $this->lastStatuses = $state->get(self::STATUSES_STATE);
    $this->lastRan = $state->get(self::TIME_STATE);
    $this->lastUpdated = $state->get('update.last_check', 0);
  }


  /*
   * Business Logic
   */

  /**
   * The top-level logic of the module.
   */
  public function run(): void {

    $now = time();
    if (!$this->shouldUpdate($now)) {
      return;
    }

    $this->refresh();
    $statuses = $this->statusesGet();
    $diff = $this->computeDiff($statuses);
    if (!empty($diff)) {
      $this->logDiff($diff);
      $new_statuses = $this->statusesIntegrate($statuses);
      $this->state->set(self::STATUSES_STATE, $new_statuses);
    }
    if ($now <= $this->lastRan + (60 * 60 * 24)) {
      $statistics = $this->generateStatistics($statuses);
      $this->logStatistics($statistics);
    }
    $this->state->set(self::TIME_STATE, $now);
  }

  /**
   * Decides whether it's time for logging.
   *
   * @param int $now
   *   The epoch timestamp of the now.
   *
   * @return bool
   *   False = don't update. True = do update.
   */
  public function shouldUpdate(int $now): bool {
    if ($this->lastRan === NULL || getenv('UPDATES_LOG_TEST')) {
      return TRUE;
    }
    // run every hour
    return $now >= $this->lastRan + (60 * 60);
  }

  /**
   * Compute old and new status differences.
   *
   * @param array $new
   *   New statuses.
   *
   * @return array
   *   Statuses diff.
   */
  public function computeDiff(array $new): array {

    $diff = [];

    foreach ($new as $project => $data) {
      $status = $data['status'];
      if (!array_key_exists($project, $this->lastStatuses)) {
        $diff[$project] = [
          'old' => '',
          'new' => $status,
        ];
      }
      elseif ($status !== '???' || $this->lastStatuses[$project] !== $status) {
        $diff[$project] = [
          'old' => $this->lastStatuses[$project],
          'new' => $status,
        ];
      }

      unset($this->lastStatuses[$project]);
    }

    return $diff;
  }

  /**
   * Integrate old and new statuses in a safe way.
   *
   * @param array $new
   *   New statuses.
   *
   * @return array
   *   Integrated statuses.
   */
  public function statusesIntegrate(array $new): array {

    $int = [];

    foreach ($new as $project => $data) {
      $status = $data['status'];
      if ($status === '???' && array_key_exists($project, $this->lastStatuses)) {
        $status = $this->lastStatuses[$project];
      }
      $int[$project] = $status;
    }

    return $int;
  }
  /*
   * Presentation
   */

  /**
   * Log the modules, and statuses.
   *
   * @param array<string, array<string, string>> $statuses
   *   An associative array of ['module_name' => ['old' => 'status_string',
   *   'new' => 'status_string']].
   */
  public function logDiff(array $statuses): void {
    foreach ($statuses as $project => $status) {
      // Drupal logging cannot handle json in any way.
      $this->logger->info(
        "(\"project\":\"@project\",\"old\":\"@old\",\"new\":\"@new\")",
        [
          '@project' => $project,
          '@new' => $status['new'],
          '@old' => $status['old'],
        ]
      );
    }
  }

  /*
   * Drupal Integration
   */

  /**
   * Update module statuses, get the fresh data from internet.
   *
   * Ripped from update_cron().
   */
  public function refresh(): void {

    if (!empty(getenv('TESTING'))) {
      // We cannot boot properly from external script.
      // It corrupts the database.
      // See notes in init.php.
      return;
    }
    $this->updateManager->refreshUpdateData();
    $this->updateProcessor->fetchData();
    update_clear_update_disk_cache();
  }

  /**
   * Get module statuses from Drupal.
   *
   * @return array<string, string>
   *   Return array of ['module_name' => 'status_string'].
   */
  public function statusesGet(): array {

    $map = [

      // From web/core/modules/update/src/UpdateManagerInterface.php.
      1 => 'NOT_SECURE',
      2 => 'REVOKED',
      3 => 'NOT_SUPPORTED',
      4 => 'NOT_CURRENT',
      5 => 'CURRENT',

      // From web/core/modules/update/src/UpdateFetcherInterface.php.
      -1 => 'NOT_CHECKED',
      -2 => 'UNKNOWN',
      -3 => 'NOT_FETCHED',
      -4 => 'FETCH_PENDING',
    ];

    $available = update_get_available(TRUE);

    /** @var array<string, array{status: int}> $available */
    $project_data = update_calculate_project_data($available);

    ksort($project_data);
    $statuses = [];
    foreach ($project_data as $key => $data) {
      $status = $data['status'];
      if ($status < 0) {
        $status = '???';
      }
      elseif (empty($map[$status])) {
        $status = '???';
      }
      else {
        $status = $map[$status];
      }
      $statuses[$key] = [
        'status' => $status,
        'version' => $data['existing_version'],
      ];
    }

    return $statuses;
  }

  private function generateStatistics(array $statuses): array {
    $statistics = [
      "updates_log" => "2.0",
      "last_check_epoch" => $this->lastUpdated,
      "last_check_human" => date('Y-m-dTh:i:s', $this->lastUpdated),
      "last_check_ago" => time() - $this->lastUpdated,
      "summary" => [
        "CURRENT" => 0,
        "OUTDATED" => 0,
        "NOT_SECURE" => 0,
        "NOT_SUPPORTED" => 0,
        "REVOKED" => 0,
        "UNKNOWN" => 0,
      ],
      'details' => [],
    ];
    foreach ($statuses as $project => $data) {
      $status = $data['status'];
      $statistics['summary'][$status] += 1;
      if ($status === 'CURRENT') {
        continue;
      }
      $statistics['details'][$project] = $data;
    }

    return $statistics;
  }

  private function logStatistics($statistics) {
    //ToDo
  }

}
