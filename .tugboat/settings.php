<?php

$databases = [];

$config_directories = array(
  CONFIG_SYNC_DIRECTORY => '/var/lib/tugboat/config/sync',
);

$settings['hash_salt'] = 'djfu983roawei90tk3;mva9023i9523';

$settings['update_free_access'] = FALSE;

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['entity_update_batch_size'] = 50;

$databases['default']['default'] = array (
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
if (PHP_SAPI === 'cli') {
  ini_set('memory_limit', '256M');
}
