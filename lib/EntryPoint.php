<?php

/*
* This file is part of the Ekino HalClient package.
*
* (c) 2014 Ekino
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Ekino\HalClient;

use Ekino\HalClient\HttpClient\HttpClientInterface;
use Ekino\HalClient\HttpClient\HttpResponse;
use Ekino\HalClient\Resource;

class EntryPoint
{
    protected $url;

    protected $headers;

    protected $client;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @param string              $url
     * @param array               $headers
     * @param HttpClientInterface $client
     */
    public function __construct($url, HttpClientInterface $client, array $headers = array())
    {
        $this->url     = $url;
        $this->client  = $client;
        $this->headers = $headers;
    }

    /**
     * @param HttpResponse        $response
     * @param HttpClientInterface $client
     *
     * @return Resource
     *
     * @throws \RuntimeException
     */
    public static function parse(HttpResponse $response, HttpClientInterface $client)
    {
        if (substr($response->getHeader('Content-Type'), 0, 20) !== 'application/hal+json') {
            throw new \RuntimeException('Invalid content type');
        }

        $data = @json_decode($response->getBody(), true);

        if ($data === null) {
            throw new \RuntimeException('Invalid JSON format');
        }

        return Resource::create($client, $data);
    }

    /**
     * @param string $name
     *
     * @return Resource
     */
    public function get($name = null)
    {
        $this->initialize();

        if ($name) {
            return $this->resource->get($name);
        }

        return $this->resource;
    }

    /**
     * Initialize the resource.
     */
    protected function initialize()
    {
        if ($this->resource) {
            return;
        }

        $this->resource = static::parse($this->client->get($this->url), $this->client);
    }
}
