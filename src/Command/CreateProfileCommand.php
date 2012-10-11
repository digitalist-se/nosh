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
class CreateProfileCommand extends Command
{
  protected function configure()
  {
        $this->setName('create-profile')
          ->setDescription('Create a NodeStream profile')
          ->addArgument('path', InputArgument::OPTIONAL, 'The path to the project')
          ->addOption('title', null, InputOption::VALUE_NONE, 'The title of the project')
          ->addOption('description', null, InputOption::VALUE_NONE, 'The description of the project');

  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $path = $input->getArgument('path');
    if (is_dir($path)) {
       throw new \Exception("The directory already exists.");
    }
    if (!is_dir($path)) {
      mkdir($path);
    }
    $dialog = $this->getHelperSet()->get('dialog');
    $title = $input->getOption('title');
    $description = $input->getOption('description');
    if (empty($title)) {
      $title = $dialog->ask($output, '<question>Enter the title of the profile:</question> ');
    }
    if (empty($description)) {
      $description = $dialog->ask($output, '<question>Enter the description of the profile</question>');
    }
    $name = basename($path);
    $twig = $this->getTwig();
    $projects = array(
      'ns_core' => array(
        'name' => 'ns_core',
        'type' => 'module',
        'version' => '7.x-2.0-beta5',
        'subdir' => 'contrib',
      ),
    );
    $variables = array('profile' => $name, 'title' => $title, 'description' => $description, 'projects' => $projects);
    file_put_contents($path . '/' . $name . '.profile', $twig->render('profile/profile.profile', $variables));
    file_put_contents($path . '/' . $name . '.install', $twig->render('profile/profile.install', $variables));
    file_put_contents($path . '/' . $name . '.info', $twig->render('profile/profile.info', $variables));
    file_put_contents($path . '/' . $name . '.make', $twig->render('profile/profile.make', $variables));
  }

  protected function getTwig()
  {
    $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates');
    $twig = new \Twig_Environment($loader, array());
    return $twig;
  }
}
