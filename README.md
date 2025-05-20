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

1. Install the module: `composer require wunderio/updates_log:^2`
2. Install a core patch for `update` module [bug](https://www.drupal.org/project/drupal/issues/2920285):
    1. For D9 use [this patch](https://www.drupal.org/files/issues/2021-06-12/2920285-23.patch)
    2. For D10 use [this patch](https://www.drupal.org/files/issues/2022-03-30/update-module-stuck-mr782-dedup-2920285-35.patch)
    3. For D10.1.5+ use [this patch](https://www.drupal.org/files/issues/2023-10-10/update-module-stuck.patch)
3. Enable the module: `drush en -y updates_log`
4. Optional: By using [Config Split](https://www.drupal.org/project/config_split) keep module enabled only in the default branch.
5. Export the configuration: `drush cex -y`
6. To verify the operations run `drush cron`. At the first cron execution it will report all the modules from "unknown" state to the "known" state. Check your logs!

## Usage

On hourly basis it logs the differences of the statuses of modules like this (if there are any changes):

```
 ---- -------------- ------------- ---------- ------------------------------------------------------------------------------------------------------
  ID   Date           Type          Severity   Message
 ---- -------------- ------------- ---------- ------------------------------------------------------------------------------------------------------
  1    01/Jul 15:43   updates_log   Info      updates_log={"project":"drupal","old":"CURRENT","new":"NOT_SECURE","site":"example.com","env:"prod"}==
 ---- -------------- ------------- ---------- ------------------------------------------------------------------------------------------------------
```

`old` and `new` denote statuses.
Respectively old status, and new status.
The above log can be understood like this: `drupal` package was up-to-date in earlier run and changed its status now (security update was released), so the status changed from `CURRENT` to `NOT_SECURE`.

Status codes are taken from the Drupal code:

- `web/core/modules/update/src/UpdateManagerInterface.php`
  - `NOT_SECURE`
  - `REVOKED`
  - `NOT_SUPPORTED`
  - `NOT_CURRENT`
  - `CURRENT`

- `web/core/modules/update/src/UpdateFetcherInterface.php`
  - `NOT_CHECKED`
  - `UNKNOWN`
  - `NOT_FETCHED`
  - `FETCH_PENDING`

## Timing

The full statistics log entry is generated in approx 24h interval.

The diff log entries may be generated as often as once per hour.

## State

`updates_log.last` - Only hourly last run timestamp is kept here. The value is kept in epoch seconds. If there is a necessity to observe or change the values, these are the reference commands:

- `drush sget updates_log.last`
- `drush sset updates_log.last 1654253832`

`updates_log_statistics.last` - Only 24h last run timestamp is kept here. The value is kept in epoch seconds. Similar reference commands apply as shown above.

`updates_log.statuses` - Module "current" statuses are kept in this state variable. Required to be able to perform diff. To observe the contents of it run the following command: `drush sget updates_log.statuses --format=json`.

## Output

The generic format is `id={json}==`. There are two equal-signs at the end to mark the end of the JSON. It is needed, because in some logging environment there is additional encapsulation used which makes parsing impossible.

### Diff

When there are any changes in module statuses, then their output in the logs looks as follows:

```
updates_log={
  project: "webform",
  old: "NOT_CURRENT",
  new: "CURRENT"
  site: "example.com"
  env: "prod"
}==
```

Every state change will have its own log entry.

### Statistics

The module also logs "Statistics" once in 24h that gives a quick overview about how many modules there are and in what statuses.
```
updates_log_statistics={
  "updates_log": "2.5.0",
  "last_check_epoch": 1672835445,
  "last_check_human": "2023-01-04T12:30:450GMT",
  "last_check_ago": 16,
  "site": "project-acme-support",
  "env": "prod",
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
}==
```

The "prefix" (`updates_log_statistics=`) is there to help filter and parse the data from the log entry.

### Site

The `site` identifies project.
It is detected by using first non-empty item:
- `$settings['updates_log_site']`
- Env `PROJECT_NAME`
- Env `HOSTNAME`
- Env `DRUSH_OPTIONS_URI` + hostname extraction
- `"unknown"`

### Env

The `env` identifies environment (dev, staging, producion, etc).
It is detected by using first non-empty item:
- `$settings['updates_log_env']`
- Env `ENVIRONMENT_NAME`
- Env `WKV_SITE_ENV`
- Settings `simple_environment_indicator` + color removal
- `"unknown"`

## Settings

You can add `$settings['updates_log_disabled'] = TRUE;` in your `settings.php` to stop updates_log from reporting.

This is useful for sites that want to report updates in only one environment.

## Development of `updates_log`

- Clone [drupal-project](https://github.com/wunderio/drupal-project) as a base
- Clone `updates_log` project into `web/modules/custom/updates_log`
- Edit `.lando.yml` to disable unneeded services and their proxies (`chrome`, `elasticsearch`, `kibana`, `mailhog`, `node`)
- `lando start` - Start up the development environment
- `lando composer install` - Install GrumPHP
- `lando drush site-install` - Populate the database
- `lando drush en updates_log` - Enable the module
- `lando drush cron` or
  - ssh into the container `lando ssh` and run `UPDATES_LOG_TEST=1 drush cron` to bypass the time checks
- `lando grumphp run` for code scanning
- `lando phpunit --group=updates_log` for running tests

### Making releases

See `.github/PULL_REQUEST_TEMPLATE.md`

## Debugging - What to do when you don't see expected results?

Use the `UPDATES_LOG_TEST` environment variable to bypass the time requirement for testing `UPDATES_LOG_TEST=1 drush cron` or `UPDATES_LOG_TEST=1 drush eval 'updates_log_cron();'`. This applies to both (hourly and daily) functional modes. After running this you should get full statistics in logs, and if there are any state changes, these should have its own log entries too.

Here are few more things to try:

- Drupal `update` module:
  - Make sure `/admin/reports/updates/settings` loads, and is configured. Save the form again.
  - Check the status at "Available updates" report. Is it red or green?
  - `drush eval 'var_dump(update_get_available(TRUE));'` - should return large array.
  - `drush eval '$available = update_get_available(TRUE); $project_data = update_calculate_project_data($available); var_dump($project_data);'`
  - `drush ev '\Drupal::keyValue("update_fetch_task")->deleteAll();'` - after `update` reinstall
  - `drush sqlq 'truncate batch'`
  - `drush sqlq 'truncate queue'`
  - `drush pm-uninstall -y update; drush pm-install -y update`
  - `drush sdel update.last_check`
- Updates Log:
  - `UPDATES_LOG_TEST=1 drush cron`
  - `UPDATES_LOG_TEST=1 drush eval 'updates_log_cron();'`
  - `drush sget updates_log.statuses --format=json`
  - `drush sget updates_log.last`
  - `drush sget updates_log_statistics.last`

## Drupal core bug

There is a Drupal [core bug](https://www.drupal.org/project/drupal/issues/2920285) which in certain situation would not fetch new data, or would not fetch it for some projects. See details in the install instructions.
