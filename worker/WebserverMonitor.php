<?php

namespace worker;

use GuzzleHttp\Client;
use models\Webserver;
use models\WebserverModel;

class WebserverMonitor
{
    private $model;
    private $httpClient;

    public function __construct(WebserverModel $model, Client $httpClient)
    {
        $this->model = $model;
        $this->httpClient = $httpClient;
    }

    public function run()
    {
        $this->monitorWebservers();
    }

    private function monitorWebservers()
    {
        $webservers = $this->model->getAllWebservers();
        foreach ($webservers as $webserver) {
            $success = $this->checkWebserverStatus($webserver);
            $this->model->saveMonitorRequest($webserver->getId(), $success);
            if ($success) {
                $webserver->incrementCurrentRequests();
                $webserver->incrementSuccessRequests();
                $webserver->resetUnsuccessfulRequests();

                if ($webserver->getCurrentRequests() >= 5) {
                    $webserver->setStatus('Healthy');
                }
            } else {
                $webserver->incrementCurrentRequests();
                $webserver->incrementUnsuccessfulRequests();

                if ($webserver->getUnsuccessfulRequests() >= 3) {
                    $webserver->setStatus('Unhealthy');
                    $webserver->resetSuccessRequests();
                }
            }
            // Update the webserver object in the database
            $this->model->editWebserver($webserver);
        }
    }

    private function checkWebserverStatus(Webserver $webserver)
    {
        $url = $webserver->getUrl();
        try {
            $response = $this->httpClient->get($url);

            // Get the response status code and latency
            $statusCode = $response->getStatusCode();
            $totalTime = (float) $response->getHeaderLine('X-Transfer-Time');

            // Check if the response code is 2xx and latency is less than 60 seconds
            if ($statusCode >= 200 && $statusCode < 300 && $totalTime < 60.0) {
                return true; // Success
            } else {
                return false; // Failure
            }
        } catch (\Exception $e) {
            // Error occurred during the request
            return false; // Failure
        }
    }
}
