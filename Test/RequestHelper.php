<?php

namespace Draw\DrawBundle\Test;

use Symfony\Bundle\FrameworkBundle\Client;
use PHPUnit_Framework_TestCase;

class RequestHelper
{
    public $testCase;

    public $client;

    public $uri;

    public $method = 'GET';

    public $isJson;

    public $body;

    public $assertions = [
        "statusCode" => null,
        "responseContentType" => null,
        "against" => null,
    ];

    public $contentFilters = [];

    public function __construct(PHPUnit_Framework_TestCase $testCase, Client $client)
    {
        $this->client = $client;
        $this->testCase = $testCase;
        $this->expectingStatusCode(200);
    }

    /**
     * @param PHPUnit_Framework_TestCase $testCase
     * @param Client $client
     * @return static
     */
    public static function factory(PHPUnit_Framework_TestCase $testCase, Client $client)
    {
        return new static($testCase, $client);
    }

    public function get($uri = null)
    {
        return $this->setMethod('GET', $uri);
    }

    public function post($uri = null)
    {
        return $this->setMethod('POST', $uri);
    }

    public function delete($uri = null)
    {
        return $this->setMethod('DELETE', $uri);
    }

    public function put($uri = null)
    {
        return $this->setMethod('PUT', $uri);
    }

    public function on($uri)
    {
        return $this->setUri($uri);
    }

    public function expectContentType($contentType)
    {
        $this->assertions["responseContentType"] = function () use ($contentType) {
            $response = $this->client->getResponse();
            $this->testCase->assertTrue(
                $response->headers->contains('Content-Type', $contentType),
                $response->headers
            );
        };

        return $this;
    }

    public function expectingNoContent()
    {
        $this->assertions["responseContentType"] = function () {
            $response = $this->client->getResponse();
            $this->testCase->assertFalse($response->headers->has('Content-Type'));
        };

        $this->assertions["against"] = function () {
            $response = $this->client->getResponse();
            $this->testCase->assertEmpty($response->getContent());
        };

        return $this;
    }

    public function expectingException($statusCode)
    {
        $this->expectingStatusCode($statusCode);
        $this->contentFilters[] = function ($content) {
            $content = json_decode($content);
            unset($content->detail);

            return json_encode($content);
        };

        return $this;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    public function setMethod($method, $uri = null)
    {
        $this->method = $method;
        if ($uri) {
            $this->setUri($uri);
        }

        return $this;
    }

    public function asJson()
    {
        $this->isJson = true;
        $this->expectContentType('application/json');

        return $this;
    }

    public function withBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function expectingStatusCode($statusCode)
    {
        $this->assertions['statusCode'] = function () use ($statusCode) {
            $this->testCase->assertSame(
                $statusCode,
                $this->client->getResponse()->getStatusCode(),
                $this->client->getResponse()->getContent()
            );
        };

        return $this;
    }

    public function validateAgainstFile($file = null)
    {
        if (is_null($file)) {
            list($class, $method) = $this->getCallingClassAndMethod();
            $class = new \ReflectionClass($class);
            $className = str_replace($class->getNamespaceName() . '\\', '', $class->getName());
            $dir = dirname($class->getFileName()) . '/fixtures/out';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $file = $dir . '/' . $className . '-' . $method . '.json';
        }

        $this->assertions['against'] = function () use ($file) {
            $content = $response = $this->client->getResponse()->getContent();

            foreach ($this->contentFilters as $filter) {
                $content = call_user_func($filter, $content);
            }

            if (!file_exists($file)) {
                file_put_contents($file, $content);
            }

            $this->testCase->assertJsonStringEqualsJsonString(
                file_get_contents($file),
                $content
            );
        };

        return $this;
    }

    public function validateAgainstString($string)
    {
        $this->assertions['against'] = function () use ($string) {
            $content = $response = $this->client->getResponse()->getContent();

            $this->testCase->assertJsonStringEqualsJsonString(
                $string,
                $content
            );
        };

        return $this;
    }

    public function asserting($callBack)
    {
        $this->assertions[] = $callBack;

        return $this;
    }

    public function execute()
    {
        $server = array();

        $body = null;
        if ($this->isJson) {
            $server['HTTP_ACCEPT'] = 'application/json';
            $server['CONTENT_TYPE'] = 'application/json';
            if ($this->body) {
                $body = json_encode($this->body);
            }
        }

        $crawler = $this->client->request($this->method, $this->uri, array(), array(), $server, $body);
        foreach (array_filter($this->assertions) as $callback) {
            call_user_func($callback, $crawler);
        }

        return $crawler;
    }

    public function executeAndDecodeJson()
    {
        $this->execute();

        return json_decode($this->client->getResponse()->getContent(), true);
    }


    private function getCallingClassAndMethod()
    {

        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        $class = $trace[1]['class'];

        // +1 to i cos we have to account for calling this function
        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i])) // is it set?
            {
                if ($class != $trace[$i]['class']) // is it a different class
                {
                    return array($trace[$i]['class'], $trace[$i]['function']);
                }
            }
        }
    }
}