<?php
namespace SseClient;

use GuzzleHttp;

class Client
{
    /** @var  GuzzleHttp\Client */
    private $client;
    /** @var GuzzleHttp\Psr7\Response */
    private $response;

    /** @var string - path to listen, must start from root "/" */
    private $path;
    /** @var  string - last received message id */
    private $lastId;
    /** @var  int - reconnection time in milliseconds */
    private $retry = 3000;

    public function __construct($baseUri, $path = '/')
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => $baseUri]);
        $this->path = $path;
        $this->connect();
    }

    private function connect()
    {
        $headers = [
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-cache'
        ];

        if ($this->lastId) {
            $headers['Last-Event-ID'] = $this->lastId;
        }

        $this->response = $this->client->request('GET', sprintf('%s.json', $this->path), [
            'stream' => true,
            'headers' => $headers
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

                // if message contains id set it to last received message id
                if ($event->getId()) {
                    $this->lastId = $event->getId();
                }

                // take into account server request for reconnection delay
                if ($event->getRetry()) {
                    $this->retry = $event->getRetry();
                }

                yield $event;
            }
        }
    }
}