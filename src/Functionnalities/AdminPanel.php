<?php declare(strict_types=1);

use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\Deferred;
use Amp\Loop;

use DI\Container;

/**
 * AdminPanel - simple administration panel
 * 
 * Default panel use to access API configuration functionnalities  
 * 
 **/
class AdminPanel extends Functionnality
{

    private $router;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->router = $this->container->get('router');
    }

    public function run()
    {   
        //Add all needed routes for the admin panel 
        $this->router->addRoute('GET', '/admin', new CallableRequestHandler(function () {
            return new Response(Status::OK, ['content-type' => 'text/plain'], 'Hello, this is the default admin panel!');
        }));
        
    }
    
}