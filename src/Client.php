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
        $endOfMessage = "/\r\n\r\n|\n\n|\r\r/";

        $response = $this->client->request('GET', '/items.json', [
            'stream' => true,
            'headers' => ['Accept' => 'text/event-stream']
        ]);

        $buffer = '';
        $body = $response->getBody();
        while (!$body->eof()) {
            $buffer .= $body->read(1);
            if (preg_match($endOfMessage, $buffer)) {
                $parts = preg_split($endOfMessage, $buffer, 2);

                $rawMessage = $parts[0];
                $remaining = $parts[1];

                $buffer = $remaining;

                $event = Event::parse($rawMessage);
                yield $event;
            }
        }
    }
}