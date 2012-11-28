#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Nosh\Command\CreateProjectCommand;
use Nosh\Command\CreateProfileCommand;
use Nosh\Command\VagrantifyCommand;


$application = new Application("Nodestream Shell", "0.1");
$application->add(new CreateProjectCommand);
$application->add(new VagrantifyCommand);
$application->add(new CreateProfileCommand);
$application->run();
