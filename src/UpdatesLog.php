<?php

/**
 * @file
 * Updates Log class.
 */

declare(strict_types = 1);

namespace Drupal\updates_log;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\State\State;



class UpdatesLog {

  CONST LAST_TIME_RAN = 'updates_log.last';
  CONST LAST_STATUSES = 'updates_log.statuses';

  private State $state;

  private LoggerChannelInterface $logger;


  public function __construct(State $state, LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->state = $state;
    $this->logger = $loggerChannelFactory->get('updates_log');
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
    $oldStatuses = $this->statusesLoad();
    $diff = $this->computeDiff($statuses, $oldStatuses);
    if (!empty($diff)) {
      $this->logDiff($diff);
      $statuses2 = $this->statusesIntegrate($statuses, $oldStatuses);
      $this->statusesSave($statuses2);
    }
    $this->state->set(self::LAST_TIME_RAN, $now);
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
    $last = $this->state->get('updates_log.last');
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
   * @param array $old
   *   Old statuses.
   *
   * @return array
   *   Statuses diff.
   */
  public function computeDiff(array $new, array $old): array {

    $diff = [];

    foreach ($new as $project => $status) {
      if (!array_key_exists($project, $old)) {
        $diff[$project] = [
          'old' => '',
          'new' => $status,
        ];
        goto next;
      }
      else if ($status == '???') {
        goto next;
      }
      if ($old[$project] == $status) {
        goto next;
      }
      $diff[$project] = [
        'old' => $old[$project],
        'new' => $status,
      ];

      next:
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

    $int = [];

    foreach ($new as $project => $status) {
      if ($status == '???' && array_key_exists($project, $old)) {
        $status = $old[$project];
      }
      $int[$project] = $status;
    }

    return $int;
  }

  /*
   * Storage
   */

  /**
   * Save statuses.
   *
   * @param array $statuses
   *   Statuses to save.
   */
  public function statusesSave(array $statuses): void {
    \Drupal::state()->set('updates_log.statuses', $statuses);
  }

  /**
   * Get statuses of last time.
   *
   * @return array
   *   Statuses of last time.
   */
  public function statusesLoad(): array {
    return \Drupal::state()->get('updates_log.statuses', []);
  }

  /*
   * Presentation
   */

  /**
   * Log the modules, and statuses.
   *
   * @param array<string, array<string, string>> $statuses
   *   An associative array of ['module_name' => ['old' => 'status_string', 'new' => 'status_string']].
   */
  public function logDiff(array $statuses): void {
    $logger = \Drupal::logger('updates_log');
    foreach ($statuses as $project => $status) {
      // Drupal logging cannot handle json in any way.
      $logger->info(
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

    update_refresh();
    update_fetch_data();
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

    /** @var array<mixed> */
    $available = update_get_available(TRUE);

    /** @var array<string, array{status: int}> */
    $project_data = update_calculate_project_data($available);

    ksort($project_data);
    $statuses = [];
    foreach ($project_data as $key => $data) {
      $status = $data['status'];
      if ($status < 0) {
        $status = '???';
      }
      else if (empty($map[$status])) {
        $status = '???';
      }
      else {
        $status = $map[$status];
      }
      $statuses[$key] = $status;
    }

    return $statuses;
  }
}
