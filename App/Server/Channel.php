<?php
namespace App\Server;

use Swim\Harbor\Crate;
use Swoole\Websocket\Frame;
use Swoole\Websocket\Server;

class Channel
{
    private array $members;
    private Crate $event;

    public function __invoke(
        Server $server, 
        Frame $frame, 
        object $message) 
    {
        ($this->event->get($message->header ?? ''))(...[$server, $frame]);
    }

    public function __construct() 
    {
        $this->members = [];
        $this->event = Crate::new();
        $this->listen();
    }
    private function listen(): void 
    {
        $this->event
            ->store('join', function (Server $server, Frame $frame) 
            {
                $this->addUsers($frame->fd);
                $this->push($server, "welcome $frame->fd!");
            })

            ->store('chatting', function (Server $server, Frame $frame) 
            {
                $this->push($server, $frame->data);
            })

            ->store('leave', function (Server $server, Frame $frame) 
            {
                $this->rmUser($frame->fd);
            })

            ->catch(function (Server $server, Frame $frame) 
            {
                $server->push($frame->fd, json_encode([
                    'message' => 'Incorrect Header!'
                ]));
            });        
    }

    public function addUsers(int $fd): void
    {
        $this->members[] = $fd;
    }

    public function rmUser(int $fd): void 
    {
        $index = array_search($fd);
        unset($this->members[$index]);
    }

    public function push(Server $server, string $message): void 
    {
        foreach ($this->members as $fd) {
            $server->push($fd, $message);
        }   
    }
}