#PHP SSE Client

(ported from Python SSE client: https://bitbucket.org/btubbs/sseclient/)

This is a PHP client library for iterating over http Server Sent Event (SSE) streams (also known as EventSource, after the name of the Javascript interface inside browsers).

Example usage:

```php
$client = new SseClient\Client('https://eventsource.firebaseio-demo.com/.json');

// returns generator
$events = $client->getEvents();

// blocks until new event arrive
foreach ($events as $event) {
    print_r($event);
}
```
