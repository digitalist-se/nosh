#!/usr/bin/env php
<?php

require 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use Command\CreateProjectCommand;
use Command\CreateProfileCommand;

$application = new Application();
$application->add(new CreateProjectCommand);
$application->add(new CreateProfileCommand);
$application->run();
