<?php
namespace Nosh\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for creating projects (also called platforms).
 */
class VagrantifyCommand extends Command
{
  protected $variableOptions = array(
    'webroot',
    'ip',
    'hostname',
    'boxname',
    'boxurl'
  );
  protected function configure()
  {
    $this->setName('vagrantify')
      ->setDescription('Add a vagrant configuration capable of running Drupal sites.')
      ->addOption('path', null, InputOption::VALUE_OPTIONAL, "The path to the project. The current working directory will be used if this isn't specified.", ".")
      ->addOption('hostname', null, InputOption::VALUE_OPTIONAL, "The hostname of the virtual machine. Defaults to devbox.dev", "devbox.dev")
      ->addOption('webroot', null, InputOption::VALUE_OPTIONAL, "The drupal web root. Defaults to web.", './web')
      ->addOption('ip', null, InputOption::VALUE_OPTIONAL, "IP Address of the new box. Defaults to 192.168.50.2", "192.168.50.2")
      ->addOption('boxname', null, InputOption::VALUE_OPTIONAL, "Box name. Defaults to precise64.", 'precise64')
      ->addOption('boxurl', null, InputOption::VALUE_OPTIONAL, "Box URL. Defaults to http://files.vagrantup.com/precise64.box", 'http://files.vagrantup.com/precise64.box');


  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Hardcoded modules (for now).
    $modules = array(
      'systools' => 'https://github.com/nodeone/puppet-systools.git',
      'apache' => 'https://github.com/nodeone/puppet-apache',
      'drush' => 'https://github.com/nodeone/puppet-drush.git',
      'mysql' => 'https://github.com/nodeone/puppet-mysql.git',
      'php' => 'https://github.com/nodeone/puppet-php.git',
      'postfix' => 'https://github.com/nodeone/puppet-postfix.git',
    );
    $path = $input->getOption('path');
    chdir($path);
    $dialog = $this->getHelperSet()->get('dialog');
    $twig = $this->getTwig();
    // Create a manifests folder.
    if (!is_dir("manifests")) {
      mkdir("manifests");
    }
    if (!is_dir("manifests/modules")) {
      mkdir("manifests/modules");
    }
    // Get latest version of all modules.
    foreach ($modules as $name => $module) {
      $module_path = "manifests/modules/$name";
      if (is_dir($module_path)) {
        exec("rm -rf {$module_path}");
      }
      exec("git clone $module $module_path");
      // Remove the git repository to avoid conflicts.
      exec("rm -rf $module_path/.git");
    }
    $variables = array();
    foreach ($this->variableOptions as $option) {
      $variables[$option] = $input->getOption($option);
    }
    // Generate vagrantfile and manifest.
    file_put_contents('Vagrantfile', $twig->render("vagrant/Vagrantfile", $variables));
    file_put_contents('manifests/manifest.pp', $twig->render("vagrant/manifest.pp", $variables));
  }

  protected function getTwig()
  {
    $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../../templates');
    $twig = new \Twig_Environment($loader, array());
    return $twig;
  }
}
