<?php
namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for creating projects (also called platforms).
 */
class VagrantifyCommand extends Command
{
  protected function configure()
  {
    $this->setName('vagrantify')
      ->setDescription('Add a vagrant configuration capable of running Drupal sites.')
      ->addOption('path', NULL, InputOption::VALUE_OPTIONAL, "The path to the project. The current working directory will be used if this isn't specified.", ".");
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
      if (is_dir($module_path) && is_dir("{$module_path}/.git")) {
        exec("git --git-dir={$module_path} pull");
      }
      else {
        exec("git clone $module $module_path");
      }
    }
    // Generate vagrantfile and manifest.
    file_put_contents('Vagrantfile', $twig->render("vagrant/Vagrantfile"));
    file_put_contents('manifests/manifest.pp', $twig->render("vagrant/manifest.pp"));
  }

  protected function getTwig()
  {
    $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates');
    $twig = new \Twig_Environment($loader, array());
    return $twig;
  }
}
