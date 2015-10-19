<?php
namespace SseClient;

use GuzzleHttp;

class Client
{
    /** @var  GuzzleHttp\Client */
    private $client;

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => 'https://popping-heat-3439.firebaseio.com']);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        $response = $this->client->request('GET', '/items.json', [
            'stream' => true,
            'headers' => ['Accept' => 'text/event-stream']
        ]);

        $buffer = '';
        $body = $response->getBody();
        while (!$body->eof()) {
            echo $body->read(1024);
//            while (!Event::completed($buffer) && !$body->eof()) {
//                $buffer .= $body->read(1);
//            }
        }
    }
}