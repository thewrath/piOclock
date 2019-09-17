<?php declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'HttpServer.php';
require_once 'Functionnalities/Functionnality.php';
require_once 'Functionnalities/HelloFunctionnality.php';

use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Http\Server\Router;

use DI\ContainerBuilder;
use DI\Container;

use Monolog\Logger;

class Kernel {

    private $container;
    private $functionnalities;

    public function __construct()
    {
        $this->functionnalities = array();
    }

    /**
     * boot
     * Used to boot the kernel 
     * @return void
     */
    public function boot()
    {
        //new services container 
        $this->buildContainer();
        //Amp based http webserver with event loop
        $server =  new HttpServer($this->container);
        $this->registerFunctionnality(new HelloFunctionnality($this->container));
        $server->run(function() {
            foreach($this->functionnalities as $functionnality)
            {
                $functionnality->run();
            }
        });
    }

    /**
     * buildContainer
     * Used to build and fill the services container  
     * @return void
     */
    private function buildContainer()
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        
        $this->container = $builder->build();
        //fill container with services
        //router
        $this->container->set('router', new Router());
        //logger
        $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
        $logHandler->setFormatter(new ConsoleFormatter);
        $logger = new Logger('server');
        $logger->pushHandler($logHandler);
        $this->container->set('logger', $logger);
    }

    /**
     * reboot
     * Used to reboot the kernel  
     * @return void
     */
    public function reboot()
    {
        $this->boot();
    }

    public function registerFunctionnality(Functionnality $functionnality)
    {
        array_push($this->functionnalities, $functionnality);
    }
}

$kernel = new Kernel();
$kernel->boot();