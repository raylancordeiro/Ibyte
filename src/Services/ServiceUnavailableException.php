<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;

class ServiceUnavailableException extends \Exception
{
    protected $status = Response::HTTP_SERVICE_UNAVAILABLE;

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}