# updates_log
Log Drupal project update statuses

## Install

1. `composer config repositories.updates_log vcs https://github.com/wunderio/drupal-updates-log.git`
2. `composer require wunderio/updates_log`
3. `drush en -y updates_log`

## Usage

On daily bases log module statuses.
For example:

```
  14309326   19/May 09:31   updates_log   Info       {"project":"webform","status":"CURRENT"}
  14309325   19/May 09:31   updates_log   Info       {"project":"warden","status":"CURRENT"}
  14309324   19/May 09:31   updates_log   Info       {"project":"video_embed_field","status":"CURRENT"}
```
