<?php

namespace Abcsoft\DIC\Tests;

use Abcsoft\DIC\Interfaces\LocatorInterface;

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

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    protected function getContainer()
    { 
        $container = new Container;
        $container->typeProperties('Service', 'ServiceViaProperties', 'ServiceWithoutDefault');        
        $container->typeMethodCall('ServiceViaMethods');
        return $container;        
    }
    
    /**
    * NotFoundException
    */   
    public function testNotFoundException()
    {
        $container = $this->getContainer(); 
        $this->expectException(NotFoundException::class);        
        $this->expectExceptionMessage('Service Dummy not found');
      
        $container->get('Dummy');
    }
    /**
    * ContainerException
    */   
    public function testContainerException()
    {
        $container = $this->getContainer(); 
        $this->expectException(ContainerException::class);        
        $this->expectExceptionMessage('Service Std is already installed');
        
        $container->add('Std', \StdClass::class);
        $container->add('Std', \StdClass::class);
    }    
    /**
    * With closure
    */   
    public function testWithClosure()
    {
        $container = $this->getContainer(); 
        $container->add('Service', function () {
            return new TestService;
        });
     
        $this->assertInstanceOf(TestService::class, $container->get('Service'));
    }        
    /**
    * With class name
    */ 
    public function testWithClassName()
    {
        $container = $this->getContainer();
        $container->add('Service', TestService::class);
     
        $this->assertInstanceOf(TestService::class, $container->get('Service'));
    }
    /**
    * With object
    */ 
    public function testWithObject()
    {
        $container = $this->getContainer();  
        $container->add('Std', new \StdClass);
        
        $this->assertInstanceOf(\StdClass::class, $container->get('Std'));
    }    
    /**
    * With service name
    */  
    public function testWithServiceName()
    {
        $container = $this->getContainer();        
        $container->add('Service', TestService::class);    
        $container->add('OtherService', 'Service');
     
        $this->assertInstanceOf(TestService::class, $container->get('OtherService'));
    }     
    /**
    * With other data
    */    
    public function testWithOtherData()
    {
        $container = $this->getContainer();
     
        $container->add('Float', 1.234);        
        $container->add('String', 'foo');    
        $container->add('Array', ['foo' => 'bar']);
        
        $this->assertEquals(1.234, $container->get('Float'));
        $this->assertEquals('foo', $container->get('String'));
        $this->assertEquals(['foo' => 'bar'], $container->get('Array'));
    } 
    /**
    * has() 
    */    
    public function testHas()
    {
        $container = $this->getContainer();
        $container->add('Std', \StdClass::class);
        
        $this->assertTrue($container->has('Std'));
    } 
    
    /**
    * get() and getNew()
    */    
    public function testGetAndGetNew()
    {
        $container = $this->getContainer();
        $container->add('Std', \StdClass::class);
        
        $this->assertSame($container->get('Std'), $container->get('Std'));
        $this->assertNotSame($container->getNew('Std'), $container->getNew('Std'));
    } 
    /**
    * Lasy load
    */     
    public function testLasyLoad()
    {
        $container = $this->getContainer();
        $container->lazyLoad(true);
        $container->add('Service', TestService::class);
        $service = $container->get('Service');
        
        $this->assertInstanceOf(LazyLoad::class, $service);
        $service->test = new \StdClass;    
        $this->assertInstanceOf(\StdClass::class, $service->test);
        
        $container->lazyLoad(false);
        $service = $container->getNew('Service');
        $this->assertInstanceOf(TestService::class, $service);
        
    }    
    /**
    * Factory
    */    
    public function testFactory()
    {
        $container = $this->getContainer();
        $container->add('Std', $container->factory(\StdClass::class));
        
        $this->assertNotSame($container->get('Std'), $container->get('Std'));
        $this->assertNotSame($container->getNew('Std'), $container->getNew('Std'));
    }      
    /**
    * Global service
    */    
    public function testGlobalService()
    {
        $container = $this->getContainer();
        $container->addGlobal('StdGlobal', \StdClass::class);
        
        $container = $this->getContainer(); 
        
        /** @var StdClass $stdGlobal */        
        $stdGlobal = $container->get('StdGlobal');
     
        $this->assertInstanceOf(\StdClass::class, $stdGlobal);     
    }          
    /**
    * Inject dependences via construct and interface
    */    
    public function testInjectDependencesViaConstruct()
    {
        $container = $this->getContainer();
        
        $container->add('ServiceViaConstruct', ViaConstruct::class)
            ->addDependences('ServiceViaConstruct', [
                'service' => TestService::class, 
                'std'     => \StdClass::class
            ]
        );
     
        /** @var ViaConstruct $viaConstruct */
        $viaConstruct = $container->get('ServiceViaConstruct');
        
        /** @var TestService $service */
        $service = $viaConstruct->service;
        
        /** @var StdClass $std */
        $std = $viaConstruct->std;
        
        $this->assertInstanceOf(TestInterface::class, $service);        
        $this->assertInstanceOf(\StdClass::class, $std);
    }     
    /**
    * Inject dependences via methods and interface
    */    
    public function testInjectDependencesViaMethods()
    {
        $container = $this->getContainer();
        
        $container->add('ServiceViaMethods', ViaMethods::class);
        $container->addDependences('ServiceViaMethods', 
            ['service' => TestService::class]);
        
        /** @var ViaMethods $viaMethods */
        $viaMethods = $container->get('ServiceViaMethods');
        
        /** @var TestService $service */
        $service = $viaMethods->service;    
        $this->assertInstanceOf(TestInterface::class, $service);
    }         
    /**
    * Inject dependences via properties and interface
    */    
    public function testInjectDependencesViaProperties()
    {
        $container = $this->getContainer();
        
        $container->add('ServiceViaProperties', ViaProperties::class);
        $container->addDependences('ServiceViaProperties', 
            ['service' => TestService::class]);
        
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
     
        $container->add('ServiceViaConstruct', ViaConstruct::class)
            ->addDependences('ServiceViaConstruct', [
               'service' => TestService::class, 
               'std'     => \StdClass::class
            ]
        );
     
        $container->add('ServiceViaMethods', ViaMethods::class)
            ->addDependences('ServiceViaMethods', 
                ['viaConstruct' => 'ServiceViaConstruct']
        );
     
        $container->add('Service', TestService::class)
            ->addDependences('Service', [
                'viaConstruct' => 'ServiceViaConstruct',
                'viaMethods'   => 'ServiceViaMethods',
            ]
        );
        
        $container->add('ServiceViaProperties', ViaProperties::class)
            ->addDependences('ServiceViaProperties', 
                ['service' => 'Service']
        );
        
        /** @var ViaProperties $viaProperties */
        $viaProperties = $container->get('ServiceViaProperties');
      
        $this->assertEquals('foo', $viaProperties->service->viaMethods->viaConstruct->service->test);
    }     
    /**
    * Extends dependences
    */
    public function testExtendsDependences()
    {
        $container = $this->getContainer();
     
        $container->add('Service', TestService::class);        
        
        $container->add('ServiceViaConstruct', ViaConstruct::class)
            ->addDependences('ServiceViaConstruct', [
               'service' => 'Service', 
               'std'     => \StdClass::class
            ]
        );
     
        $container->add('ServiceViaProperties', ViaProperties::class)
            ->extendsDependences('ServiceViaConstruct')
            ->addDependences('ServiceViaProperties', 
                ['std' => false]
        );
     
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
     
        $container->addDefault([
           'service' => TestService::class,
           'std'     => \stdClass::class
        ]);
      
        $container->add('ServiceWithDefault', ViaConstruct::class);
        
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
    /**
    * Service Locator
    */
    public function testServiceLocator()
    {
        $container = $this->getContainer(); 
        $container->lazyLoad(true);
        $container->lazyLoad(false, ['Locator']);
        
        $container->createLocator('Locator', ['Service']);
        
        $container->add('Service', TestService::class);
        $container->add('Std', \StdClass::class);
               
        /** @var LocatorInterface $locator */
        $locator = $container->get('Locator');
     
        /** @var TestService $service */
        $service = $locator->get('Service');//dbg($service); 
        
        $this->assertInstanceOf(LocatorInterface::class, $locator);
        $this->assertInstanceOf(LazyLoad::class, $service);
     
        $this->expectException(NotFoundException::class);        
        $this->expectExceptionMessage('Service Std not found in servicelocator Locator');
        
        $locator->get('Std');
    }
    /**
    * Service Locator Implements
    */
    public function testServiceLocatorImplements()
    {
        $container = $this->getContainer(); 
        $container->createLocator('Locator', ['Service']);     
        $container->add('Service', TestService::class);
        
        /** @var LocatorInterface $locator */
        $locator = $container->get('Locator');
        
        /** @var TestService $service, $withClass, $withInterface */
        $simple = $locator->get('Service');
        $withClass = $locator->get(TestService::class, 'Service');
        $withInterface = $locator->get(TestInterface::class, 'Service');
        
        $this->assertInstanceOf(TestInterface::class, $simple);
        $this->assertInstanceOf(TestInterface::class, $withClass);
        $this->assertInstanceOf(TestInterface::class, $withInterface);    
        
        $this->expectException(ContainerException::class);        
        $this->expectExceptionMessage('Service Service  must be an instance of StdClass. Check disabled lazy loading');
        
        $locator->get(\StdClass::class, 'Service');
    }
    /**
    * Helper methods
    */
    public function testHelperMethods()
    {
        $container = $this->getContainer(); 
        $this->expectException(ContainerException::class);        
        $this->expectExceptionMessage('Service Service created synthetically. 
        Impossible to implement services according to the synthetic');        
        
        $container->add('Service', TestService::class);
        $this->assertFalse($container->isSynthetic('Service'));
        
        $container->serviceSynthetic('Service');
        $this->assertTrue($container->isSynthetic('Service'));
        
        $container->addDependences('Service', ['std' => \StdClass::class]);
    }    
}
