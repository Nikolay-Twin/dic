<?php

namespace Abcsoft\DIC;
 
use Abcsoft\DIC\Exceptions\ContainerException;

/** 
 * DI контейнер
 * 
 * NOTE: Requires PHP version 7.0 or later   
 * @author phpforum.su
 * @copyright © 2021
 * @license http://www.wtfpl.net/ 
 */   
class Mapper
{
    use CallableTrait;
    
    protected $container = [];
    protected $localServices = [];
    protected $globalServices = [];
    protected $callables = [];
    protected $dependences = [];    
    protected $ext = [];
    
    public function __construct($container)
    { 
        $this->container = $container;
    }

    /**
    * {static $i = 1; if($i == 2) dbg($map); $i++;}
    * @param array $map
    * @param boll $shared
    *
    * @return void
    */    
    public function setServices($arrayMap, $shared = false)
    {
        $default = $this->container->getDefaultName();
        foreach($arrayMap as $serviceId => $source) {
         
            if(is_array($source) && $serviceId !== $default){
                continue;
            }
            
            $serviceId = $this->setExtends($serviceId);
            switch(true) {
             
                case !is_string($serviceId) :
                    throw new ContainerException(sprintf(
                            ABC_DIC_INVALID_SERVICENAME, 
                            gettype($serviceId),
                            gettype($serviceId)
                        )
                    );
             
                case $serviceId === $default && !isset($this->dependences[$default]) :
                    $this->setDependences($default, $source);
                    break;
             
                case is_string($source) && false !== ($service = $this->findServiceByName($source)) :
                    $this->copyService($service, $serviceId, $shared);    
                    break;
             
                case false !== ($callable = $this->getCallable($source)) :
                    $this->setService($serviceId, $callable, $shared);
                    break;
                    
                default :
                    throw new ContainerException(sprintf(ABC_DIC_INVALID_DATA, $serviceId, $serviceId)); 
            }
        }
    }
    
    /**
    * 
    * @param array $map
    * @param boll $shared
    *
    * @return void
    */    
    protected function findServiceByName($source)
    {
        switch(true) {
        
            case $this->container->has($source) :
            case isset($this->localServices[$source]) :
            case isset($this->globalServices[$source]) :
                return $source;
                
            default :
                return false; 
        }
    }
    
    /**
    *
    * @param string|array $service
    * @param mix $source
    *
    * @return $this
    */  
    protected function copyService($service, $newService, $shared = false)
    {
        if($shared){
            $this->globalServices[$newService] = $this->globalServices[$service];    
        } else {
            $this->localServices[$newService] = $this->localServices[$service];
        } 
        
        if(isset($this->dependences[$service])){
            $this->dependences[$newService] = $this->dependences[$service];
        }
        
        if(isset($this->callables[$service])){
        $this->callables[$newService] = $this->callables[$service];
        }
    }    
    
    /**
    * @param string $serviceId
    * @param callable $callable
    * @param bool $shared
    *
    * @return callable
    */ 
    protected function setService($serviceId, $callable, $shared = false)
    {            
        if($shared){
            $this->globalServices[$serviceId] = $callable;    
        } else {
            $this->localServices[$serviceId] = $callable; 
        }    
    } 
    
    /**
    * 
    * @param string $serviceId
    *
    * @return string
    */    
    public function setExtends($serviceId)
    {
        $parts = preg_split("/[\s,]+/", $serviceId);
        if(count($parts) == 1){    
            return $parts[0];
        }
        
        if(strtolower($parts[1]) === 'extends'){
            $serviceId = array_shift($parts);
            $this->ext[$serviceId] = array_slice($parts, 1);
            return $serviceId;    
        }
        
        throw new ContainerException(ABC_DIC_INVALID_SERVICE);
    }  
 
    /**
    * @param string|callable|object $source
    *
    * @return callable
    */ 
    protected function getCallable($source)
    {
        switch(true){
            case is_callable($source) :
                $callable = $this->bind($source, $this->container);
                $this->attachFactory($this->container, $source, $callable);
                return $callable; 
            
            case is_string($source) :
                if(class_exists($source)){
                    return $this->createClassCallable($source);
                }
                return false;
                
            case is_object($source) :
                return $this->createDataCallable($source);
                
            default :
                return false;
        }
    }     
    
    /**
    * 
    * @param array $map
    * @param bool $shared
    *
    * @return $this
    */ 
    public function setInjections($map, $shared = false)
    {
        foreach($map as $serviceId => $service){
            if(is_array($service)){
                $this->setDependences($serviceId, $service, $shared);
            }
        }
        return $this;
    }
    
    /**
    * 
    * @param array $map
    * @param bool $shared
    *
    * @return $this
    */ 
    public function setDependences($serviceId, $dependences, $shared = false)
    {
        foreach($dependences as $id => $dependence){
            if(is_numeric($id) && class_exists($dependence)){
                $serviceId = $this->setExtends($serviceId);
                $this->setService($serviceId, $this->createClassCallable($dependence), $shared);
                continue;    
            } elseif(is_numeric($id)){
                $serviceId = $this->setExtends($serviceId);
                $this->setService($serviceId, $dependence, $shared);
            }
            
            if(is_string($id)) { 
                if(is_callable($dependence)){
                    $dependance = $this->bind($dependence, $this->container);
                    $this->callables[$serviceId][$id] = $dependence;
                    $this->dependences[$serviceId][$id] = $dependence;
                } elseif(false === $dependence) {
                    $this->dependences[$serviceId][$id] = false;
                } else {
                    $this->dependences[$serviceId][$id] = $dependence;
                }
            }  
        }
        
        return $this;
    } 

    /**
    *
    * @return array
    */ 
    public function getLocalServices()
    {
        return $this->localServices;
    }

    /**
    *
    * @return array
    */ 
    public function getGlobalServices()
    {
        return $this->globalServices;
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
    * @return array
    */ 
    public function getDependences()
    {
        return $this->dependences;
    }
    
    /**
    *
    * @return array
    */ 
    public function getExtends()
    {
        return $this->ext;
    }
}
