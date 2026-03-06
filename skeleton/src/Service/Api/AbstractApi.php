<?php

namespace App\Service\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Response\TraceableResponse;

abstract class AbstractApi
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @throws \Exception
     */
    protected function sendRequest(
        string $url,
        string $method = 'GET',
        array $body = [],
        array $auth = [],
        array $headers = []
    ): TraceableResponse {
        $options = [];

        if (!empty($body)) {
            $options['body'] = $body;
        }

        if (!empty($auth)) {
            $options['auth_basic'] = $auth;
        }

        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        $response = $this->client->request($method, $url, $options);

        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \Exception(
                sprintf('API request failed: status code "%d" for URL "%s"', $statusCode, $url)
            );
        }

        return $response;
    }

    public function getContent(string $url, ?string $method = 'GET', ?array $body = [], ?array $auth = [])
    {
        $request = $this->sendRequest($url, $method, $body, $auth);

        return $request->getContent();
    }

    protected function setOptions(?array $options = [])
    {
        $this->client = $this->client->withOptions($options);
    }
}