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
class Expander
{
    
    protected $service;
    protected $parents = [];
    protected $dependences = [];
    protected $callables = [];
    /**
    * 
    *
    * @return array
    */ 
    public function setService($service)
    {
        $this->service = $service;
    }
  
    /**
    * 
    *
    * @return array
    */ 
    public function setParents($parents)
    {
        $this->parents = $parents;
    }
    
    /**
    * 
    *
    * @return array
    */ 
    public function setDependences($dependences)
    {
        $this->dependences = $dependences;
    }
    
    /**
    * 
    *
    * @return array
    */ 
    public function setCallables($callables)
    {
        $this->callables = $callables;
    }
    
    /**
    * 
    * @return array
    */ 
    public function getDependences()
    {
        $ext = $dependences = [];
        foreach($this->parents as $parent){
            if(isset($this->dependences[$parent])){
                $dependences[] = $this->dependences[$parent];
            }
        }
   
        foreach($dependences as $dependence){
            $ext = array_merge($ext, $dependence);
        }
         
        return [$this->service => $ext];
    }  

    /**
    * 
    * @return array
    */ 
    public function getCallables()
    {
        $callables = [];
        foreach($this->parents as $parent){
            if(isset($this->callables[$parent])){
                $callables[] = $this->callables[$parent];
            }
        }
        
        foreach($callables as $callable){
            $this->callables = array_merge($this->callables, $callable);
        }
     
        return [$this->service => $this->callables];
    }
}