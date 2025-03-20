<?php
namespace UnitedSearch\Site\BlockLayout;

use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Laminas\View\Renderer\PhpRenderer;

class DualPropertySearch extends AbstractBlockLayout
{
    public function getLabel()
    {
        return 'Dual Property Search'; // @translate
    }

    public function form(PhpRenderer $view, SiteRepresentation $site,
    SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null
) { 
    $defaults = [
        'propertyOne' => '',
        'propertyTwo' => '',
        'joinType' => 'and',
    ];

    $data = $block ? $block->data() + $defaults : $defaults;
    
    $html = '';
    
    // Simple property fields (not using PropertySelect yet)
    $html .= '<div class="field"><div class="field-meta">';
    $html .= '<label>First Property</label>';
    $html .= '</div><div class="inputs">';
    $html .= '<input name="o:block[__blockIndex__][o:data][propertyOne]" type="text" value="' . $view->escapeHtml($data['propertyOne']) . '">';
    $html .= '</div></div>';
    
    $html .= '<div class="field"><div class="field-meta">';
    $html .= '<label>Second Property</label>';
    $html .= '</div><div class="inputs">';
    $html .= '<input name="o:block[__blockIndex__][o:data][propertyTwo]" type="text" value="' . $view->escapeHtml($data['propertyTwo']) . '">';
    $html .= '</div></div>';
    
    // Join type select
    $html .= '<div class="field"><div class="field-meta">';
    $html .= '<label>Join Type</label>';
    $html .= '</div><div class="inputs">';
    $html .= '<select name="o:block[__blockIndex__][o:data][joinType]">';
    $html .= '<option value="and"' . ($data['joinType'] == 'and' ? ' selected' : '') . '>AND</option>';
    $html .= '<option value="or"' . ($data['joinType'] == 'or' ? ' selected' : '') . '>OR</option>';
    $html .= '</select>';
    $html .= '</div></div>';
    
    return $html;
}
public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
{
    $data = $block ? $block->data() : [];
    $html = '<div class="dual-property-search-debug">';
    $html .= 'Property One: ' . $view->escapeHtml($data['propertyOne'] ?? '') . '<br>';
    $html .= 'Property Two: ' . $view->escapeHtml($data['propertyTwo'] ?? '') . '<br>';
    $html .= 'Join Type: ' . $view->escapeHtml($data['joinType'] ?? '') . '<br>';
    $html .= '</div>';
    
    return $html;
}
}