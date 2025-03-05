<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Server\Event;
use OpenSwoole\Http\Request;
use OpenSwoole\WebSocket\Frame;
use OpenSwoole\WebSocket\Server;

$env = Dotenv::createImmutable(__DIR__ );
$env->load();

$event = Event::emit();

$server = new Server($_ENV['ADDR'], $_ENV['PORT']);

$server->on("Start", function(Server $server)
{
    echo "WebSocket Server started at http://{$_ENV['ADDR']}:{$_ENV['PORT']}\n";
});

$server->on('Message', function(Server $server, Frame $frame) use ($event)
{
    $message = json_decode($frame->data);
    $event->get($message->header ?? '')(...[$server, $frame, $message]);
});

$server->on('Close', function(Server $server, int $fd)
{
    echo "connection close: {$fd}\n";
});

$server->on('Disconnect', function(Server $server, int $fd)
{
    echo "connection disconnect: {$fd}\n";
});

$server->start();
