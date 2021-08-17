<?php

namespace DIC\Tests;

use Abcsoft\DIC\{
    Container,
    Exceptions\ContainerException,
    Exceptions\NotFoundException,
    LazyLoad
};
 
use Abcsoft\DIC\Tests\Fixtures\{
    TestInterface,
    TestService,
    ViaConstruct,
    ViaMethods,
    ViaProperties    
};

class MaperTest extends \PHPUnit_Framework_TestCase
{

    protected function getContainer($new = false)
    { 
        $container = new \DIC\Container;    
        $container->typeProperties('Service', 'ServiceViaProperties');        
        $container->typeMethodCall('ServiceViaMethods');
        return $container;        
    }
    
    /**
    * 
    */   
    public function getSimpleMap()
    {
        return [
            'ByClassName'   => TestService::class,
            'ByServiceName' => 'ByClassName',            
            'WithObject'    => new TestService,
            'WithCallable'  => function () {
                return new TestService;
            },
            'WithAnyData'  => function () {
                return 'foo';
            },
        ];
    }  
    
    /**
    * 
    */   
    public function getGlobalMap()
    {
        return [
            'StdGlobal' => \StdClass::class    
        ];
    } 
    
    /**
    * 
    */   
    public function getInjectionsMap()
    {
        return [
            'ServiceViaConstruct' => [
                ViaConstruct::class,
                    'service' => TestService::class, 
                    'std'     => \StdClass::class
            ],
            'ServiceViaMethods' => [
                ViaMethods::class, 
                    'service' => TestService::class
                ],
            'ServiceViaProperties' => [
               ViaProperties::class,
                   'service' => TestService::class
            ]
        ];
    }
    
    /**
    * 
    */   
    public function getInjectionsInternalMap()
    {
        return [
            'ServiceViaConstruct' => [
                ViaConstruct::class,
                    'service' => TestService::class, 
                    'std'     => \StdClass::class
            ],
            'ServiceViaMethods' => [
                ViaMethods::class, 
                    'viaConstruct' => 'ServiceViaConstruct'
            ],
            'Service' => [
                TestService::class,
                    'viaConstruct' => 'ServiceViaConstruct',
                    'viaMethods'   => 'ServiceViaMethods',
            ],            
                
            'ServiceViaProperties' => [
               ViaProperties::class,
                   'service' => 'Service'
            ]
        ];
    }  

    /**
    * 
    */   
    public function getExtendsMap()
    {
        return [
            'ServiceViaConstruct' => [
                ViaConstruct::class,
                    'service' => TestService::class, 
                    'std'     => \StdClass::class
            ],         
            'ServiceViaProperties extends ServiceViaConstruct' => [
               ViaProperties::class,
                   'std' => false
            ]
        ];
    } 
    
    /**
    * 
    */   
    public function getDefaultDependencesMap()
    {
        return [
            ABCDIC_DEFAULT => [
               'service' => TestService::class,
               'std'     => \stdClass::class
            ],
            
            'ServiceWithDefault' => ViaConstruct::class
        ];    
    }
    
