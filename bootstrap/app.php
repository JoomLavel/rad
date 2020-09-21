<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 14.09.2020
 * Time: 18:42
 */

require __DIR__ . '/../src/Command/GenericCommand.php';
require __DIR__ . '/../src/Command/MakeComponent.php';
require __DIR__ . '/../src/Service/ComponentGenerator.php';
require __DIR__ . '/../src/Service/ZipGenerator.php';

use JoomLavel\Rad\Command\MakeComponent;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new MakeComponent($config));

try {
    $application->run();
} catch (Exception $e) {
}