<?php
namespace SseClient;

use GuzzleHttp;

class Client
{
    const RETRY_DEFAULT_MS = 3000;
    const END_OF_MESSAGE = "/\r\n\r\n|\n\n|\r\r/";

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
        $this->client = new GuzzleHttp\Client([
            'headers' => [
                'Accept' => 'text/event-stream',
                'Cache-Control' => 'no-cache'
            ]
        ]);
        $this->connect();
    }

    private function connect()
    {
        $headers = [];
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
        $buffer = '';
        $body = $this->response->getBody();
        while (true) {
            // if server close connection - try to reconnect
            if ($body->eof()) {
                // wait retry period before reconnection
                sleep($this->retry / 1000);
                $this->connect();
                // clear buffer since there is no sense in partial message
                $buffer = '';
            }

            $buffer .= $body->read(1);
            if (preg_match(self::END_OF_MESSAGE, $buffer)) {
                $parts = preg_split(self::END_OF_MESSAGE, $buffer, 2);

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