<?php

namespace models;

interface WebserverModelInterface
{
    public function addWebserver(Webserver $webserver);
    public function editWebserver(Webserver $webserver);
    public function deleteWebserver(Webserver $webserver);
    public function getAllWebservers();
    public function getWebserverById($id);
    public function getWebserverRequestsHistory($webserverId);
    public function updateWebserverStatus(Webserver $webserver);
    public function saveMonitorRequest($webserverId, $success);
    public function getCurrentWebserverStatus(Webserver $webserver);
    public function getLast10RequestsForWebserver($webserverId);
}
