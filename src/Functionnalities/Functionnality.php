<?php declare(strict_types=1);

use DI\Container;

abstract class Functionnality
{

    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * init
     * Called once services are build and ready to be used
     * @return void
     */
    public function init()
    {
    
    }

    /**
     * run
     * Call inside Amp event loop -> register async call here 
     * @return void
     */
    public function run()
    {

    }
}