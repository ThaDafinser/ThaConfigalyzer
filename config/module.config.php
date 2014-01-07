<?php
return array(
    
    'service_manager' => array(
        'invokables' => array(
            'configPerformance' => 'ThaConfigalyzer\Collector\ConfigPerformance'
        )
    ),
    
    'view_manager' => array(
        'template_map' => array(
            'zend-developer-tools/toolbar/performance' => __DIR__ . '/../view/zend-developer-tools/toolbar/performance.phtml'
        )
    ),
    
    'view_helpers' => array(
        'invokables' => array(
            'ThaConfigalyzerDetailArrayBadge' => 'ThaConfigalyzer\View\Helper\DetailArrayBadge',
    )
    ),
    
    'zenddevelopertools' => array(
        
        'profiler' => array(
            'collectors' => array(
                'configPerformance' => 'configPerformance'
            )
        ),
        'toolbar' => array(
            'entries' => array(
                'configPerformance' => 'zend-developer-tools/toolbar/performance'
            )
        )
    )
);
