<?php

namespace models;

require_once './config.php';

use mysqli;

class WebserverModel implements WebserverModelInterface
{
    private $database;
    private $conn;

    public function __construct()
    {
        $this->database = new Database(new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD));
        $this->conn = $this->database->getConnection();
        $this->createDatabaseIfNotExists();
        $this->createTables();
    }

    private function createDatabaseIfNotExists()
    {
        $databaseName = DB_NAME;

        // Check if the database exists
        $result = $this->conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$databaseName'");
        if ($result->num_rows === 0) {
            // Create the database if it doesn't exist
            $createDatabaseQuery = "CREATE DATABASE $databaseName";
            $this->conn->query($createDatabaseQuery);
        }
    }

    public function createTables()
    {
        $this->conn->select_db(DB_NAME);
        $createTableSql = "CREATE TABLE IF NOT EXISTS webservers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            url VARCHAR(255),
            status VARCHAR(150) DEFAULT '',
            success_requests INT DEFAULT 0,
            current_requests INT DEFAULT 0,
            unsuccessful_requests INT DEFAULT 0
          )";
        $this->conn->query($createTableSql);

        $createRequestsTableSql = "CREATE TABLE IF NOT EXISTS monitor_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                webserver_id INT,
                status VARCHAR(150),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (webserver_id) REFERENCES webservers(id)
            )";
        $this->conn->query($createRequestsTableSql);
    }

    public function addWebserver(Webserver $webserver)
    {
        $name = $webserver->getName();
        $url = $webserver->getUrl();

        $successRequests = $webserver->getSuccessRequests();
        $currentRequests = $webserver->getCurrentRequests();
        $unsuccessfulRequests = $webserver->getUnsuccessfulRequests();

        $sql = "INSERT INTO webservers (name, url, success_requests, current_requests, unsuccessful_requests) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiii", $name, $url, $successRequests, $currentRequests, $unsuccessfulRequests);
        $stmt->execute();
        // Retrieve the inserted web server ID
        $webserverId = $stmt->insert_id;
        $stmt->close();
        // Set fields on the web server object
        $webserver->setId($webserverId);
        $webserver->setName($name);
        $webserver->setUrl($url);
        $webserver->setSuccessRequests($successRequests);
        $webserver->setCurrentRequests($currentRequests);
        $webserver->setUnsuccessfulRequests($unsuccessfulRequests);
    }

    public function editWebserver(Webserver $webserver)
    {
        $id = $webserver->getId();
        $name = $webserver->getName();
        $url = $webserver->getUrl();
        $status = $webserver->getStatus();
        $successRequests = $webserver->getSuccessRequests();
        $currentRequests = $webserver->getCurrentRequests();
        $unsuccessfulRequests = $webserver->getUnsuccessfulRequests();

        $sql = "UPDATE webservers SET name = ?, url = ?, status = ?, success_requests = ?, current_requests = ?, unsuccessful_requests = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssiiii", $name, $url, $status, $successRequests, $currentRequests, $unsuccessfulRequests, $id);
        $stmt->execute();
        $stmt->close();
    }

    public function deleteWebserver(Webserver $webserver)
    {
        $id = $webserver->getId();
        //Delete related rows in the monitor_requests table
        $sql = "DELETE FROM monitor_requests WHERE webserver_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        // Delete the web server
        $sql = "DELETE FROM webservers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function getAllWebservers()
    {
        $webservers = [];

        $sql = "SELECT * FROM webservers";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $webserver = new Webserver($row["name"], $row["url"]);
                $webserver->setId($row["id"]);
                $webserver->setStatus($row['status']);
                $webserver->setSuccessRequests($row["success_requests"]);
                $webserver->setCurrentRequests($row["current_requests"]);
                $webserver->setUnsuccessfulRequests($row["unsuccessful_requests"]);
                $webservers[] = $webserver;
            }
        }

        return $webservers;
    }

    public function getWebserverById($id)
    {
        // Prepare and execute the query to retrieve the web server by its ID
        $sql = "SELECT * FROM webservers WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a web server was found
        if ($result->num_rows === 1) {
            $webserverData = $result->fetch_assoc();
            $webserver = new Webserver(
                $webserverData['name'],
                $webserverData['url'],
                $webserverData['status'],
                $webserverData['success_requests']
            );
            $webserver->setId($webserverData['id']);
            return $webserver;
        } else {
            return null;
        }
    }

    public function getWebserverRequestsHistory($webserverId)
    {
        $requestsHistory = [];

        $sql = "SELECT * FROM monitor_requests WHERE webserver_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $webserverId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requestData = [
                    'webserver_id' => $row['webserver_id'],
                    'status' => $row['status'],
                    'timestamp' => $row['timestamp']
                ];

                $requestsHistory[] = $requestData;
            }
        }

        return $requestsHistory;
    }

    public function updateWebserverStatus(Webserver $webserver)
    {
        $id = $webserver->getId();
        $status = $webserver->getStatus();

        $sql = "UPDATE webservers SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);

        $stmt->execute();
        $stmt->close();
    }


    public function saveMonitorRequest($webserverId, $success)
    {
        $sql = "INSERT INTO monitor_requests (webserver_id, status) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $webserverId, $success);
        $stmt->execute();
        $stmt->close();
    }

    public function getCurrentWebserverStatus(Webserver $webserver)
    {
        $id = $webserver->getId();
        $sql = "SELECT status FROM monitor_requests WHERE webserver_id = ? order by timestamp desc limit 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        $stmt->execute();
        $stmt->close();
    }

    public function getLast10RequestsForWebserver($webserverId)
    {
        $requests = [];

        $sql = "SELECT * FROM monitor_requests WHERE webserver_id = ? ORDER BY timestamp DESC LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $webserverId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requestData = [
                    'id' => $row['id'],
                    'status' => $row['status'],
                    'timestamp' => $row['timestamp']
                ];

                $requests[] = $requestData;
            }
        }

        return $requests;
    }
}
