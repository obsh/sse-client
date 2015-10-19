<?php
namespace SseClient;

use GuzzleHttp;

class Client
{
    const RETRY_DEFAULT_MS = 3000;

    /** @var  GuzzleHttp\Client */
    private $client;
    /** @var GuzzleHttp\Psr7\Response */
    private $response;

    /** @var string - requesting url * */
    private $url;
    /** @var  string - last received message id */
    private $lastId;
    /** @var  int - reconnection time in milliseconds */
    private $retry = self::RETRY_DEFAULT_MS;

    public function __construct($url)
    {
        $this->url = $url;
        $this->client = new GuzzleHttp\Client();
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

        $this->response = $this->client->request('GET', $this->url, [
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