class { 'systools': }
class { 'apache': }
class { 'php': }
class { 'drush': }
class { 'postfix': }

class { 'mysql':
  local_only     => true,
  hostname => '33.33.33.10'
}

apache::vhost { "drupal": }
