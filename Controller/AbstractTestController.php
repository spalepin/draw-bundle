<?php
/**
 * Created by PhpStorm.
 * User: Martin
 * Date: 14-06-22
 * Time: 10:41
 */

namespace Nucleus\Bundle\RestBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AbstractTestController extends WebTestCase
{
    /**
     * @param $method
     * @param $uri
     * @param $body
     * @param bool $assertJsonResponse
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    protected function jsonCall($client, $method,$uri,$body = null, $assertJsonResponse = true, $statusCode = 200)
    {
        if(!is_null($body)) {
            $body = json_encode($body);
        }

        $client->request(
            $method,
            $uri,
            array(),
            array(),
            array(
                'HTTP_ACCEPT'=>'application/json',
                'CONTENT_TYPE'=>'application/json'
            ),
            $body
        );

        if(!is_null($statusCode)) {
            $this->assertEquals(
                $statusCode, $client->getResponse()->getStatusCode(),
                $client->getResponse()->getContent()
            );
        }

        if($assertJsonResponse) {
            $this->assertJsonResponse($client->getResponse());
        }

        return $client;
    }

    protected function assertJsonResponse(Response $response)
    {
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }
} 