<?php declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'HttpServer.php';
require_once 'Functionnalities/Functionnality.php';
require_once 'Functionnalities/Hello.php';
require_once 'Functionnalities/AdminPanel.php';

use Amp\ByteStream\ResourceOutputStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Http\Server\Router;

use DI\ContainerBuilder;
use DI\Container;

use Monolog\Logger;

class Kernel {

    private $container;
    private $registredFunctionnalities;
    private $functionnalities;

    public function __construct()
    {
        //All activated functionnalities
        $this->registredFunctionnalities = [
            Hello::class => [], 
            AdminPanel::class => []
        ];
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
        //build all registred functionnalities 
        $this->buildFunctionnalities();

        $server->run(function() {
            foreach($this->functionnalities as $functionnality)
            {
                $functionnality->run();
            }
        });
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
     * buildFunctionnalities 
     * Used to create object of each class that are registred in the functionnalities array
     * @return void 
     */
    private function buildFunctionnalities()
    {
        //Loop in registred functionnality and call 
        foreach ($this->registredFunctionnalities as $functionnality => $args) {
            $this->buildFunctionnality($functionnality, $args);
        }
    }

    /**
     * buildFunctionnality
     * Used to build a functionnality   
     * @return void
     */
    private function buildFunctionnality(string $functionnalityClass, $args)
    {
        //add container to args array 
        array_unshift($args, $this->container);
        //change to object creation using reflectionClass 
        array_push($this->functionnalities, (new ReflectionClass($functionnalityClass))->newInstanceArgs($args));
    }
}

$kernel = new Kernel();
$kernel->boot();