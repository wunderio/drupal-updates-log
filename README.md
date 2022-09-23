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
2. [Enable Diff mode if needed](#diff-mode)
3. Enable the module: `drush en -y updates_log`
4. Check the logs on daily bases.

## Usage

On daily bases it logs module statuses like this:

```
 ---- -------------- ------------- ---------- ---------------------------------------------
  ID   Date           Type          Severity   Message
 ---- -------------- ------------- ---------- ---------------------------------------------
  68   03/Jun 16:34   updates_log   Info       ("project":"drupal","status":"CURRENT")
  69   03/Jun 16:34   updates_log   Info       ("project":"module1","status":"NOT_SECURE")
  70   03/Jun 16:34   updates_log   Info       ("project":"module2","status":"NOT_CURRENT")
```

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

### Diff mode

Diff mode allows to track changes, rather than having full status dumps all the time.
In `settings.php` add the following:

```php
$config['updates_log']['diff'] = TRUE;
```

It would produce following log:
```
 ---- -------------- ------------- ---------- --------------------------------------------------------
  ID   Date           Type          Severity   Message
 ---- -------------- ------------- ---------- --------------------------------------------------------
  1    01/Jul 15:43   updates_log   Info       ("project":"drupal","old":"NOT_SECURE","new":"CURRENT")
 ---- -------------- ------------- ---------- --------------------------------------------------------
```

`old` and `new` denote statuses.
Respectively old status, and new status.

## Timing

Essentially two date strings are compared in format of `YYYYMMDD`.
If last datestamp and current one differ, the logs are issued.
The dates are generated according to the local time.

## State

The state of the module is kept in Drupal State `updates_log.last`.
The value represent the last time the logs were issued.
The value is stored as seconds since epoch.
It is needed for deciding when to send out the next batch of logs.

- `drush sget updates_log.last`
- `drush sset updates_log.last 1654253832`

When running in diff mode the status is kept in the state variable `updates_log.statuses`.

## Development of `updates_log`

- `lando install` - Install development packages of the module.
- `lando scan` - Run code scanners after development and before committing.
- `lando start` - Start up the development environment
- `lando test` - Run tests during/after development and before committing.

