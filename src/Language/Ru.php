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
class Ru
{
    /**
    * Устанавливает языковые константы
    */     
    public static function set() 
    {
        if(!defined("ABC_DIC")){
         
            define("ABC_DIC", true);
            
            define("ABC_DIC_INVALID_SERVICE",     
            " The service is set incorrectly 
            <br />\n<span style=\"color:#f98a8a\">
            (сервис задан некорректно)</span><br />\n");
            
            define("ABC_DIC_NO_SERVICE",               
            " Service <strong>%s</strong> is not defined 
            <br />\n<span style=\"color:#f98a8a\">
            (сервис <strong>%s</strong> не определен)</span><br />\n");
            
            define("ABC_DIC_ALREADY_SERVICE",          
            " Service <strong>%s</strong> is already installed 
            <br />\n<span style=\"color:#f98a8a\">
            (сервис  <strong>%s</strong> уже имеется в хранилище)</span><br />\n");
            
            define("ABC_DIC_ALREADY_DEPENDANCE",       
            " The dependencies for the service <strong>%s</strong> are already installed 
            <br />\n<span style=\"color:#f98a8a\">
            (зависимости для сервиса <strong>%s</strong> уже установлены)</span><br />\n");
            
            define("ABC_DIC_NOT_FOUND_SERVICE",        
            " Service <strong>%s</strong> not found 
            <br />\n<span style=\"color:#f98a8a\">
            (сервис <strong>%s</strong> не найден)</span><br />\n"); 
            
            define("ABC_DIC_INVALID_CALLABLE",         
            " Argument must be a function of anonymity is confer#f98a8a 
            <br />\n<span style=\"color:#f98a8a\">
            (аргумент должен быть анонимной функцией)</span><br />\n"); 
            
            define("ABC_DIC_INVALID_DEPENDANCE",       
            " The %s type cannot be used for a dependency in a service %s. Wrap the data in a closure.
            <br />\n<span style=\"color:#f98a8a\">
            (Тип %s не может быть использован в качестве зависимости сервиса %s. Заверните данные в замыкание)</span><br />\n");
            
            define("ABC_DIC_INVALID_INJECT",           
            " Dependency injection into service %s is not possible. Service is not an object of a valid class
            <br />\n<span style=\"color:#f98a8a\">
            (инъекция в сервис %s невозможна. Сервис не является валидным объектом )</span><br />\n");
            
            define("ABC_DIC_INVALID_INSTANCE",         
            " Service %s must be an instance of %s. Check disabled lazy loading
            <br />\n<span style=\"color:#f98a8a\">
            (сервис %s должен быть инстансом %s. Проверьте ленивую загрузку.)</span><br />\n");
            
            define("ABC_DIC_INVALID_SERVICENAME",      
            " %s cannot be used as service name. The key must be string
            <br />\n<span style=\"color:#f98a8a\">
            (%s не может быть использован в качестве имени сервиса. Ключ должен быть строкой.)</span><br />\n");
            
            define("ABC_DIC_INVALID_DATA",        
            "The service %s is set incorrectly. 
            The value must be a class name, object, or closure. Wrap the data in a closure or use single sintax
            <br />\n<span style=\"color:#f98a8a\">
            (Сервис %s не корректен. Значением может быть только имя класса, сервиса или анонимная функция. 
            Заверните данные в замыкание или используйте одиночный синтаксис)</span><br />\n");
            
            define("ABC_DIC_NOT_FOUND_IN_LOCATOR",     
            " Service %s not found in servicelocator %s
            <br />\n<span style=\"color:#f98a8a\">
            (Сервис %s не найден в сервис-локаторе %s)</span><br />\n");
            
            define("ABC_DIC_ALREADY_TYPE",             
            " The type of injection via %s is already set
            <br />\n<span style=\"color:#f98a8a\">
            (тип внедрения %s уже установлен.)</span><br />\n");
            
            define("ABC_DIC_SYNTHETIC_SERVICE",        
            " Service  <strong>%s</strong> created synthetically. Impossible to implement services according to the synthetic 
            <br />\n<span style=\"color:#f98a8a\">
            (сервис  <strong>%s</strong> создан синтетически. Невозможно внедрение зависимости в синтетический сервис)</span><br />\n");
            
            define("ABC_DIC_SETTER_NOT_FOUND",         
            " Setter for property %s not found in class %s
            <br />\n<span style=\"color:#f98a8a\">
            (сеттер для свойства %s не найден в классе %s)</span><br />\n");
            
            define("ABC_DIC_INVALID_CALL_ORDER",         
            " Method %s::extendsService() can only be called after addAsLocal() or addAsShared() 
            <br />\n<span style=\"color:#f98a8a\">
            (метод %s::extendsService() может быть вызван только после addAsLocal() или addAsShared())</span><br />\n");
            
            define("ABC_DIC_INVALID_CONSTRUCT",        
            " The constructor is not implemented in %s, or it does not accept arguments 
            <br />\n<span style=\"color:#f98a8a\">
            (конструктор класса %s не реализован, либо не принимает аргументов)</span><br />\n");
            
            define("ABC_DIC_LOCATOR_EXISTS",           
            " Locator <strong>%s</strong> is already installed
            <br />\n<span style=\"color:#f98a8a\">
            (локатор <strong>%s</strong> уже установлен)</span><br />\n");
            
            define("ABC_DIC_RESERVED_WORD",            
            " You cannot use the <strong>%s</strong> reserved word for a service identifier
            <br />\n<span style=\"color:#f98a8a\">
            (Вы не можете использовать зарезервированное слово <strong>%s</strong> для идентификатора службы)</span><br />\n");   
        }   
    }    
}
    
