<?php
require_once 'vendor/autoload.php';

$client = new GuzzleHttp\Client(['base_uri' => 'https://popping-heat-3439.firebaseio.com']);

$response = $client->request('GET', '/items.json', [
    'stream' => true,
    'headers' => ['Accept' => 'text/event-stream']
]);

// Read bytes off of the stream until the end of the stream is reached
$buffer = '';
$body = $response->getBody();

while (!Event::completed($buffer) && !$body->eof()) {
    $buffer .= $body->read(1);
}
