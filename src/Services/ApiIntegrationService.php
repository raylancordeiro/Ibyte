<?php


namespace App\Services;


use GuzzleHttp\Psr7\Request;

class ApiIntegrationService extends RestIntegrationService
{
    /**
     * @return array
     * @throws ServiceUnavailableException
     */
    public function getEmployees()
    {
            return $this->formatResponse($this->proxy(new Request('GET', $this->getBaseUri())));
    }
}