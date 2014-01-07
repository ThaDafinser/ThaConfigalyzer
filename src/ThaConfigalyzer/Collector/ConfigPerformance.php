<?php
namespace ThaConfigalyzer\Collector;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use ZendDeveloperTools\Collector\ConfigCollector;
use ZendDeveloperTools\Collector\AbstractCollector;
use Zend\Mvc\MvcEvent;

class ConfigPerformance extends AbstractCollector
{
    use ServiceLocatorAwareTrait;

    const NAME = 'configPerformance';

    const PRIORITY = 100;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return static::PRIORITY;
    }

    /**
     * Collects data.
     *
     * @param MvcEvent $mvcEvent            
     */
    public function collect(MvcEvent $mvcEvent)
    {
        /* @var $application \Zend\Mvc\Application */
        if (! $application = $mvcEvent->getApplication()) {
            return;
        }
        
        $sm = $application->getServiceManager();
        $applicationConfig = $sm->get('ApplicationConfig');
        
        // general
        $this->data = array();
        $this->data[] = array(
            'label' => 'General',
            'results' => $this->getGeneral($mvcEvent)
        );
        
        if (isset($applicationConfig['modules']) && in_array('DoctrineORMModule', $applicationConfig['modules'])) {
            $this->data[] = array(
                'label' => 'Doctrine settings',
                'results' => $this->getDoctrine($mvcEvent)
            );
        }
        
        // loaded modules
        if (isset($applicationConfig['modules'])) {
            foreach ($applicationConfig['modules'] as $moduleName) {
                $this->data[] = array(
                    'label' => $moduleName,
                    'results' => $this->getModulePerformance($moduleName, $mvcEvent)
                );
            }
        }
    }

    private function getGeneral(MvcEvent $mvcEvent)
    {
        /* @var $application \Zend\Mvc\Application */
        $application = $mvcEvent->getApplication();
        $sm = $application->getServiceManager();
        
        $applicationConfig = $sm->get('ApplicationConfig');
        $config = $sm->get('config');
        
        $return = array();
        
        /*
         * Config cache enabled?
         */
        $result = false;
        if (isset($applicationConfig['module_listener_options']['config_cache_enabled']) && $applicationConfig['module_listener_options']['config_cache_enabled'] === true) {
            $result = true;
        }
        $return[] = array(
            'label' => 'Config cache enabled',
            'description' => 'This is the description',
            'result' => $result
        );
        
        /*
         * Module map cache enabled?
         */
        $result = false;
        if (isset($applicationConfig['module_listener_options']['module_map_cache_enabled']) && $applicationConfig['module_listener_options']['module_map_cache_enabled'] === true) {
            $result = true;
        }
        $return[] = array(
            'label' => 'Module cache enabled',
            'description' => 'This is the description',
            'result' => $result
        );
        
        /*
         * Composer used? @todo detect classmap enabled?
         */
        $return[] = array(
            'label' => 'Composer used',
            'description' => 'This is the description',
            'result' => class_exists('Composer\Autoload\ClassLoader') ? true : false
        );
        
        $result = false;
        if (class_exists('Composer\Autoload\ClassLoader')) {
            $classMap = include 'vendor/composer/autoload_classmap.php';
            // @todo i think other check would be cool...
            if (count($classMap) > 100) {
                $result = true;
            }
        }
        $return[] = array(
            'label' => 'Composer classmap autoloading',
            'description' => 'This is the description',
            'result' => $result
        );
        
        /*
         * EdpSuperluminal installed?
         */
        if (isset($applicationConfig['modules']) && in_array('EdpSuperluminal', $applicationConfig['modules'])) {
            $return[] = array(
                'label' => 'EdpSuperluminal installed',
                'description' => 'This is the description',
                'result' => true
            );
        } else {
            $return[] = array(
                'label' => 'EdpSuperluminal installed',
                'description' => 'This is the description',
                'result' => false
            );
        }
        
        return $return;
    }

    private function getDoctrine(MvcEvent $mvcEvent)
    {
        $return = array();
        
        /* @var $application \Zend\Mvc\Application */
        $application = $mvcEvent->getApplication();
        $sm = $application->getServiceManager();
        
        $applicationConfig = $sm->get('ApplicationConfig');
        $config = $sm->get('config');
        
        if (isset($config['doctrine']['configuration'])) {
            $doctrineConfiguration = $config['doctrine']['configuration'];
            
            foreach ($doctrineConfiguration as $ormAdapter => $options) {
                /*
                 * Generate proxies
                 */
                $result = '';
                if (isset($options['generate_proxies'])) {
                    $result = false;
                    if ($options['generate_proxies'] === false) {
                        $result = true;
                    }
                }
                $return[] = array(
                    'label' => $ormAdapter . ': Proxy generation',
                    'description' => 'This is the description',
                    'result' => $result
                );
                
                /*
                 * Metadata cache
                 */
                $result = '';
                if (isset($options['metadata_cache'])) {
                    $result = false;
                    if ($options['metadata_cache'] != 'array' && $options['metadata_cache'] != '') {
                        $result = true;
                    }
                }
                $return[] = array(
                    'label' => $ormAdapter . ': Metadata cache',
                    'description' => 'This is the description',
                    'result' => $result
                );
            }
        }
        
        return $return;
    }

    private function getModulePerformance($name, MvcEvent $mvcEvent)
    {
        $return = array();
        
        $className = '\\' . $name . '\Module';
        $moduleClass = new $className();
        
        $config = array();
        if (method_exists($moduleClass, 'getConfig')) {
            $config = $moduleClass->getConfig();
        }
        
        /*
         * Check autoloading classmap
         */
        $result = '';
        if (method_exists($moduleClass, 'getAutoloaderConfig')) {
            $autoloaderConfig = $moduleClass->getAutoloaderConfig();
            
            $result = false;
            if (array_key_exists('Zend\Loader\ClassMapAutoloader', $autoloaderConfig)) {
                $result = true;
            }
        }
        $return[] = array(
            'label' => 'Classmap autoloading',
            'description' => 'This is the description',
            'result' => $result
        );
        
        /*
         * Check templatemap classmap
         */
        $result = '';
        if (isset($config['view_manager'])) {
            if (array_key_exists('template_map', $config['view_manager'])) {
                $result = true;
            } else {
                if (array_key_exists('template_path_stack', $config['view_manager'])) {
                    $result = false;
                }
            }
        }
        $return[] = array(
            'label' => 'Template map',
            'description' => 'This is the description',
            'result' => $result
        );
        
        /*
         * Avoid Feature\*Interfaces
         */
        $result = true;
        $interfaces = class_implements($moduleClass);
        foreach ($interfaces as $interface) {
            if (strpos($interface, 'Zend\\ModuleManager\\Feature') === 0) {
                $result = false;
                break;
            }
        }
        $return[] = array(
            'label' => 'Avoid interfaces in Module.php',
            'description' => 'Avoid the usage of Zend\ModuleManager\Feature\*Interface(s)',
            'result' => $result
        );
        
        /*
         * Move services...
         */
        // $result = '';
        // if (isset($config['view_manager'])) {
        // if (array_key_exists('template_map', $config['view_manager'])) {
        // $result = true;
        // } else {
        // if (array_key_exists('template_path_stack', $config['view_manager'])) {
        // $result = false;
        // }
        // }
        // }
        // $return[] = array(
        // 'label' => 'Template map',
        // 'description' => 'This is the description',
        // 'result' => $result
        // );
        
        return $return;
    }
    
    // private function setConfigPerformance($config)
    // {
    // $result = array();
    
    // $bool = false;
    // if (isset($config['module_listener_options']['config_cache_enabled']) && $config['module_listener_options']['config_cache_enabled'] === true) {
    // $bool = true;
    // }
    // $result['config cache'] = $bool;
    
    // $bool = false;
    // if (isset($config['module_listener_options']['module_map_cache_enabled']) && $config['module_listener_options']['module_map_cache_enabled'] === true) {
    // $bool = true;
    // }
    // $result['module map cache'] = $bool;
    
    // $this->data['config'] = $result;
    // }
    public function getResult()
    {
        return $this->data;
    }
}
