<?php
namespace SseClient;

use GuzzleHttp;

class Client
{
    /** @var  GuzzleHttp\Client */
    private $client;
    /** @var GuzzleHttp\Psr7\Response */
    private $response;
    /** @var string */
    private $path;

    public function __construct($baseUri, $path = '/')
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => $baseUri]);
        $this->path = $path;
        $this->connect();
    }

    private function connect()
    {
        $this->response = $this->client->request('GET', sprintf('%s.json', $this->path), [
            'stream' => true,
            'headers' => [
                'Accept' => 'text/event-stream',
                'Cache-Control' => 'no-cache'
            ]
        ]);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        $endOfMessage = "/\r\n\r\n|\n\n|\r\r/";

        $buffer = '';
        $body = $this->response->getBody();
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