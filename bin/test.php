<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * example callback function
 * @param SseClient\Event $event
 */
function someCallbackFunction(\SseClient\Event $event){
    print_r($event);
}

// if authentication needed - add to url auth parameter ?auth=CREDENTIAL
// where "CREDENTIAL" can either be your Firebase Secret or an authentication token.
$client = new SseClient\Client('https://popping-heat-3439.firebaseio.com/items.json');

// returns generator
$events = $client->getEvents();

// blocks until new event arrive
foreach ($events as $event) {
    // pass event to callback function
    someCallbackFunction($event);
}