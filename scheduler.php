<?php

require_once 'vendor/autoload.php';

use models\WebserverModel;
use worker\WebserverMonitor;
use GuzzleHttp\Client;

$model = new WebserverModel();
$httpClient = new Client();

$loop = React\EventLoop\Loop::get();

$loop->addPeriodicTimer(5, function () use ($model, $httpClient) {
    $localhostUrl = 'http://localhost:8000';

    try {
        // Send a HEAD request to the localhost URL to check if it's alive
        $response = $httpClient->head($localhostUrl);

        if ($response->getStatusCode() === 200) {
            // The local server is alive, run the monitor
            $webserverMonitor = new WebserverMonitor($model, $httpClient);
            $webserverMonitor->run();
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
});
$loop->run();
