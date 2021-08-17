<?php

namespace Abcsoft\DIC\Language;

/** 
 * Класс En
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */   
class En
{
    
    public static function set() 
    {
        if(!defined("ABC_DIC")){
         
            define("ABC_DIC", true);
            
            define('ABC_DIC_INVALID_SERVICE',          ' The service is set incorrectly');
            define('ABC_DIC_NO_SERVICE',               ' Service <strong>%s</strong> is not defined');  
            define('ABC_DIC_ALREADY_SERVICE',          ' Service <strong>%s</strong> is already installed');
            define('ABC_DIC_ALREADY_DEPENDANCE',       ' The dependencies for the service <strong>%s</strong> are already installed');        
            define('ABC_DIC_NOT_FOUND_SERVICE',        ' Service <strong>%s</strong> not found');      
            define('ABC_DIC_INVALID_CALLABLE',         ' Argument must be a function of anonymity is conferred');        
            
            define('ABC_DIC_INVALID_DEPENDANCE',       ' The %s type cannot be used for a dependency in a service %s. Wrap the data in a closure.');
            define('ABC_DIC_INVALID_INJECT',           ' Dependency injection into service %s is not possible. Service is not an object of a valid class');
            define('ABC_DIC_INVALID_INSTANCE',         ' Service %s must be an instance of %s. Check disabled lazy loading');
            define('ABC_DIC_INVALID_SERVICENAME',      ' %s cannot be used as service name. The key must be string');            
            define('ABC_DIC_INVALID_DATA',        'The service %s is set incorrectly. 
            The value must be a class name, object, or closure. Wrap the data in a closure or use single sintax');
            define('ABC_DIC_NOT_FOUND_IN_LOCATOR',     ' Service %s not found in servicelocator %s');
            define('ABC_DIC_ALREADY_TYPE',             ' The type of injection via %s is already set');
    
            
            define('ABC_DIC_SETTER_NOT_FOUND',         ' Setter for property <strong>%s</strong> not found in class <strong>%s</strong> ');  
            define('ABC_DIC_INVALID_CALL_ORDER',       ' Method <strong>%s</strong> can only be called after addAsLocal() or addAsShared()');
            define('ABC_DIC_INVALID_CONSTRUCT',        ' The constructor is not implemented in <strong>%s</strong>, or it does not accept arguments'); 
            define('ABC_DIC_LOCATOR_EXISTS',           ' Locator <strong>%s</strong> is already installed');
            define('ABC_DIC_RESERVED_WORD',            ' You cannot use the %s reserved word for a service identifier');
            define('ABC_DIC_SYNTHETIC_SERVICE',        ' Service  <strong>%s</strong> created synthetically. 
            Impossible to implement services according to the synthetic');
        }
    }
}
















