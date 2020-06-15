<?php


namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Response;

class RestIntegrationService
{
    /**
     * URL base da API
     */
    const API_BASE_URI = 'https://5e61af346f5c7900149bc5b3.mockapi.io/desafio03/employer';

    const TIMOUT = 20;

    /**
     * @var int
     */
    private $timeout;
    /**
     * @var Client
     */
    private $client;

    /**
     * @return string
     */
    public function getBaseUri(): string
    {
        return self::API_BASE_URI;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => self::API_BASE_URI,
                'timeout' => self::TIMOUT,
            ]);
        }

        return $this->client;
    }

    /**
     * @param $contents
     * @return mixed|null
     * @throws ServiceUnavailableException
     */
    protected function decode($contents)
    {
        $data = null;
        try {
            if (!empty($contents)) {
                $data = \GuzzleHttp\json_decode($contents, true);
            }
        } catch (\InvalidArgumentException $e) {
            throw new ServiceUnavailableException(base64_encode($e->getMessage()));
        }

        return $data;
    }

    /**
     * @param Request $request
     * @param array $query
     * @param int $timeout
     * @return array
     * @throws ServiceUnavailableException
     */
    protected function proxy(Request $request, array $query = [], int $timeout = 10)
    {
        try {
            /** @var Uri $uri */
            $uri = null;
            $guzzleResponse = $this->getClient()->send($request, [
                'on_stats' => function (TransferStats $stats) use (&$uri) {
                    $uri = $stats->getEffectiveUri();
                },
                'query' => $query,
                'timeout' => $timeout,
            ]);
            $contents = $guzzleResponse->getBody()->getContents();
            $status = $guzzleResponse->getStatusCode();
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $contents = $e->getResponse()->getBody();
                $status = $e->getCode();
            } else {
                throw new ServiceUnavailableException('No answer');
            }
        } catch (GuzzleException $e) {
            throw new ServiceUnavailableException($e);
        } catch (\Exception $e) {
            throw new ServiceUnavailableException($e);
        }

        $data = json_decode((string) $contents, true);
        try {
            $data = \GuzzleHttp\json_decode((string) $contents, true);
        } catch (\InvalidArgumentException $e) {
            if ($status != Response::HTTP_NO_CONTENT) {
                throw new ServiceUnavailableException(
                    'Response syntax is wrong: '.dump((string) $contents)
                    . ' >>> ' . json_last_error_msg()
                );
            }
        }

        return [
            'data' => $data,
            'status' => $status,
        ];
    }

    /**
     * Formata a resposta
     *
     * @param array $response
     * @return array
     * @throws ServiceUnavailableException
     */
    protected function formatResponse(array $response): array
    {
        $defaultErrorMessage = 'Internal error';
        if (!isset($response['status'])) {
            throw new ServiceUnavailableException(__LINE__ . ": $defaultErrorMessage");
        }

        if (intdiv($response['status'], 100) == 2) {
            if (isset($response['data']['data'])) {
                $response['data'] = $response['data']['data'];
            }
        } else {
            $errors = [__LINE__ . ": $defaultErrorMessage"];
            if (is_array($response['data']) && array_key_exists('errors', $response['data'])) {
                $errors = $response['data']['errors'];
            }
            unset($response['data']);
            $response['errors'] = $errors;
        }
        return $response;
    }
}