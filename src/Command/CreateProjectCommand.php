<?php
namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for creating projects (also called platforms).
 */
class CreateProjectCommand extends Command
{
  var $options = array(
    'profile-name' => 'The name of a profile to create',
    'profile-title' => 'The title of a profile',
    'profile-description' => 'A profile description',
  );
  protected function configure()
  {
    $this->setName('create-project')
      ->setDescription('Create a NodeStream project (platform) with Drupal core and potentially an installation profile.')
      ->addArgument('path', InputArgument::REQUIRED, 'The path to the project');

    foreach ($this->options as $option => $description) {
      $this->addOption($option, null, InputOption::VALUE_NONE, $description);
    }
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $path = $input->getArgument('path');
    if (is_dir($path)) {
      throw new \Exception("The directory already exists.");
    }
    mkdir($path);
    $dialog = $this->getHelperSet()->get('dialog');
    $twig = $this->getTwig();
    $name = basename($path);
    // Fetch Drupal.
    $return_val = 1;
    system('drush dl drupal-7.15 --destination=' . $path, $return_val);
    if ($return_val) {
      throw new \Exception("Could not download Drupal.");
    }
    system("mv {$path}/drupal-7.15 {$path}/web");
    if ($dialog->askConfirmation($output, '<question>Do you want to create an installation profile?</question> ')) {
      $arguments = array(
        'command' => 'create-profile',
      );
      $profile_name = $input->getOption('profile-name');
      if (empty($profile_name)) {
        $profile_name = $dialog->ask($output, '<question>Enter the name of the profile:</question> ');
      }
      $arguments['path'] = $profile_path = $path . '/web/profiles/' . $profile_name;
      $command = $this->getApplication()->find('create-profile');
      $input = new ArrayInput($arguments);
      $returnCode = $command->run($input, $output);
    }
    $variables = array('core_version' => '7.15');
    file_put_contents($path . '/' . 'platform.make', $twig->render('project/platform.make', $variables));
    if ($dialog->askConfirmation($output, '<question>Do you want to build your profile now?</question> ')) {
      $output->writeln("Building installation profile...");
      system("drush make --no-core --contrib-destination={$profile_path} {$profile_path}/{$profile_name}.make");
    }
  }

  protected function getTwig()
  {
    $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates');
    $twig = new \Twig_Environment($loader, array('cache' => '/tmp'));
    return $twig;
  }
}