<?php


use SseClient\Event;

class EventTest extends PHPUnit_Framework_TestCase
{
    /**
     * Ignore comment line.
     */
    public function testComment()
    {
        $event = Event::parse(':this is comment');

        $this->assertEmpty($event->getData(), 'Must be no data after parsing comment');
    }

    public function testDefaultEvent()
    {
        $event = Event::parse('data: some data');

        $this->assertEquals('some data', $event->getData());
        $this->assertEquals('message', $event->getEventType());
    }

    public function testMultilineWithComment()
    {
        $event = Event::parse(":this is comment\ndata: Some data\ndata: data second line");

        $this->assertEquals("Some data\ndata second line", $event->getData());
    }

    public function testExtraSpaces()
    {
        $event = Event::parse('data:  spaced data');

        $this->assertEquals(' spaced data', $event->getData());
    }

    public function testNoColon()
    {
        $event = Event::parse('data');

        $this->assertEquals('', $event->getData());

    }

    public function testCompleteExample()
    {
        $event = Event::parse("event: event type\nid: 20\nretry: 200\ndata: hello\ndata: world!");

        $this->assertEquals('event type', $event->getEventType());
        $this->assertEquals('20', $event->getId());
        $this->assertEquals(200, $event->getRetry());
        $this->assertEquals("hello\nworld!", $event->getData());
    }
}
