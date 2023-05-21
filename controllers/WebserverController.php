<?php

namespace controllers;

use models\Webserver;
use models\WebserverModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use views\WebserverView;

class WebserverController
{
    private $model;
    private $view;

    public function __construct()
    {
        $this->model = new WebserverModel();
        $this->view = new WebserverView();
    }

    private function jsonResponse(Response $response, $data, $statusCode): Response
    {
        $response->getBody()->write($this->view->render($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }

    public function createWebserver(Request $request, Response $response): Response
    {
        // Get the request data
        $data = $request->getParsedBody();

        // Validate the required fields (e.g., name and URL)
        if (empty($data['name']) || empty($data['url'])) {
            $responseData = ['error' => 'Name and URL are required'];
            return $this->jsonResponse($response, $responseData, 404);
        }

        // Create a new Webserver object
        $webserver = new Webserver($data['name'], $data['url']);
        $webserver->setName($data['name']);
        $webserver->setUrl($data['url']);

        // Add the webserver to the database
        $this->model->addWebserver($webserver);
        // Retrieve the web server ID after adding it to the database
        $webserverId = $webserver->getId();

        // Prepare the response data
        $responseData = [
            'message' => 'Webserver created successfully',
            'webserver' => [
                'id' => $webserverId,
                'name' => $webserver->getName(),
                'url' => $webserver->getUrl(),
                'status' => $webserver->getStatus()
            ]
        ];

        // Create a new Slim response and send JSON response
        return $this->jsonResponse($response, $responseData, 201);
    }

    public function editWebserver(Request $request, Response $response, $id): Response
    {
        // Retrieve the existing web server details
        $existingWebserver = $this->model->getWebserverById($id);

        if (!$existingWebserver) {
            $responseData = [
                'error' => 'Web server not found'
            ];
            return $this->jsonResponse($response, $responseData, 404);
        }
        // Get the request data
        $data = $request->getParsedBody();

        // Validate the required fields (e.g., field and newValue)
        if (empty($data['field']) || empty($data['newValue'])) {
            $responseData = [
                'error' => 'Field and newValue are required fields'
            ];
            return $this->jsonResponse($response, $responseData, 401);
        }

        // Retrieve the existing web server details
        $field = $data['field'];
        $newValue = $data['newValue'];

        // Check if the field name exists in the fields list of the table
        $fields = ['name', 'url']; // Update with the actual fields list of the table

        if (!in_array($field, $fields)) {
            $responseData = [
                'error' => 'Invalid field'
            ];
            return $this->jsonResponse($response, $responseData, 400);
        }

        // Update the specified field with the new value
        switch ($field) {
            case 'name':
                $existingWebserver->setName($newValue);
                break;
            case 'url':
                $existingWebserver->setUrl($newValue);
                break;
        }

        // Call the editWebserver method in the WebserverModel
        try {
            $this->model->editWebserver($existingWebserver);
        } catch (\Exception $e) {
            $responseData = [
                'error' => $e->getMessage()
            ];
            return $this->jsonResponse($response, $responseData, 500);
        }

        // Prepare the response data
        $responseData = [
            'message' => 'Web server edited successfully',
            'webserver' => [
                'id' => $existingWebserver->getId(),
                'name' => $existingWebserver->getName(),
                'url' => $existingWebserver->getUrl(),
                'status' => $existingWebserver->getStatus()
            ]
        ];

        // Create a new Slim response and send JSON response
        return $this->jsonResponse($response, $responseData, 200);
    }

    public function deleteWebserver(Request $request, Response $response, $id): Response
    {
        // Retrieve the existing web server details
        $existingWebserver = $this->model->getWebserverById($id);

        if (!$existingWebserver) {
            $responseData = [
                'error' => 'Web server not found'
            ];
            return $this->jsonResponse($response, $responseData, 404);
        }

        // Call the deleteWebserver method in the WebserverModel
        try {
            $this->model->deleteWebserver($existingWebserver);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $responseData = ['error' => 'Cannot delete the web server. There are related records in the monitor_requests table.'];
            } else {
                $responseData = [
                    'error' => $e->getMessage()
                ];
            }
            return $this->jsonResponse($response, $responseData, 500);
        }

        // Prepare the response data
        $responseData = [
            'message' => 'Web server deleted successfully',
            'webserver' => [
                'id' => $existingWebserver->getId(),
                'name' => $existingWebserver->getName(),
                'url' => $existingWebserver->getUrl(),
                'status' => $existingWebserver->getStatus()
            ]
        ];

        // Create a new Slim response and send JSON response
        return $this->jsonResponse($response, $responseData, 200);
    }

    public function getAllWebservers(Request $request, Response $response): Response
    {
        try {
            // Get all web servers
            $webservers = $this->model->getAllWebservers();
            // Prepare the response data
            $responseData = [
                'webservers' => []
            ];

            // Add each web server's details to the response data
            foreach ($webservers as $webserver) {
                $webserverData = [
                    'id' => $webserver->getId(),
                    'name' => $webserver->getName(),
                    'url' => $webserver->getUrl(),
                    'status' => $webserver->getStatus()
                ];

                $responseData['webservers'][] = $webserverData;
            }

            // Create a new Slim response and send JSON response
            return $this->jsonResponse($response, $responseData, 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            $responseData = ['error' => $e->getMessage()];
            return $this->jsonResponse($response, $responseData, 500);
        }
    }

    public function getWebserverRequestsHistory(Request $request, Response $response, $id): Response
    {
        // Retrieve the existing web server details
        $webserver = $this->model->getWebserverById($id);

        if (!$webserver) {
            $responseData = ['error' => 'Web server not found'];
            $response->getBody()->write(json_encode($responseData));
            return $response->withStatus(404);
        }

        // Get the requests history for the specific web server
        $requestsHistory = $this->model->getWebserverRequestsHistory($id);

        // Prepare the response data
        $responseData = [
            'webserver' => [
                'id' => $webserver->getId(),
                'name' => $webserver->getName(),
                'url' => $webserver->getUrl(),
                'status' => $webserver->getStatus()
            ],
            'requests' => $requestsHistory
        ];

        // send JSON response
        return $this->jsonResponse($response, $responseData, 200);
    }

    public function getWebserver(Request $request, Response $response, $id): Response
    {
        // Retrieve the existing web server details
        $webserver = $this->model->getWebserverById($id);

        if (!$webserver) {
            $responseData = [
                'error' => 'Web server not found'
            ];
            return $this->jsonResponse($response, $responseData, 404);
        }

        // Get the current health status of the web server
        $healthStatus = $webserver->getStatus();

        // Get the last 10 requests objects for the web server
        $requestsHistory = $this->model->getLast10RequestsForWebserver($id);

        // Prepare the response data
        $responseData = [
            'webserver' => [
                'id' => $webserver->getId(),
                'name' => $webserver->getName(),
                'url' => $webserver->getUrl(),
                'status' => $healthStatus,
                'requests' => $requestsHistory
            ]
        ];

        //send JSON response
        return $this->jsonResponse($response, $responseData, 200);
    }
}
