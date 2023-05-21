<?php

namespace models;

class Webserver
{
    private $id;
    private $name;
    private $url;
    private $status;
    private $successRequests;
    private $currentRequests;
    private $unsuccessfulRequests;

    public function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $url;
        $this->status = '';
        $this->successRequests = 0;
        $this->currentRequests = 0;
        $this->unsuccessfulRequests = 0;
    }

    // Getter and setter methods for the properties

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getSuccessRequests()
    {
        return $this->successRequests;
    }

    public function getCurrentRequests()
    {
        return $this->currentRequests;
    }

    public function getUnsuccessfulRequests()
    {
        return $this->unsuccessfulRequests;
    }

    public function incrementCurrentRequests()
    {
        $this->currentRequests++;
    }

    public function incrementSuccessRequests()
    {
        $this->successRequests++;
    }

    public function resetUnsuccessfulRequests()
    {
        $this->unsuccessfulRequests = 0;
    }

    public function incrementUnsuccessfulRequests()
    {
        $this->unsuccessfulRequests++;
    }

    public function setSuccessRequests($successRequests)
    {
        $this->successRequests = $successRequests;
    }

    public function setCurrentRequests($currentRequests)
    {
        $this->currentRequests = $currentRequests;
    }

    public function setUnsuccessfulRequests($unsuccessfulRequests)
    {
        $this->unsuccessfulRequests = $unsuccessfulRequests;
    }

    public function resetSuccessRequests()
    {
        $this->successRequests = 0;
    }
}
