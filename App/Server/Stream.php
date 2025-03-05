<?php
namespace App\Server;

use Swim\Harbor\Crate;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server;

class Stream
{
    private static Crate $channels; 
    private static array $channel_list = [123, 124, 125, 143, 234]; 

    public static function create(): Crate
    {
        self::make();
        foreach (self::$channel_list as $channel) {
            self::$channels->store($channel, new Channel());
        }
        return self::$channels;
    }

    private static function make() 
    {
        self::$channels = Crate::new();
        self::$channels->catch(function (Server $server, Frame $frame, string $event) {
            $server->push($frame->fd, json_encode([
                'message' => 'Channel did not exist!'
            ]));
        });
    }
}