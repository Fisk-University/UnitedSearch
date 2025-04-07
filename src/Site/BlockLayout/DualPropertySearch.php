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
use UnitedSearch\Service\PropertyValueService;

class DualPropertySearch extends AbstractBlockLayout implements TemplateableBlockLayoutInterface
{
    /**
     * @var FormElementManager
     */
    protected $formElements;

    /**
     * @var PropertyValueService
     */
    protected $propertyValueService;

    public function __construct(
        FormElementManager $formElements, 
        PropertyValueService $propertyValueService
    ) {
        $this->formElements = $formElements;
        $this->propertyValueService = $propertyValueService;
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
                'class' => 'chosen-select', // Add chosen-select class for admin side
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
                'class' => 'chosen-select', // Add chosen-select class for admin side
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
                'class' => 'chosen-select', // Add chosen-select class for admin side
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
        
        // If both properties are selected, use the new service to get values
        if (!empty($blockData['propertyOne']) && !empty($blockData['propertyTwo'])) {
            // Get unique values for first property
            $blockData['propertyOneValues'] = $this->propertyValueService->getUniquePropertyValues(
                $blockData['propertyOne'], 
                ['limit' => 500]
            );

            // Create relationship map
            $blockData['relationshipMap'] = $this->propertyValueService->createRelationshipMap(
                $blockData['propertyOne'], 
                $blockData['propertyTwo']
            );

            // Get all possible values for property two if join type is 'or'
            if ($blockData['joinType'] === 'or') {
                $blockData['propertyTwoValues'] = $this->propertyValueService->getUniquePropertyValues(
                    $blockData['propertyTwo'], 
                    ['limit' => 500]
                );
            }
        }

        return $view->partial($templateViewScript, $blockData);
    }
}