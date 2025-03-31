#!/usr/local/php8.4/bin/php
<?php
// cron_clean.php

// Charge l'autoloader de Composer pour charger toutes les classes, y compris Dotenv
require __DIR__ . '/vendor/autoload.php';

// Chargement des variables d'environnement si un fichier .env existe
if (file_exists(__DIR__ . '/.env')) {
    (new Symfony\Component\Dotenv\Dotenv())->bootEnv(__DIR__ . '/.env');
}

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

// Configuration de l'environnement (ici "prod", sans debug)
$env = 'prod';
$debug = false;
$kernel = new Kernel($env, $debug);
$kernel->boot();

// Création de l'application console de Symfony
$application = new Application($kernel);
$application->setAutoExit(false);

// Prépare l'input pour exécuter la commande app:clean-old-reservations
$input = new ArrayInput([
    'command' => 'app:clean-old-reservations',
]);

// Exécute la commande
$returnCode = $application->run($input);

// Affiche le résultat
echo "Commande exécutée avec le code retour : " . $returnCode;
