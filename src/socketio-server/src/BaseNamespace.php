<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\SocketIOServer;

use Hyperf\SocketIOServer\Collector\SocketIORouter;
use Hyperf\SocketIOServer\Emitter\Emitter;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\WebSocketServer\Sender;

class BaseNamespace implements NamespaceInterface
{
    use Emitter;

    /**
     * @var array<string, callable[]>
     */
    private $eventHandlers = [];

    public function __construct(Sender $sender, SidProviderInterface $sidProvider)
    {
        /* @var AdapterInterface adapter */
        $this->adapter = make(AdapterInterface::class, ['sender' => $sender, 'nsp' => $this]);
        $this->sidProvider = $sidProvider;
        $this->sender = $sender;
        $this->broadcast = true;
        $this->on('connect', function (Socket $socket) {
            $this->adapter->add($socket->getSid(), $socket->getSid());
        });
        $this->on('disconnect', function (Socket $socket) {
            $this->adapter->del($socket->getSid());
        });
    }

    /**
     * Register socket.io event.
     */
    public function on(string $event, callable $callback)
    {
        $this->eventHandlers[$event][] = $callback;
    }

    /**
     * Retrieves all callbacks for any events.
     * @return array<string, callable[]>
     */
    public function getEventHandlers()
    {
        return $this->eventHandlers;
    }

    /**
     * Returns the current namespace in string form.
     */
    public function getNamespace(): string
    {
        return SocketIORouter::getNamespace(static::class);
    }
}