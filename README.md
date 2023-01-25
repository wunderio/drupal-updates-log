# Updates Log

Log Drupal project update statuses.

Why? When having many Drupals around then keeping track of security updates can
be challenging. One option is to log statuses of the modules on daily bases,
and create alerts (for example on Slack) based on the logs. It makes sense on
centralized logging systems like SumoLogic. It allows to create all kinds stats
and analysis.

As an alternative there is Warden, but it lacks highly configurable alerting.

## Distribution

- [Packagist](https://packagist.org/packages/wunderio/updates_log)
- [GitHub](https://github.com/wunderio/drupal-updates-log)

## Install

1. Install the module: `composer require wunderio/updates_log:^1`
2. Enable the module: `drush en -y updates_log`
3. Optional: By using [Config Split](https://www.drupal.org/project/config_split) keep module enabled only in the default branch.
4. Export the configuration: `drush cex -y`
5. NB! In diff mode there will be nothing in logs immediately, and maybe even not in coming weeks, unless any of the
   packages change state.

## Usage

On hourly basis it logs modules status differences like this:

```
 ---- -------------- ------------- ---------- --------------------------------------------------------------------
  ID   Date           Type          Severity   Message
 ---- -------------- ------------- ---------- --------------------------------------------------------------------
  1    01/Jul 15:43   updates_log   Info       updates_log={"project":"drupal","old":"CURRENT","new":"NOT_SECURE"}
 ---- -------------- ------------- ---------- --------------------------------------------------------------------
```

`old` and `new` denote statuses.
Respectively old status, and new status.
The above log can be understood like this: `drupal` package was up-to-date yesterday, changed its status (security update was released), so the status changed from yesterday's `CURRENT` to today's `NOT_SECURE`.

Status codes are taken from the Drupal code:

- `web/core/modules/update/src/UpdateManagerInterface.php`
  - `NOT_SECURE`
  - `REVOKED`
  - `NOT_SUPPORTED`
  - `NOT_CURRENT`
  - `CURRENT`

- `web/core/modules/update/src/UpdateFetcherInterface.php`
  - `???` (`NOT_CHECKED`)
  - `???` (`UNKNOWN`)
  - `???` (`NOT_FETCHED`)
  - `???` (`FETCH_PENDING`)

## Timing

Essentially two date strings are compared in format of `YYYYMMDD`.
If last datestamp and current one differ, the logs are issued.
The dates are generated according to the local time.
Messages are sent when more than 1h has passed after last run.

Use the "UPDATES_LOG_TEST" env variable to bypass the time requirement for testing `UPDATES_LOG_TEST=1 drush cron`

## State

The state of the module is kept in Drupal State `updates_log.last`.
The value represent the last time the logs were issued.
The value is stored as seconds since epoch.
It is needed for deciding when to send out the next batch of logs.

- `drush sget updates_log.last`
- `drush sset updates_log.last 1654253832`

The status is kept in the state variable `updates_log.statuses`.
- `drush sget updates_log.statuses --format=json`

## Statistics

The module also logs "Statistics" once a day that gives a quick overview about how many modules and in what states they are.
```
{
  "updates_log": "2.0",
  "last_check_epoch": 1672835445,
  "last_check_human": "2023-01-04T12:30:450GMT",
  "last_check_ago": 16,
  "summary": {
    "CURRENT": 31,
    "NOT_CURRENT": 0,
    "NOT_SECURE": 0,
    "NOT_SUPPORTED": 1,
    "REVOKED": 0,
    "UNKNOWN": 0
  },
  "details": {
    "NOT_SUPPORTED": {
       "admin_toolbar": "3.1.0"
    }
  }
}
```
The last run time is kept in State `updates_log_statistics.last`

## Development of `updates_log`

For Development, I suggest the [drupal-project](https://github.com/wunderio/drupal-project) as a base.

- `lando start` - Start up the development environment
- clone this project into `web/modules/custom/updates_log`
- `lando drush en updates_log` enable the module
- `lando drush cron` or
  - ssh into the container `lando ssh` and run `UPDATES_LOG_TEST=1 drush cron` to bypass the time checks
- `lando grumphp run` for code scanning
- `lando phpunit --group=updates_log` for running tests

## Debugging - What to do when you don't see expected results?

- Check the status at "Available updates" report. Is it red or green?
- Run this `drush eval '$available = update_get_available(TRUE); $project_data = update_calculate_project_data($available); var_dump($project_data);'`
- Run this `drush sget updates_log.statuses --format=json`
- Run this `drush sget updates_log.last`
- Run this `drush sget updates_log_statistics.last`
