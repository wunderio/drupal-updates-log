<?php

declare(strict_types=1);

namespace Drupal\updates_log;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\update\UpdateManagerInterface;
use Drupal\update\UpdateProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * The UpdatesLog class.
 */
class UpdatesLog {

  public const TIME_STATE = 'updates_log.last';

  public const STATUSES_STATE = 'updates_log.statuses';

  public const STATISTICS_TIME_STATE = 'updates_log_statistics.last';

  /**
   * Last time updates were checked.
   *
   * @var int
   */
  private int $lastUpdated;

  /**
   * The StateInterface.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private StateInterface $state;

  /**
   * The Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * The UpdateManagerInterface.
   *
   * @var \Drupal\update\UpdateManagerInterface
   */
  private UpdateManagerInterface $updateManager;

  /**
   * The UpdateProcessorInterface.
   *
   * @var \Drupal\update\UpdateProcessorInterface
   */
  private UpdateProcessorInterface $updateProcessor;

  /**
   * UpdatesLog constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The StateInterface.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The LoggerChannelInterface.
   * @param \Drupal\update\UpdateManagerInterface $updateManager
   *   The UpdateManagerInterface.
   * @param \Drupal\update\UpdateProcessorInterface $updateProcessor
   *   The UpdateProcessorInterface.
   */
  public function __construct(
    StateInterface $state,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    UpdateManagerInterface $updateManager,
    UpdateProcessorInterface $updateProcessor
  ) {
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
    if (getenv('UPDATES_LOG_TEST') || ($now >= $this->getLastRanStatistics() + (60 * 60 * 24))) {
      $version = $this->getVersion();
      $statistics = $this->generateStatistics($statuses, $version);
      $this->logStatistics($statistics);
      $this->state->set(self::STATISTICS_TIME_STATE, $now);
    }
    $this->state->set(self::TIME_STATE, $now);
  }

  /**
   * Decides whether it's time for logging.
   *
   * @param int $now
   *   The epoch timestamp of the now.
   * @param int|null $last
   *   Last report time.
   *
   * @return bool
   *   False = don't update. True = do update.
   */
  public function shouldUpdate(int $now, ?int $last): bool {
    if ($last === NULL || getenv('UPDATES_LOG_TEST')) {
      return TRUE;
    }
    // Run every hour.
    return $now >= $last + (60 * 60);
  }

  /**
   * Compute old and new status differences.
   *
   * @param array $new
   *   New statuses.
   * @param array $old
   *   Old statuses.
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
   * @param array $old
   *   Old statuses.
   *
   * @return array
   *   Integrated statuses.
   */
  public function statusesIntegrate(array $new, array $old): array {

    $integrated = [];

    foreach ($new as $project => $data) {
      $status = $data['status'];
      if ($status === '???' && array_key_exists($project, $old)) {
        $status = $old[$project];
      }
      $integrated[$project] = $status;
    }

    return $integrated;
  }

  /*
   * Presentation
   */

  /**
   * Log the modules, and statuses.
   *
   * @param array[] $statuses
   *   An associative array of ['module_name' => ['old' => 'status_string',
   *   'new' => 'status_string']].
   */
  public function logDiff(array $statuses): void {
    foreach ($statuses as $project => $status) {
      $log = [
        'project' => $project,
        'old' => $status['old'],
        'new' => $status['new'],
      ];
      try {
        $json = json_encode($log, JSON_THROW_ON_ERROR);
      }
      catch (\Exception $exception) {
        $json = $exception->getMessage();
      }
      $this->logger->info('updates_log=@placeholder', ["@placeholder" => $json]);
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

    $this->updateManager->refreshUpdateData();
    $this->updateProcessor->fetchData();
    update_clear_update_disk_cache();
  }

  /**
   * Get module statuses from Drupal.
   *
   * @return array
   *   Return array of statuses.
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

  /**
   * Fetch version string of updates_log module itself.
   *
   * @return string
   *   A version string like "1.2.3".
   */
  public function getVersion(): string {
    $data = \Drupal::service('extension.list.module')->getExtensionInfo('updates_log');
    $version = $data['version'];
    return $version;
  }

  /**
   * Generates "Statistics" of module states and versions.
   *
   * @param array $statuses
   *   An array of statuses.
   *
   * @return array
   *   The statistics array.
   */
  public function generateStatistics(array $statuses, string $version): array {
    $statistics = [
      "updates_log" => $version,
      "last_check_epoch" => $this->lastUpdated,
      "last_check_human" => gmdate('Y-m-d\Th:i:sZT', $this->lastUpdated),
      "last_check_ago" => time() - $this->lastUpdated,
      "summary" => [
        "CURRENT" => 0,
        "NOT_CURRENT" => 0,
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
      $statistics['details'][$status][$project] = $data['version_used'];
    }

    return $statistics;
  }

  /**
   * Logs the given Statistics in json using the Logger.
   *
   * @param array $statistics
   *   The statistics array.
   */
  public function logStatistics(array $statistics): void {
    try {
      $json = json_encode($statistics, JSON_THROW_ON_ERROR);
    }
    catch (\Exception $exception) {
      $json = $exception->getMessage();
    }
    $this->logger->info('updates_log_statistics=@placeholder', ["@placeholder" => $json]);
  }

  /**
   * Get the last time UpdatesLog was run.
   */
  private function getLastRan(): ?int {
    return $this->state->get(self::TIME_STATE);
  }

  /**
   * Get the statuses from the last time UpdatesLog was run.
   */
  private function getLastStatuses(): array {
    return $this->state->get(self::STATUSES_STATE, []);
  }

  /**
   * Get the last time Statistics was logged.
   */
  private function getLastRanStatistics(): ?int {
    return $this->state->get(self::STATISTICS_TIME_STATE);
  }

}
