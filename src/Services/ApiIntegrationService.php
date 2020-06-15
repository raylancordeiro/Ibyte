<?php


namespace App\Services;


use GuzzleHttp\Psr7\Request;

class ApiIntegrationService extends RestIntegrationService
{
    /**
     * Cabeçalhos necessários para a correta integração
     *
     * @var array
     */
    private $headers = ['X-Requested-With' => 'XMLHttpsRequest'];

    /**
     * @return array
     * @throws ServiceUnavailableException
     */
    public function getEmployees()
    {
            return $this->formatResponse($this->proxy(new Request('GET', $this->getBaseUri())));
    }
}