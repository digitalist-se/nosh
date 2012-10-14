<?php
namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Util\DrupalReleaseFetcher;

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
    $this->addOption('api', null, InputOption::VALUE_OPTIONAL, 'API version. Defaults to 7.x', '7.x');
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
    $returnVal = 1;
    $output->writeln("Fetching Drupal");
    $fetcher = new DrupalReleaseFetcher();
    $api = $input->getOption('api');
    $release = $fetcher->getReleaseInfo('drupal', $api)->currentRelease();
    $drupalIdentifier = "drupal-{$release['version']}";
    passthru("drush dl $drupalIdentifier --destination={$path}", $returnVal);
    if ($returnVal) {
      throw new \Exception("Could not download Drupal.");
    }
    passthru("mv {$path}/{$drupalIdentifier} {$path}/web");
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
      if ($dialog->askConfirmation($output, '<question>Do you want to build your profile now?</question> ')) {
        $output->writeln("Building installation profile...");
        passthru("drush make -y --no-core --contrib-destination={$profile_path} {$profile_path}/{$profile_name}.make");
      }
    }
    $variables = array('core_version' => '7.15');
    file_put_contents($path . '/' . 'platform.make', $twig->render('project/platform.make', $variables));
    file_put_contents($path . '/' . '.gitignore', $twig->render('project/gitignore', $variables));
    file_put_contents($path . '/' . 'build', $twig->render('project/build', $variables));
    exec('chmod +x ' . $path . '/' . 'build');
    if ($dialog->askConfirmation($output, 'Do you want to use vagrant for this project?')) {
      $command = $this->getApplication()->find('vagrantify');
      $arguments = array(
        'command' => 'create-profile',
        '--path' => $path,
      );
      $input = new ArrayInput($arguments);
      $returnCode = $command->run($input, $output);
    }
  }

  protected function getTwig()
  {
    $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates');
    $twig = new \Twig_Environment($loader, array('cache' => '/tmp'));
    return $twig;
  }
}