<?php
require_once __DIR__ . '/../vendor/autoload.php';

// example callback function
function someCallbackFunction($message){
    print_r($message);
}

$client = new SseClient\Client('https://popping-heat-3439.firebaseio.com/items.json');

// returns generator
$messages = $client->getMessages();

// blocks until new message arrive
foreach ($messages as $message) {
    // pass message to callback function
    someCallbackFunction($message);
}