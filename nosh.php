#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Command\CreateProjectCommand;
use Command\CreateProfileCommand;
use Command\VagrantifyCommand;


$application = new Application();
$application->add(new CreateProjectCommand);
$application->add(new VagrantifyCommand);
$application->add(new CreateProfileCommand);
$application->run();
