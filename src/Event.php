<?php
namespace SseClient;


class Event
{
    /** @var string */
    private $data;
    /** @var string */
    private $eventType;
    /** @var string */
    private $id;
    /** @var int */
    private $retry;

    /**
     * @param string $data
     * @param string $eventType
     * @param null $id
     * @param null $retry
     */
    public function __construct($data = '', $eventType = 'message', $id = null, $retry = null)
    {
        $this->data = $data;
        $this->eventType = $eventType;
        $this->id = $id;
        $this->retry = $retry;
    }

    /**
     * @param $raw
     * @return Event
     */
    public static function parse($raw)
    {
        $event = new static();
        $lines = preg_split('/$\R?^/m', $raw);

        foreach ($lines as $line) {
            $matched = preg_match('/(?P<name>[^:]*):?( ?(?P<value>.*))?/', $line, $matches);

            if (!$matched) {
                # Malformed line. Silently discard.
                // TODO: Think on this. Maybe should throw exception
                continue;
            }

            $name = $matches['name'];
            $value = $matches['value'];

            if ($name === '') {
                // ignore comments
                continue;
            }

            switch ($name) {
                case 'event':
                    $event->eventType = $value;
                    break;
                case 'data':
                    $event->data = empty($event->data) ? $value : "$event->data\n$value";
                    break;
                case 'id':
                    $event->id = $value;
                    break;
                case 'retry':
                    $event->retry = (int)$value;
                    break;
                default:
                    // The field is ignored.
                    continue;
            }
        }

        return $event;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getEventType()
    {
        return $this->eventType;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRetry()
    {
        return $this->retry;
    }
}