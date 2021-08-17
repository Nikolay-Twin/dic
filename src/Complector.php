<?php

namespace Abcsoft\DIC;

use Abcsoft\DIC\{
        ContainerInterface,
        CallableTrait,
        Repository,
        Mapper,
        Expander,
        Builder
    };

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
class Complector 
{ 
    use CallableTrait;
    
    protected $container;    
    protected $repository;
    protected $defaultName;    
   
    public function __construct(Container $container)
    {
        $this->container   = $container;
        $this->repository  = $container->repository;
        $this->defaultName = $container->getDefaultName();
    }
    /**
    * 
    * @param string $service
    * @param callable $callable
    * @param boolean $shared
    *
    * @return void
    */ 
    public function set($source, callable $callable = null)
    {
        switch(true){
            case is_string($source) :
                if($this->defaultName === $source){
                    throw new ContainerException(sprintf(
                            ABC_DIC_RESERVED_WORD,
                            $this->defaultName,
                            $this->defaultName
                        )
                    );
                } 
                $this->repository->setCallable($source, $callable);        
                break;
         
            case is_object($source) && $source instanceof Mapper :
                $this->setFromMap($source);
                break;
            
            case is_object($source) && $source instanceof Expander :
                $this->setFromExpander($source);
                break;
         
            default :
                throw new ContainerException(ABC_DIC_INVALID_SERVICE);        
        }    
    }
    /**
    *
    * @param string|array $service
    * @param mix $source
    *
    * @return $this
    */  
    public function setServiceData($serviceId, $source, $shared = false)
    {
        if(is_string($serviceId) && $this->repository->has($serviceId)){
            throw new ContainerException(sprintf(ABC_DIC_ALREADY_SERVICE, $serviceId, $serviceId));
        }
        if(!is_string($serviceId)){
            throw new ContainerException(ABC_DIC_INVALID_SERVICENAME);
        }
     
        switch(true){
            case is_callable($source) :
                $callable = $this->bind($source, $this->container);
                $this->attachFactory($this->container, $source, $callable);
                break;
                
            case is_string($source) && class_exists($source) :
                $callable = $this->createClassCallable($source); 
                break;
                
            case is_string($source) && $this->repository->has($source):
                $callable = $this->repository->copyService($source, $serviceId, $shared);
                if($shared){
                    self::$objectStorage[$newService] = self::$objectStorage[$service];
                    self::$serviceFrozen[$newService] = self::$serviceFrozen[$service];
                }
                return $this;
                
            default :
                $callable = $this->createDataCallable($source);
        }
        
        $this->setService($serviceId, $callable, $shared);  
    }   
    
     /**
    *
    * @param string $serviceId
    * @param string|callable $source
    * @param bool $shared
    *
    * @return void
    */      
    public function setService($serviceId, $source, $shared = false)
    {  
        if(!$this->repository->has($serviceId)){
            $callable = (is_string($source) && class_exists($source)) ? $this->createClassCallable($source) : $source;
            if(is_callable($callable)){
                $this->repository->setService($serviceId, $callable, $shared);  
                if($this->repository->issetDependences($this->defaultName)) {
                    $this->setExtends(
                        $serviceId, 
                        [$this->defaultName], 
                        $this->repository->getDependences(), 
                        $this->repository->getCallables()
                    );
                }        
                return;             
            }
            return; 
        }
        throw new ContainerException(sprintf(ABC_DIC_ALREADY_SERVICE, $serviceId, $serviceId));
    } 
    
    /**
    * 
    * @param string $service
    * @param callable $callable
    * @param boolean $shared
    *
    * @return void
    */ 
    protected function setFromMap($map)
    {
        $dependences = $map->getDependences();
        $callables   = $map->getCallables();
        $this->setLocalServicesFromMap($map, $dependences);
        $this->setGlobalServicesFromMap($map, $dependences);
        $this->setExtendsFromMap($map, $dependences, $callables);
        $this->setDependencesFromMap($dependences);
        $this->setCallablesFromMap($callables);
        $this->repository->clearDefault();
    }
    
    /**
    * 
    * @param string $service
    * @param callable $callable
    * @param boolean $shared
    *
    * @return void
    */ 
    protected function setFromExpander($expander)
    {
        foreach($expander->getDependences() as $serviceId => $dependances) {
            $this->repository->setDependences($serviceId, $dependances);                 
        }
        
        foreach($expander->getCallables() as $serviceId => $callables) {
            $this->repository->setCallables($serviceId, $callables);
        }
    }
    
    /**
    *
    * @param array $service
    * @param array $dependences
    *
    * @return $this
    */  
    protected function setDependencesFromMap($dependences)
    {
        foreach($dependences as $serviceId => $depend) {
            $this->repository->setDependences($serviceId, $depend);             
        }
    }  
    
    /**
    *
    * @param array $service
    * @param array $dependences
    *
    * @return $this
    */  
    protected function setExtendsFromMap($map, $dependences, $callables)
    {
        foreach($map->getExtends() as $serviceId => $parents) {
            $this->setExtends($serviceId, $parents, $dependences, $callables);   
        } 
    }  
 
    /**
    *
    * @param array $service
    * @param array $dependences
    *
    * @return $this
    */  
    protected function setLocalServicesFromMap($map, $dependences)
    {
        foreach($map->getLocalServices() as $serviceId => $callable) {
            if($this->repository->has($serviceId)){
                throw new ContainerException(sprintf(ABC_DIC_ALREADY_SERVICE, $serviceId, $serviceId));
            }
         
            if(isset($dependences[$this->defaultName])){
                $this->repository->setDependences($serviceId, $dependences[$this->defaultName]);
            }
            $this->repository->setService($serviceId, $callable, false);
        }
    } 
    
    /**
    *
    * @param array $service
    * @param array $dependences
    *
    * @return $this
    */  
    protected function setGlobalServicesFromMap($map, $dependences)
    {
        foreach($map->getGlobalServices() as $serviceId => $callable) {
            if($this->repository->has($serviceId)){
                throw new ContainerException(sprintf(ABC_DIC_ALREADY_SERVICE, $serviceId, $serviceId));
            }
         
            if(isset($dependences[$this->defaultName])){
                $this->repository->setDependances($serviceId, $dependences[$this->defaultName]);
            }
            
            $this->repository->setService($serviceId, $callable, true); 
        }     
    } 

    
    /**
    *
    * @param array $service
    * @param array $dependences
    *
    * @return $this
    */  
    protected function setCallablesFromMap($mapCallables)
    {
        foreach($mapCallables as $serviceId => $callables) {
            $this->repository->setCallables($serviceId, $callables);  
        } 
    }  
    

    /**
    *
    * @param string $service
    * @param array $parents
    *
    * @return $this
    */  
    public function setExtends($serviceId, $parents, $dependences, $callables)
    {
        $expander = new Expander($this);
        $expander->setService($serviceId);
        $expander->setParents($parents);
        $expander->setDependences($dependences);
        $expander->setCallables($callables);
        $this->set($expander);
    }    
}
