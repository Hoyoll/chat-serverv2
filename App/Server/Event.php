<?php
namespace App\Server;

use App\Server\Stream;
use Swim\Harbor\Crate;
use Swoole\WebSocket\Frame;
use Swoole\Websocket\Server;

class Event
{
    // private Table $connections;
    private Crate $channels;
    private Crate $event; 

    public static function emit(): Crate 
    {
        return (new Event())->event();
    }
    
    
    public function __construct() 
    {
        // $this->connections = Connection::get();
        $this->channels = Stream::create();
        $this->event = Crate::new();
        $this->listen();
    }

    private function listen(): Crate 
    {
        $this->event
        ->store('join', function (Server $server, Frame $frame, object $message) {
            ($this->channels->get($message->channel))(...[$server, $frame, 'join']);
        })

        ->store('chatting', function (Server $server, Frame $frame, object $message) {
            ($this->channels->get($message->channel))(...[$server, $frame, 'chatting']);
        })

        ->store('leave', function (Server $server, Frame $frame, object $message) {
            ($this->channels->get($message->channel))(...[$server, $frame, 'leave']);
        })

        ->store('close', function (Server $server, int $fd) {
            $this->connections->del($fd);
        })

        ->catch(function (Server $server, Frame $frame) {
            $server->push($frame->fd, json_encode([
                'message' => 'Incorrect Header!'
            ]));
        });
        return $this->event;
    }

    private function event(): Crate 
    {
        return $this->event;    
    }
}
