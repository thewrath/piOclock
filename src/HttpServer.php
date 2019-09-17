<?php declare(strict_types=1);

use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Server;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;

use Amp\Http\Status;
use Amp\Socket;
use Amp\Loop;

use DI\Container;

class HttpServer {

    private $container;
    private $router;
    private $logger;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->router = $container->get('router');
        $this->logger = $container->get('logger');
    }

    /**
     * run
     * Run Amp event loop in order to receive HTTP request from client
     * @param  mixed $callback
     * regiser all amp async functionnality in the callback function
     * it's call inside the loop 
     *
     * @return void
     */
    public function run($callback)
    {

        Loop::run(function () use ($callback) {
            $sockets = [
                Socket\listen("0.0.0.0:1337"),
                Socket\listen("[::]:1337"),
            ];
            
            //call callback provide by the server owner in order to add async call in this loop 
            // yield $callback();
            $callback();

            $server = new Server($sockets, $this->router, $this->logger);
            yield $server->start();

            
            // Stop the server gracefully when SIGINT is received.
            // This is technically optional, but it is best to call Server::stop().
            Loop::onSignal(SIGINT, function (string $watcherId) use ($server) {
                Loop::cancel($watcherId);
                yield $server->stop();
            });
        });
    }
}