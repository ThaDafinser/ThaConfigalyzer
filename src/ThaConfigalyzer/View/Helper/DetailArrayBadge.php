<?php
namespace ThaConfigalyzer\View\Helper;

use Zend\View\Helper\AbstractHelper;

class DetailArrayBadge extends AbstractHelper
{

    /**
     * Renders a detail entry for an array.
     *
     * @param string $label
     *            Label name
     * @param array $details
     *            Value array (list)
     * @return string
     */
    public function __invoke($label, array $results)
    {
        $r = array();
        
        $r[] = '<span class="zdt-toolbar-info">';
        
        $r[] = '<span class="zdt-detail-label">';
        $r[] = $label;
        $r[] = '</span>';
        
        $extraCss = '';
        $newLine = false;
        
        foreach ($results as $entry) {
            if ($newLine === true) {
                $r[] = '</span><span class="zdt-toolbar-info">';
            }
            
            $title = '';
            if ($entry['description'] != '') {
                $title = $entry['description'];
            }
            
            $class = '';
            if ($entry['result'] === true) {
                $class = 'zdt-toolbar-status-green';
            } elseif($entry['result'] === false) {
                $class = 'zdt-toolbar-status-red';
            }
             
            $string = '<span title="' . $title . '" class="zdt-toolbar-status ' . $class . '">' . $entry['label'] . '</span>';
            
            $r[] = sprintf('<span class="zdt-detail-value%s">%s</span>', $extraCss, $string);
            
            $newLine = true;
            $extraCss = ' zdt-detail-extra-value';
        }
        
        $r[] = '</span>';
        
        return implode('', $r);
    }
}
