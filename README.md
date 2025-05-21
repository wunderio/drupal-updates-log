# Updates Log

## Project overview

Updates Log is a Drupal module that logs project update statuses. It helps track security updates across multiple Drupal installations by logging module statuses on a daily basis and creating alerts based on these logs.

### Why use Updates Log?

When managing many Drupal sites, keeping track of security updates can be challenging. Updates Log provides:

- Daily logging of module status changes
- Centralized logging compatible with systems like SumoLogic
- Ability to create custom alerts (e.g., on Slack) based on logs
- Comprehensive statistics for analysis and monitoring

While alternatives like Warden exist, Updates Log offers more configurable alerting capabilities.

## Team

- **Ragnar Kurm** - Maintainer
- **Wunder** - Development and support

## Distribution

- [Packagist](https://packagist.org/packages/wunderio/updates_log)
- [GitHub](https://github.com/wunderio/drupal-updates-log)

## Installation

### Requirements

- Drupal 9, 10, or 11
- Composer
- Drush

### Installation steps

1. Install the module using Composer:

   ```bash
   composer require wunderio/updates_log:^2
   ```

2. Install a core patch for the `update` module [bug](https://www.drupal.org/project/drupal/issues/2920285):
   - For Drupal 9 use [this patch](https://www.drupal.org/files/issues/2021-06-12/2920285-23.patch)
   - For Drupal 10 use [this patch](https://www.drupal.org/files/issues/2022-03-30/update-module-stuck-mr782-dedup-2920285-35.patch)
   - For Drupal 10.1.5+ use [this patch](https://www.drupal.org/files/issues/2023-10-10/update-module-stuck.patch)
   - For Drupal 10.2.2+ use [this patch](https://www.drupal.org/files/issues/2024-01-26/2920285-51.patch)
   - For Drupal 11, you may still encounter this issue. If you do, try the workarounds in the troubleshooting section

3. Enable the module:

   ```bash
   drush en -y updates_log
   ```

4. Optional: By using [Config Split](https://www.drupal.org/project/config_split), keep the module enabled only in the default branch.

5. Export the configuration:

   ```bash
   drush cex -y
   ```

6. Verify the installation by running cron:

   ```bash
   drush cron
   ```

   At the first cron execution, it will report all modules from "unknown" state to the "known" state. Check your logs!

## Usage

On hourly basis it logs the differences of the statuses of modules like this (if there are any changes):

```text
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

```json
updates_log={
  "project": "webform",
  "old": "NOT_CURRENT",
  "new": "CURRENT",
  "site": "example.com",
  "env": "prod"
}==
```

Every state change will have its own log entry.

### Statistics

The module also logs "Statistics" once in 24h that gives a quick overview about how many modules there are and in what statuses.

```json
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

The `env` identifies environment (dev, staging, production, etc).
It is detected by using first non-empty item:

- `$settings['updates_log_env']`
- Env `ENVIRONMENT_NAME`
- Env `WKV_SITE_ENV`
- Settings `simple_environment_indicator` + color removal
- `"unknown"`

## Settings

You can add `$settings['updates_log_disabled'] = TRUE;` in your `settings.php` to stop updates_log from reporting.

This is useful for sites that want to report updates in only one environment.

## Development

### Setting up a development environment

1. Clone [drupal-project](https://github.com/wunderio/drupal-project) as a base:

   ```bash
   git clone https://github.com/wunderio/drupal-project.git
   cd drupal-project
   ```

2. Clone the `updates_log` project into the modules directory:

   ```bash
   git clone https://github.com/wunderio/drupal-updates-log.git web/modules/custom/updates_log
   ```

3. Edit `.lando.yml` to disable unneeded services and their proxies:

   ```yaml
   # Disable: chrome, elasticsearch, kibana, mailpit, node
   ```

4. Start the development environment:

   ```bash
   lando start
   ```

5. Install dependencies:

   ```bash
   lando composer install
   ```

6. Set up the Drupal site:

   ```bash
   lando drush site-install
   lando drush en updates_log
   ```

7. Run cron to test the module:

   ```bash
   lando drush cron
   ```

   Or bypass time checks for testing:

   ```bash
   lando ssh
   UPDATES_LOG_TEST=1 drush cron
   ```

### Testing and quality assurance

The module includes automated tests and code quality tools:

1. Run code quality checks:

   ```bash
   lando grumphp run
   ```

2. Run PHPUnit tests:

   ```bash
   lando phpunit --group=updates_log
   ```

### Making releases

See the [PR template](.github/PULL_REQUEST_TEMPLATE.md) for the release process.

## Troubleshooting

### Testing without time restrictions

Use the `UPDATES_LOG_TEST` environment variable to bypass the time requirement for testing:

```bash
UPDATES_LOG_TEST=1 drush cron
```

or

```bash
UPDATES_LOG_TEST=1 drush eval 'updates_log_cron();'
```

This applies to both hourly and daily functional modes. After running this, you should get full statistics in logs, and if there are any state changes, these should have their own log entries too.

### Debugging the Drupal update module

If you're experiencing issues with the update module:

1. Verify the update settings:
   - Make sure `/admin/reports/updates/settings` loads and is configured correctly
   - Save the form again to ensure settings are applied
   - Check the status at "Available updates" report - is it red or green?

2. Debug update data:

   ```bash
   drush eval 'var_dump(update_get_available(TRUE));'
   ```

   This should return a large array.

3. Check project data:

   ```bash
   drush eval '$available = update_get_available(TRUE); $project_data = update_calculate_project_data($available); var_dump($project_data);'
   ```

4. Reset update module state (try these solutions in order until one works):

   ```bash
   # Solution 1: Clear the update fetch task
   drush php:eval "\Drupal::keyValue('update_fetch_task')->deleteAll();"

   # Solution 2: If Solution 1 doesn't work, try uninstalling and reinstalling the update module
   drush pm-uninstall -y update && drush pm-enable -y update

   # Solution 3: If Solutions 1 and 2 don't work, try the full reset
   drush ev '\Drupal::keyValue("update_fetch_task")->deleteAll();'
   drush sqlq 'truncate batch'
   drush sqlq 'truncate queue'
   drush pm-uninstall -y update; drush pm-install -y update
   drush sdel update.last_check
   ```

### Debugging Updates Log module

Check the state of the Updates Log module:

```bash
drush sget updates_log.statuses --format=json
drush sget updates_log.last
drush sget updates_log_statistics.last
```

## Known issues

### Drupal core bug

There is a Drupal [core bug](https://www.drupal.org/project/drupal/issues/2920285) which in certain situations would not fetch new data, or would only fetch it for some projects but not others. This issue has been reported across multiple Drupal versions including Drupal 9, 10, and 11.

Symptoms of this issue include:

- "No update information available" message
- Only some modules showing update information while others don't
- Update information not refreshing even after running cron

The issue can sometimes be resolved by:

1. Applying the appropriate patch for your Drupal version (see installation instructions)
2. Clearing the update fetch task cache (see troubleshooting section)
3. Uninstalling and reinstalling the update module

This issue has been partially fixed in various Drupal versions, but may still occur. The patches and workarounds listed in this README have been reported to help in most cases.

## Contributing

Contributions to the Updates Log module are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests to ensure code quality
5. Submit a pull request

Please follow the coding standards and include tests for new functionality.
