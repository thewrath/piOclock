<?php declare(strict_types=1);

use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\Deferred;
use Amp\Loop;

use DI\Container;

/**
 * HelloFunctionnality - demo functionnality 
 * 
 **/
class Hello extends Functionnality
{

    private $router;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->router = $this->container->get('router');
    }

    public function run()
    {
        $this->router->addRoute('GET', '/hello-func', new CallableRequestHandler(function () {
            return new Response(Status::OK, ['content-type' => 'text/plain'], 'Hello, from hello functionnality!');
        }));
        $promise = $this->asyncMultiply(6, 7)->onResolve(function (Throwable $error = null, $result = null) {
            if ($error) {
                printf(
                    "Something went wrong:\n%s\n",
                    $error->getMessage()
                );
            } else {
                printf(
                    "Hurray! Our result is:\n%s\n",
                    print_r($result, true)
                );
            }
        });
    }

    private function asyncMultiply($x, $y)
    {
        // Create a new promisor
        $deferred = new Deferred;
        
        // Resolve the async result one second from now
        Loop::delay($msDelay = 10000, function () use ($deferred, $x, $y) {
            $deferred->resolve($x * $y);
        });
        
        return $deferred->promise();
    }
}