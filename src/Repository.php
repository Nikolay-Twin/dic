<?php

namespace Abcsoft\DIC;

use Abcsoft\DIC\Exceptions\{
        ContainerException,
        NotFoundException
    };
 
/** 
 * DI контейнер
 * 
 * NOTE: Requires PHP version 7.0 or later   
 * @author phpforum.su
 * @copyright © 2021
 * @license http://www.wtfpl.net/ 
 */   
class Repository
{ 
    use CallableTrait;

    public $factories;
    public $lazyServices = [];     

    protected $container;
    protected $defaultName;    

    protected $objects   = [];    
    protected $services  = [];
    protected $callables = [];
    protected $locators  = [];    
    protected $dependences = [];
    
    protected static $serviceFrozen = [];     
    protected static $globalObjects = [];     


    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->factories = new \SplObjectStorage();
        $this->defaultName = $container->getDefaultName();
    }
    
    /**
    *
    * @param string $serviceId
    *
    * @return void
    */  
    public function has($serviceId)    
    { 
        return isset($this->services[$serviceId]);
    }
 
        
    /**
    *
    * @param string $serviceId
    * @param object $object
    *
    * @return voidobject 
    */  
    public function setLocalObject($serviceId, $object)    
    {
        $this->objects[$serviceId] = $object;
    } 
    
    /**
    *
    * @param string $serviceId
    * @param callable $callable
    *
    * @return void
    */  
    public function setGlobalObject($serviceId, object $object)    
    { 
        self::$globalObjects[$serviceId] = $object;        
    }     
    
    /**
    *
    * @param string $serviceId
    * @param callable $callable
    *
    * @return void
    */  
    public function setFrozen($serviceId, callable $callable)    
    { 
        self::$serviceFrozen[$serviceId] = $callable;        
    }
    
    /**
    *
    * @param string $serviceId
    * @param callable $callable
    *
    * @return void
    */  
    public function setCallables($serviceId, array $callables)    
    { 
        foreach($callables as $id => $callable){
            $this->dependences[$serviceId][$id] = $callable;        
        }
    } 
    
    /**
    *
    * @param string $locatorId
    * @param array $services
    *
    * @return void
    */  
    public function setLocators($locatorId, array $services)    
    { 
        $this->locators[$locatorId] = $services;
    } 
    
    /**
    *
    * @param string $serviceId
    * @param array $dependences
    *
    * @return void
    */  
    public function setDependences($serviceId, array $dependences)    
    { 
        foreach($dependences as $id => $callable){
            $this->dependences[$serviceId][$id] = $callable;        
        }
    }

    /**
    *
    * @param array $services
    * @param bool $switch
    *
    * @return void
    */  
    public function setLazyServices(array $services, $switch)    
    { 
        foreach($services as $service){
            $this->lazyServices[$service] = (bool)$switch;
        }
    }
    
    /**
    *
    * @param string $serviceId
    *
    * @return void
    */  
    public function issetLocalObject($serviceId)    
    { 
        return isset($this->objects[$serviceId]);
    } 
    
    /**
    *
    * @param string $serviceId
    *
    * @return void
    */  
    public function issetGlobalObject($serviceId)    
    { 
        return isset(self::$globalObjects[$serviceId]);
    }     

    /**
    *
    * @param string $serviceId
    *
    * @return void
    */  
    public function issetFrozen($serviceId)    
    { 
        return isset(self::$serviceFrozen[$serviceId]);
    }         
 
    /**
    *
    * @param string $serviceId
    * @param string $dependenceId
    *
    * @return bool
    */  
    public function issetCallable($serviceId, $dependenceId)    
    { 
        return isset($this->callables[$serviceId][$dependenceId]);
    } 
    
    /**
    *
    * @param string $serviceId
    *
    * @return void
    */  
    public function issetDependences($serviceId)    
    { 
        return isset($this->dependences[$serviceId]);
    }         
    
    /**
    *
    * @param string $serviceId
    *
    * @return void
    */  
    public function getLocalObject($serviceId)    
    { 
        return $this->objects[$serviceId];
    } 
    
    /**
    *
    * @param string $serviceId
    *
    * @return void
    */  
    public function getGlobalObject($serviceId)    
    { 
        return self::$globalObjects[$serviceId];
    } 
    
    /**
    *
    * @return string
    */       
    public function getLastService()
    {
        $services = array_keys($this->services);
        return array_pop($services);
    }
    
    /**
    *
    * @param string $serviceId
    *
    * @return array
    */  
    public function getDependence($serviceId)   
    { 
        return $this->dependences[$serviceId];
    } 
    
    /**
    *
    * @return array
    */  
    public function getDependences()   
    { 
        return $this->dependences;
    } 
    
    /**
    *
    * @param string $serviceId
    * @param string $dependenceId
    *
    * @return callable
    */  
    public function getCallable($serviceId, $dependenceId)   
    { 
        return $this->callables[$serviceId][$dependenceId];
    } 
    
    /**
    *
    * @return array
    */  
    public function getCallables()   
    { 
        return $this->callables;
    } 
    
    /**
    *
    * @param string $serviceId
    *
    * @return object
    */  
    public function getServiceObject($serviceId)    
    { 
        return $this->services[$serviceId]->__invoke();        
    }
    
    /**
    *
    * @param string $serviceId
    *
    * @return object
    */  
    public function getFrozenObject($serviceId)    
    { 
        return self::$serviceFrozen[$serviceId]->__invoke();        
    } 

    /**
    *
    * @param mix $source
    *
    * @return mix
    */  
    public function factoryWrap($source)    
    { 
        if (is_string($source) && class_exists($source)){
            $source = $this->createClassCallable($source);
        } elseif(!is_callable($source)){
            $source = $this->createDataCallable($source);
        }
        $this->factories->attach($source);
        return $source;
    }
    
    /**
    *
    * @return void
    */  
    public function clearDefault()
    {
        unset($this->dependences[$this->defaultName]);
        unset($this->serviceSynthetic[$this->defaultName]);
    }     
    /**
    *
    * @param string $serviceId
    *
    * @return bool
    */  
    public function contains($serviceId)    
    { 
        return $this->factories->contains($this->services[$serviceId]);
    } 

    /**
    *
    * @param string $serviceId
    * @param callable $callable
    * @param bool $shared
    *
    * @return void
    */  
    public function setService($serviceId, callable $callable, $shared = false)    
    { 
        $this->services[$serviceId] = $callable;
      
        if($shared){
            self::$serviceFrozen[$serviceId] = $callable;
        }
    } 
    
     /**
    *
    * @param string $serviceId
    * @param mix $dependences
    *
    * @return array
    */      
    public function getProperties($serviceId, $dependences)
    {
        $properties = [];
        foreach($dependences as $dependenceId => $value){
         
            switch(true){
             
                case $this->issetCallable($serviceId, $dependenceId) :
                    $callable = $this->getCallable($serviceId, $dependenceId);    
                    $properties[$dependenceId] = $this->bind($callable, $this)->__invoke(); 
                    break;
             
                case is_callable($value) : 
                    $properties[$dependenceId] = $this->bind($value, $this)->__invoke();            
                    break;
             
                case is_object($value) :
                    $properties[$dependenceId] = $value;
                    break;
                    
                case $this->findLocal($dependenceId) :
                    $properties[$dependenceId] = $this->getLocalObject($dependenceId);
                    break;
                 
                case $this->findFrozen($dependenceId) && $this->checkFrozen($dependenceId):
                    $properties[$dependenceId] = $this->getGlobalObject($dependenceId);
                    break;
                 
                case $this->findFrozen($dependenceId) :
                    $properties[$dependenceId] = $this->getFrozenObject($dependenceId);
                    break;
             
                case !is_null($dependenceId) && $this->has($dependenceId) :
                    $properties[$dependenceId] = $this->getServiceObject($dependenceId);            
            }
        } 
        
        return $properties;
    } 
    
    /**
    *
    * @param string $serviceId
    * @param string $newService
    * @param bool $shared
    *
    * @return $this
    */  
    public function copyService($serviceId, $newService, $shared = false)
    {
        $this->services[$newService] = $this->services[$serviceId];
        $this->dependences[$newService] = $this->dependences[$serviceId];
        $this->callables[$newService] = $this->callables[$serviceId];
        if($shared){
            self::$objectStorage[$newService] = self::$objectStorage[$serviceId];
            self::$serviceFrozen[$newService] = self::$serviceFrozen[$serviceId];
        }
        return $this;
    }
}
