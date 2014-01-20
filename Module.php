<?php
namespace ThaConfigalyzer;

use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventInterface;

class Module
{

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function init(ModuleManager $manager)
    {
        $events = $manager->getEventManager();
        
        // Initialize logger collector once the profiler is initialized itself
        $events->attach('profiler_init', function () use($manager)
        {
            $manager->getEvent()
                ->getParam('ServiceManager')
                ->get('configPerformance');
        });
    }
}

