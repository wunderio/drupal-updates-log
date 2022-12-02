<?php

/**
 * @file
 * Updates Log class.
 */

declare(strict_types = 1);

namespace Drupal\updates_log;

class UpdatesLog {

  /*
   * Business Logic
   */

  /**
   * The top-level logic of the module.
   */
  public function run(): void {

    $now = time();
    $last = $this->lastGet();
    if (!$this->shouldUpdate($now, $last)) {
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
    $this->lastSet($now);
  }

  /**
   * Decides whether it's time for logging.
   *
   * @param int $now
   *   The epoch timestamp of the now.
   * @param int $last
   *   Last report time (epoch seconds).
   *
   * @return bool
   *   False = don't update. True = do update.
   */
  public function shouldUpdate(int $now, ?int $last): bool {

    if (empty($last)) {
      return TRUE;
    }

    $now = date('Ymd', $now);
    $last = date('Ymd', $last);
    $status = $now !== $last;

    return $status;
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
   * Get the last update time.
   *
   * @return int
   *   Return int of last update time, or NULL when first time.
   */
  public function lastGet(): ?int {

    /** @var ?mixed */
    $last = \Drupal::state()->get('updates_log.last');

    $last = empty($last) ? NULL : intval($last);

    return $last;
  }

  /**
   * Set the last update time.
   *
   * @param int $time
   *   Set update last time logged.
   */
  public function lastSet(?int $time): void {
    \Drupal::state()->set('updates_log.last', $time);
  }

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
