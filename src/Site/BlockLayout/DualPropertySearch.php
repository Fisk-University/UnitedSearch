<?php
namespace UnitedSearch\Site\BlockLayout;

use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Site\BlockLayout\TemplateableBlockLayoutInterface;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Form\Element as OmekaElement;
use Laminas\Form\Element;
use Laminas\Form\Form;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\Form\FormElementManager;

class DualPropertySearch extends AbstractBlockLayout implements TemplateableBlockLayoutInterface
{
    /**
     * @var FormElementManager
     */
    protected $formElements;

    public function __construct(FormElementManager $formElements)
    {
        $this->formElements = $formElements;
    }

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
        
        $layoutForm = new Form();
        $errors = [];

        try {
            $propertySelectOne = $this->formElements->get(OmekaElement\PropertySelect::class);
            $propertySelectOne->setName('o:block[__blockIndex__][o:data][propertyOne]');
            $propertySelectOne->setOptions([
                'label' => 'First Property', // @translate
                'empty_option' => 'Select property…', // @translate
                'term_as_value' => true,
            ]);
            $propertySelectOne->setAttributes([
                'value' => $data['propertyOne'] ?? null,
                'required' => true,
            ]);
            $layoutForm->add($propertySelectOne);
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }
        
        try {
            $propertySelectTwo = $this->formElements->get(OmekaElement\PropertySelect::class);
            $propertySelectTwo->setName('o:block[__blockIndex__][o:data][propertyTwo]');
            $propertySelectTwo->setOptions([
                'label' => 'Second Property', // @translate
                'empty_option' => 'Select property…', // @translate
                'term_as_value' => true,
            ]);
            $propertySelectTwo->setAttributes([
                'value' => $data['propertyTwo'] ?? null,
                'required' => true,
            ]);
            $layoutForm->add($propertySelectTwo);
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }
        
        // Join type selector (AND/OR)
        $layoutForm->add([
            'name' => 'o:block[__blockIndex__][o:data][joinType]',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Join Type', // @translate
                'value_options' => [
                    'and' => 'AND - Show related values only', // @translate
                    'or' => 'OR - Show all possible values', // @translate
                ],
            ],
            'attributes' => [
                'value' => $data['joinType'],
            ]
        ]);
        
        $layoutForm->prepare();

        $html = '';
        if (!empty($errors)) {
            $html .= '<div class="errors">' . implode('<br>', $errors) . '</div>';
        }
        $html .= $view->formCollection($layoutForm);
        return $html;
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block, $templateViewScript = 'common/block-layout/dualproperty-search')
    {
        $blockData = ($block) ? $block->data() : [];
        return $view->partial($templateViewScript, $blockData);
    }
}