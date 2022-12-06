<?php

/**
 * @file
 * Updates Log class.
 */

declare(strict_types=1);

namespace Drupal\updates_log;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\StateInterface;
use Drupal\update\UpdateManagerInterface;
use Drupal\update\UpdateProcessorInterface;


class UpdatesLog {

  public const TIME_STATE = 'updates_log.last';

  public const STATUSES_STATE = 'updates_log.statuses';

  /**
   * Last time updates were checked.
   *
   * @var int
   */
  private int $lastUpdated;

  private StateInterface $state;

  private LoggerChannelInterface $logger;

  private UpdateManagerInterface $updateManager;

  private UpdateProcessorInterface $updateProcessor;


  public function __construct(
    StateInterface                $state,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    UpdateManagerInterface        $updateManager,
    UpdateProcessorInterface      $updateProcessor
  )
  {
    $this->state = $state;
    $this->logger = $loggerChannelFactory->get('updates_log');
    $this->updateManager = $updateManager;
    $this->updateProcessor = $updateProcessor;
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
    $last = $this->getLastRan();
    if (!$this->shouldUpdate($now, $last)) {
      return;
    }

    $this->refresh();
    $statuses = $this->statusesGet();
    $old_statuses = $this->getLastStatuses();

    $diff = $this->computeDiff($statuses, $old_statuses);

    if (!empty($diff)) {
      $this->logDiff($diff);
      $new_statuses = $this->statusesIntegrate($statuses, $old_statuses);
      $this->state->set(self::STATUSES_STATE, $new_statuses);
    }
    if ($now <= $this->getLastRan() + (60 * 60 * 24)) {
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
  public function shouldUpdate(int $now, ?int $last): bool {
    if ($last === NULL || getenv('UPDATES_LOG_TEST')) {
      return TRUE;
    }
    // run every hour
    return $now >= $last + (60 * 60);
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
  public function computeDiff(array $new, array $old): array {

    $diff = [];

    foreach ($new as $project => $data) {
      $status = $data['status'];
      if (!array_key_exists($project, $old)) {
        $diff[$project] = [
          'old' => '',
          'new' => $status,
        ];
      }
      elseif ($status !== '???' && $old[$project] !== $status) {
        $diff[$project] = [
          'old' => $old[$project],
          'new' => $status,
        ];
      }

      unset($old[$project]);
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
  public function statusesIntegrate(array $new, array $old): array {

    $int = [];

    foreach ($new as $project => $data) {
      $status = $data['status'];
      if ($status === '???' && array_key_exists($project, $old)) {
        $status = $old[$project];
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
    // TODO do same JSON as statistics
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
        'version_used' => $data['existing_version'],
      ];
    }

    return $statuses;
  }

  private function generateStatistics(array $statuses): array {
    $statistics = [
      "updates_log" => "2.0",
      "last_check_epoch" => $this->lastUpdated,
      "last_check_human" => gmdate('Y-m-d\Th:i:sZT', $this->lastUpdated),
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
      if (array_key_exists($status, $statistics['summary'])) {
        $statistics['summary'][$status] += 1;
      }
      else {
        $statistics['summary']['UNKNOWN'] += 1;
      }

      if ($status === 'CURRENT') {
        continue;
      }
      $statistics['details'][$project] = $data;
    }

    return $statistics;
  }

  /**
   * @param array $statistics
   *
   * @return void
   */
  private function logStatistics(array $statistics): void {
    try {
      $json = json_encode($statistics, JSON_THROW_ON_ERROR);
    }
    catch (\Exception $exception) {
      $json = $exception->getMessage();
    }
    $this->logger->info('updates_log={placeholder}', ["placeholder" => $json]);
  }

  private function getLastRan()
  {
    return $this->state->get(self::TIME_STATE);
  }

  private function getLastStatuses()
  {
    return $this->state->get(self::STATUSES_STATE);
  }

}