    /**
    * NotFoundException
    */   
    public function testNotFoundException()
    {
        $container = $this->getContainer(); 
        $container->setMaps($this->getSimpleMap());
        
        $this->expectException(NotFoundException::class);        
        $this->expectExceptionMessage('Service Dummy not found');
      
        $container->get('Dummy');
    }    
    /**
    * ContainerException
    */   
    public function testContainerExceptions()
    {
        $container = $this->getContainer(); 
        
        $this->expectException(ContainerException::class);        
        $this->expectExceptionMessage('Service foo is already installed');
        $container->add([
            'foo' => function() {return 'bar';}
            ]
        );
        $container->add([
            'foo' => function() {return 'baz';}
            ]
        );
    }
    /**
    * With other data
    */    
    public function testOtherDataException()
    {
        $container = $this->getContainer();
     
        $this->expectException(ContainerException::class);        
        $this->expectExceptionMessage('The service foo is set incorrectly. 
                    The value must be a class name, object, or closure. Wrap the data in a closure
                    or use single sintax');
     
        $container->add([
            'foo' => 'bar'
            ]
        );
    }
    /**
    * Simple Services
    */   
    public function testSimpleServices()
    {
        $container = $this->getContainer(); 
        $container->add($this->getSimpleMap());
        
        $this->assertInstanceOf(TestService::class, $container->get('ByClassName'));
        $this->assertInstanceOf(TestService::class, $container->get('ByServiceName'));
        $this->assertInstanceOf(TestService::class, $container->get('WithObject'));
        $this->assertInstanceOf(TestService::class, $container->get('WithCallable'));
    }
    /**
    * Global service 
    */    
    public function testGlobalService()
    {
        $container = $this->getContainer();
        $container->setMaps($this->getSimpleMap(), $this->getGlobalMap());
        
        $container = $this->getContainer(); 

        /** @var StdClass $stdGlobal */        
        $stdGlobal = $container->get('StdGlobal');
     
        $this->assertInstanceOf(\StdClass::class, $stdGlobal); 

        $this->expectException(NotFoundException::class); 
        $container->get('ByClassName');
    }     
    /**
    * Factory
    */    
    public function testFactory()
    {
        $container = $this->getContainer();
        $container->add([
            'Std' => $container->factory(\StdClass::class)
            ]
        );
        
        $this->assertNotSame($container->get('Std'), $container->get('Std'));
        $this->assertNotSame($container->getNew('Std'), $container->getNew('Std'));
    } 

    /**
    * Inject dependences 
    */    
    public function testInjectDependences()
    {
        $container = $this->getContainer();
        $container->add($this->getInjectionsMap());
        
        /** @var ViaConstruct $viaConstruct */
        $viaConstruct = $container->get('ServiceViaConstruct');
        
        /** @var TestService $service */
        $service = $viaConstruct->service;
        
        /** @var StdClass $std */
        $std = $viaConstruct->std;
        
        $this->assertInstanceOf(TestInterface::class, $service);        
        $this->assertInstanceOf(\StdClass::class, $std);
        
        /** @var ViaMethods $viaMethods */
        $viaMethods = $container->get('ServiceViaMethods');
        
        /** @var TestService $service */
        $service = $viaMethods->service; 
        
        $this->assertInstanceOf(TestInterface::class, $service); 
     
        /** @var ViaProperties $viaProperties */
        $viaProperties = $container->get('ServiceViaProperties');
        
        /** @var TestService $service */
        $service = $viaProperties->service;
        $this->assertInstanceOf(TestInterface::class, $service);    
    } 
    /**
    * Compiled service with internal services
    */
    public function testCompiledServiceWithInternalServices()
    {
        $container = $this->getContainer();
        $container->add($this->getInjectionsInternalMap());
        
        
     
        
        $viaProperties = $container->get('ServiceViaProperties');
      
        $this->assertEquals('foo', $viaProperties->service->viaMethods->viaConstruct->service->test);
    }  
    /**
    * Extends dependences
    */
    public function testExtendsDependences()
    {
        $container = $this->getContainer();
        $container->add($this->getExtendsMap());        
     
        /** @var ViaProperties $viaProperties */         
        $viaProperties = $container->get('ServiceViaProperties');
        
        $this->assertInstanceOf(TestService::class, $viaProperties->service);
        $this->assertNull($viaProperties->std);
    } 
    /**
    * Set and clear default blocks
    */
    public function testSetAndClearDefault()
    {
        $container = $this->getContainer();
        $container->add($this->getDefaultDependencesMap());
        
        /** @var ViaConstruct $withDefault */
        $withDefault = $container->get('ServiceWithDefault');
     
        $this->assertInstanceOf(TestService::class, $withDefault->service);
        $this->assertInstanceOf(\stdClass::class, $withDefault->std);
        
        $container->clearDefault();   
        $container->add('ServiceWithoutDefault', TestService::class);
     
        /** @var ViaConstruct $withoutDefault */
        $withoutDefault = $container->get('ServiceWithoutDefault');
     
        $this->assertNull($withoutDefault->service);
        $this->assertNull($withoutDefault->std);
    }   
}
