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
  public function Run(): void {

    $now = time();
    $last = $this->LastGet();
    if (!$this->ShouldUpdate($now, $last)) {
      return;
    }

    $this->Refresh();
    $statuses = $this->StatusesGet();
    $this->Log($statuses);
    $this->LastSet($now);
  }

  /**
   * Decides wether it's time for logging.
   *
   * @param int $now
   *   The epoch timestamp of the now.
   * @param int $last
   *   Last report time (spoch seconds).
   *
   * @return bool
   *   False = don't update. True = do update.
   */
  public function ShouldUpdate(int $now, ?int $last): bool {

    if (empty($last)) {
      return TRUE;
    }

    $now = date('Ymd', $now);
    $last = date('Ymd', $last);
    $status = $now !== $last;

    return $status;
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
  public function LastGet(): ?int {
  
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
  public function LastSet(?int $time): void {
    \Drupal::state()->set('updates_log.last', $time);
  }

  /*
   * Storage
   */

  /**
   * Log the modules, and statuses.
   *
   * @param array<string, string> $statuses
   *   An associative array of ['module_name' => 'status_string'].
   */
  function Log(array $statuses): void {
    $logger = \Drupal::logger('updates_log');
    foreach ($statuses as $key => $status) {
      $message = [
        'project' => $key,
        'status' => $status,
      ];
      // It is problematic to get JSON logged.
      // https://stackoverflow.com/a/67669934/1602728
      $logger->info(
        "{json}",
        $message
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
  public function Refresh(): void {
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
  public function StatusesGet(): array {

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
      $status = empty($map[$status]) ? '???' : $map[$status];
      $statuses[$key] = $status;
    }

    return $statuses;
  }

}
