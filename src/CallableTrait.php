<?php

namespace Abcsoft\DIC;


/**  
 * DI контейнер
 * 
 * NOTE: Requires PHP version 7.0 or later   
 * @author phpforum.su
 * @copyright © 2021
 * @license http://www.wtfpl.net/ 
 */   
trait CallableTrait 
{
    /**
    *
    * @param string $serviceId
    * @param string|callable $source
    * @param bool $shared
    *
    * @return void
    */      
    public function bind($callable, $newthis)
    {
        return \Closure::bind($callable, $newthis);
    } 

    /**
    *
    * @param obj $space
    * @param callable $source
    * @param callable $callable
    *
    * @return void
    */ 
    protected function attachFactory($space, callable $source, callable $callable)
    {
        if ($space->repository->factories->contains($source)){
            $space->repository->factories->offsetUnset($source);
            $space->repository->factories->attach($callable);
        }
    }       
 
    /**
    *
    * @param string $class
    *
    * @return callable
    */ 
    protected function createClassCallable($class)
    {
        $callable = function() use ($class) {
            return new $class;
        };
        $container = $this->container ?? $this;
        $this->bind($callable, $container);
        return $callable;
    } 

    /**
    *
    * @param string $class
    *
    * @return callable
    */ 
    protected function createDataCallable($data)
    {
        return function() use ($data) {
            return $data;
        };
    }
    
    /**
    *
    * @param string $class
    *
    * @return callable
    */ 
    protected function createServiceLocator($locatorId, array $services, $container)
    {
        $locator = function () use ($locatorId, $services, $container) {  
            return new class($locatorId, $services, $container) implements \Abcsoft\DIC\Interfaces\LocatorInterface
            {
                private $locatorServices = [];
                private $locatorId; 
                private $container;
                
                public function __construct($locatorId, $services, $container)
                {
                    $this->locatorId = $locatorId; 
                    $this->locatorServices = $services;
                    unset($this->locatorServices[$container->getDefaultName()]);
                    $this->container = $container;
                }
                
                public function get($implementation, $serviceId = null)
                { 
                    return $this->instaceOf($serviceId, $implementation);
                }
                
                public function getNew($implementation, $serviceId = null)
                {
                    return $this->instaceOf($serviceId, $implementation, true);
                }
             
                public function has($serviceId)
                {
                    return isset($this->locatorServices[$serviceId]);
                }
                
                protected function instaceOf($serviceId, $implementation, $new = false )
                {
                    $id = $serviceId ?? $implementation;
                    $this->checkHas($id);
                    $service = $new ? $this->container->getNew($id) : $this->container->get($id);
                 
                    if(!empty($serviceId) && !$service instanceof $implementation){
                        throw new \Abcsoft\DIC\Exceptions\ContainerException(
                        sprintf(ABC_DIC_INVALID_INSTANCE, $serviceId, $implementation, $serviceId, $implementation));
                    } 
                    
                    return $service;
                }
                
                public function checkHas($serviceId)
                {
                    if(!in_array($serviceId, $this->locatorServices)){
                        throw new \Abcsoft\DIC\Exceptions\NotFoundException(
                        sprintf(ABC_DIC_NOT_FOUND_IN_LOCATOR, $serviceId, $this->locatorId, $serviceId, $this->locatorId));
                    }
                } 
            };
        };
        
         return $locator;
    } 
} 
