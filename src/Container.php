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
class Container implements \Psr\Container\ContainerInterface
{ 
    use CallableTrait;
    
    const DEFAULT_SERVICE = '__default';
  
 /** @var DIC\Storage $storage */   
    public $repository;
    
 /** @var DIC\Resolver $resolver */       
    protected $builder;
    
 /** @var DIC\Resolver $resolver */       
    protected $complector;
    
    protected $checkExtends = false; 
    protected $lazyLoad = false;
    


    public function __construct(array $config = null)
    {
        $language = '\Abcsoft\DIC\Language\\';
        $language .= !empty($config['debug']['language']) ? $config['debug']['language'] : 'En';
        $language::set();
        defined('ABCDIC_DEFAULT') or define('ABCDIC_DEFAULT', self::DEFAULT_SERVICE);
        $this->repository  = new Repository($this);
        $this->builder = new Builder($this);
        $this->complector = new Complector($this);
    }
    
    /**
    * Устанавливает отложенную загрузку
    *
    * @param иboll $switch
    *
    * @return $this
    */  
    public function lazyLoad($switch, array $services = null)    
    { 
        if(!empty($services)){
            $this->repository->setLazyServices($services, $switch);
        } else {
            $this->lazyLoad = (bool)$switch;
        }
    }
    
    /**
    * Устанавливает карты зависимостей
    *
    * @param array $localMap
    * @param array $sharedMap
    *
    * @return $this
    */  
    public function setMaps($localMap = [], $globalMap = [])
    { 
        if(!empty($localMap)){
            $this->complector->set($this->createMapTo($localMap));       
        }
        
        if(!empty($globalMap)){
            $this->complector->set($this->createMapTo($globalMap, true));
        }
        
        return $this;
    }  

     /**
    * Устанавливает дефолтный блок
    *
    * @param array  $dependances
    *
    * @return $this
    */  
    public function addDefault(array $dependences)
    {  
        $this->repository->setDependences(self::DEFAULT_SERVICE, $dependences);
        return $this;
    } 
    
     /**
    * Очищает дефолтный блок
    *
    * @return $this
    */  
    public function clearDefault()
    {
        $this->repository->clearDefault();
        return $this;
    } 
    

     /**
    * Фабричная обертка
    *
    * @param callable $callable
    *
    * @return callable
    */  
    public function factory($source)
    {   
        return $this->repository->factoryWrap($source);
    }
    
    /**
    * Записывает сервис в локальное хранилище
    *
    * @param string|array $service
    * @param mix $source
    *
    * @return $this
    */  
    public function add($service, $source = null)
    {
        if(is_array($service)){
            $this->setMaps($service);
            return $this;          
        }
        $this->checkExtends = __METHOD__;  
        $this->complector->setServiceData($service, $source);
        return $this;
    } 
   
    /**
    * Записывает сервис в глобальное хранилище
    *
    * @param string|array $service
    * @param mix $source
    *
    * @return $this
    */  
    public function addGlobal($service, $source = null)
    {
        if(is_array($service)){
            $this->setMaps([], $service);
            return $this;          
        } 
        $this->checkExtends = __METHOD__;
        return $this->complector->setServiceData($service, $source, true);
    }
 
    /*
    *
    * Устанавливает сервис-локатор
    *
    * @param string $locatorId
    * @param array $services
    *
    * @return $this
    */  
    public function createLocator($locatorId, array $services)
    { 
        if($this->has($locatorId)){
            throw new ContainerException(sprintf(ABC_DIC_LOCATOR_EXISTS, $locatorId, $locatorId));
        }
        
        $locator = $this->createServiceLocator($locatorId, $services, $this);
        $this->add($locatorId, $locator);
        return $this;
    }       
  
    /**
    * Устанавливает тип внедрения через конструктор
    *
    * @param string $services
    *
    * @return $this
    */  
    public function typeConstruct(...$services)
    {
        $this->builder->typeConstruct($services);
        return $this;
    }     
    
    /**
    * Устанавливает тип внедрения через сеттеры
    *
    * @param string $services
    *
    * @return $this
    */  
    public function typeMethodCall(...$services)
    {
        $this->builder->typeMethodCall($services);
        return $this;
    } 
    
    /**
    * Устанавливает тип внедрения через свойства
    *
    * @param string $services
    *
    * @return $this
    */  
    public function typeProperties(...$services)
    {
        $this->builder->typeProperties($services);
        return $this;
    } 
    
    /**
    * Наследование зависимостей
    *
    * @param string $services
    *
    * @return $this
    */  
    public function extendsDependences(...$parents)
    { 
       if(!$this->checkExtends()){
            foreach($parents as $parent){
                if(!$this->has($parent)){
                    throw new NotFoundException(sprintf(ABC_DIC_NOT_FOUND_SERVICE, $parent, $parent));
                }
            }
            $this->complector->setExtends(
                $this->repository->getLastService(), 
                $parents, 
                $this->repository->getDependences(), 
                $this->repository->getCallables()
            );
            $this->checkExtends = false;
            return $this;
        }
        
        throw new ContainerException(sprintf(ABC_DIC_INVALID_CALL_ORDER, __METHOD__, __METHOD__));
    }
    
