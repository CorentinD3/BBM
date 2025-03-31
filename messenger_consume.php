#!/usr/local/php8.4/bin/php
<?php
// messenger_consume.php

require __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

// Définissez l'environnement et le mode debug
$env = 'prod';
$debug = false;

// Boot du kernel
$kernel = new Kernel($env, $debug);
$kernel->boot();

// Création de l'application console
$application = new Application($kernel);
$application->setAutoExit(false);

// Préparation de l'input pour lancer la commande de consommation asynchrone
$input = new ArrayInput([
    'command'        => 'messenger:consume',
    'transport'      => 'async',
    '--time-limit'   => 3600,
    '--memory-limit' => '128M',
]);

// Exécute la commande
$returnCode = $application->run($input);

echo "Commande exécutée avec code de retour : " . $returnCode;
