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
        string $event) 
    {
        switch ($event) {
            case 'join':
                $this->addUsers($frame->fd);
                $this->push($server, "welcome $frame->fd!");
                break;
            case 'leave':
                $this->rmUser($frame->fd);
                break;
            case 'chatting':
                $this->push($server, $frame->data);
                break;
        }
    }

    public function __construct() 
    {
        $this->members = [];    
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