    /**
    * Записывает в хранилище зависимость
    *
    * @param string $serviceId
    * @param string $dependenceId
    * @param callable $callable
    *
    * @return $this
    */  
    public function addDependences($serviceId, array $dependences)
    { 
        if(!$this->isSynthetic($serviceId)){
            $mapper = $this->getMapper();
            $mapper->setDependences($serviceId, $dependences);
            $this->complector->set($mapper);
            $this->serviceSynthetic[$serviceId] = true;    
            return $this;            
        } 
        
        throw new ContainerException(sprintf(ABC_DIC_SYNTHETIC_SERVICE, $serviceId, $serviceId));
    }     

    /**
    * Проверяет наличие сервиса в хранилище
    *
    * @param string $serviceId
    *
    * @return bool
    */       
    public function has($serviceId)
    {
        return $this->repository->has($serviceId);
    }  
    
    /**
    * Инициализирует и возвращает объект сервиса
    *
    * @param string $serviceId
    *
    * @return object
    */      
    public function get($serviceId)
    { 
        if ($this->repository->issetFrozen($serviceId)) {
            if (!$this->repository->issetGlobalObject($serviceId)) {
                $this->repository->setGlobalObject($serviceId, $this->selectObject($serviceId));
            } 
            return $this->repository->getGlobalObject($serviceId);
        } 
        
        if ($this->has($serviceId)){
            
            if($this->repository->contains($serviceId)) { 
                return $this->createServiceObject($serviceId);
            }
            
            if(!$this->repository->issetLocalObject($serviceId)) {
                $this->repository->setLocalObject($serviceId, $this->selectObject($serviceId));
            }
            
            return $this->repository->getLocalObject($serviceId);
        }
     
        throw new NotFoundException(sprintf(ABC_DIC_NOT_FOUND_SERVICE, $serviceId, $serviceId));
    }

    /**
    * Инициализирует и возвращает новый объект сервиса, даже если он заморожен
    *
    * @param string $serviceId
    *
    * @return object|bool
    */       
    public function getNew($serviceId)
    {
        if ($this->has($serviceId)) {  
            return $this->createServiceObject($serviceId);
        }
        throw new NotFoundException(sprintf(ABC_DIC_NOT_FOUND_SERVICE, $serviceId, $serviceId));
    } 
    
    /**
    * Объявляет сервис синтетическим, запрещенным к внедрению в него зависимостей
    *
    * @param string $serviceId
    *
    * @return void
    */      
    public function serviceSynthetic($serviceId)
    {
        if(!$this->has($serviceId)){
            throw new NotFoundException(sprintf(ABC_DIC_NOT_FOUND_SERVICE, $serviceId, $serviceId));
        }
        $this->serviceSynthetic[$serviceId] = true;
    } 
    
    /**
    * Проверяет, объявлен ли сервис синтетическим
    *
    * @param string $serviceId
    *
    * @return void
    */       
    public function isSynthetic($serviceId)
    {
        return isset($this->serviceSynthetic[$serviceId]);
    }
    
    /**
    * Удаляет объект из хранилища объектов
    *
    * @param string $serviceId
    *
    * @return void
    */       
    public function unsetObject($serviceId)
    {
        unset(self::$objectStorage[$serviceId]);
        unset($this->objects[$serviceId]);
    }
    
    /**
    * Возвращает имя дефолтного блока
    *
    * @param string $serviceId
    *
    * @return void
    */       
    public function getDefaultName()
    {
        return self::DEFAULT_SERVICE;
    }    
    
/////////////////////////////////////////////////////////////////////    
  
    /**
    * 
    * @param string $serviceId
    *
    * @return object
    */      
    protected function selectObject($serviceId)
    { 
        switch(true){
        
            case isset($this->repository->lazyServices[$serviceId]) && !$this->repository->lazyServices[$serviceId] :
                return $this->createServiceObject($serviceId);
          
            case isset($this->repository->lazyServices[$serviceId]) && $this->repository->lazyServices[$serviceId] :
                return $this->createLazyObject($serviceId);
         
            case $this->lazyLoad :
                return $this->createLazyObject($serviceId);
                
            default :
                return $this->createServiceObject($serviceId);
        }
    }
    
    /**
    * 
    * @param string $serviceId
    *
    * @return object
    */      
    protected function createLazyObject($serviceId)
    { 
        return new LazyLoad(
            function () use ($serviceId) {    
                return $this->createServiceObject($serviceId);
            }
        );
    } 
    
    /**
    * 
    * @param string $serviceId
    *
    * @return object
    */      
    public function createServiceObject($serviceId)
    {     
        if($this->repository->issetDependences($serviceId)) {
            return $this->builder->setRepository($this->repository)->run($serviceId);
        } 
      
        if($this->repository->issetFrozen($serviceId)){     
            return $this->repository->getFrozenObject($serviceId);
        }
         
        if($this->has($serviceId)){
            return $this->repository->getServiceObject($serviceId);
        }
    }

    /**
    * @param array $services
    * @param booleaan $shared
    *
    * @return obj Mapper
    */ 
    protected function getMapper()
    {   
        return new Mapper($this);
    }
    
    /**
    * @param array $services
    * @param booleaan $shared
    *
    * @return obj Mapper
    */ 
    protected function createMapTo($services, $shared = false)
    {   
        $mapper = $this->getMapper();
        $mapper->setServices($services, $shared);  
        $mapper->setInjections($services, $shared);
        return $mapper;
    }
    
    /**
    *
    * @return bool
    */ 
    public function checkExtends()
    {  
        $allowed = ['add', 'addGlobal'];
        return in_array($this->checkExtends, $allowed);
    }
}
