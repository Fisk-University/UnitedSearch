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
use Omeka\Api\Manager as ApiManager;
use Laminas\Form\FormElementManager;

class ItemSetSearch extends AbstractBlockLayout implements TemplateableBlockLayoutInterface
{
    const ITEM_SET_ALL_ITEMS = '__all_items__';
    const ITEM_SET_ALL_ITEM_SETS = '__all_item_sets__';
    const PROPERTY_ALL_PROPERTIES = '__all_properties__';

    /**
     * @var FormElementManager
     */
    protected $formElements;

    /**
     * @var ApiManager
     */
    protected $api;

    public function getLabel()
    {
        return 'Item Set | Property Search'; // @translate
    }

    public function __construct(FormElementManager $formElements, ApiManager $api)
    {
        $this->formElements = $formElements;
        $this->api = $api;
    }

    public function form(
        PhpRenderer $view,
        SiteRepresentation $site,
        SitePageRepresentation $page = null,
        SitePageBlockRepresentation $block = null
    ) {
        $defaults = [
            'selectedItemSet' => '',
            'conditionalSelect' => 'and',
            'searchTypeSelect' => 'in',
            'searchField' => '',
            'fieldPlaceholder' => '',
        ];

        $data = $block ? $block->data() + $defaults : $defaults;

        $layoutForm = new Form();
        $errors = [];

        /*
         * Keep Omeka's native ItemSetSelect so the original item set loading behavior
         * remains intact. Add synthetic options using prepend_value_options so they
         * appear before the real item sets without replacing Omeka's populated list.
         */
        try {
            $itemSetSelect = $this->formElements->get(OmekaElement\ItemSetSelect::class);
            $itemSetSelect->setName('o:block[__blockIndex__][o:data][selectedItemSet]');

            $itemSetSelect->setOptions([
                'label' => 'Item Set', // @translate
                'empty_option' => 'Select item set…', // @translate
                'term_as_value' => false,
                'prepend_value_options' => [
                    self::ITEM_SET_ALL_ITEMS => 'All items', // @translate
                    self::ITEM_SET_ALL_ITEM_SETS => 'All item sets', // @translate
                ],
            ]);

            $itemSetSelect->setAttributes([
                'value' => $data['selectedItemSet'] ?? '',
                'required' => true,
                'data-column-data-key' => 'item_set_id',
                'class' => 'chosen-select',
            ]);

            $layoutForm->add($itemSetSelect);
        } catch (\Exception $e) {
            $errors[] = 'Unable to load item set selector: ' . $e->getMessage();
        }

        /*
         * Keep Omeka's native PropertySelect so the original vocabulary/property
         * loading behavior remains intact. Add "All properties" before the real
         * property list without replacing Omeka's populated list.
         */
        try {
            $propertySelect = $this->formElements->get(OmekaElement\PropertySelect::class);
            $propertySelect->setName('o:block[__blockIndex__][o:data][searchField]');

            $propertySelect->setOptions([
                'label' => 'Property', // @translate
                'empty_option' => 'Select property…', // @translate
                'term_as_value' => false,
                'prepend_value_options' => [
                    self::PROPERTY_ALL_PROPERTIES => 'All properties', // @translate
                ],
            ]);

            $propertySelect->setAttributes([
                'value' => $data['searchField'] ?? '',
                'required' => true,
                'data-column-data-key' => 'searchField',
                'class' => 'chosen-select',
            ]);

            $layoutForm->add($propertySelect);
        } catch (\Exception $e) {
            $errors[] = 'Unable to load property selector: ' . $e->getMessage();
        }

        $layoutForm->add([
            'name' => 'o:block[__blockIndex__][o:data][fieldPlaceholder]',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Placeholder:', // @translate
            ],
            'attributes' => [
                'value' => $data['fieldPlaceholder'],
            ],
        ]);

        $layoutForm->add([
            'name' => 'o:block[__blockIndex__][o:data][searchTypeSelect]',
            'type' => Element\Text::class,
            'options' => [],
            'attributes' => [
                'value' => $data['searchTypeSelect'],
                'hidden' => 'hidden',
            ],
        ]);

        $layoutForm->prepare();

        $html = '';

        if (!empty($errors)) {
            $html .= '<div class="errors">' . implode('<br>', array_map('htmlspecialchars', $errors)) . '</div>';
        }

        $html .= $view->formCollection($layoutForm);

        return $html;
    }

    public function render(
        PhpRenderer $view,
        SitePageBlockRepresentation $block,
        $templateViewScript = 'common/block-layout/itemsetsearch'
    ) {
        $blockData = ($block) ? $block->data() : [];

        $blockData['allItemsOption'] = self::ITEM_SET_ALL_ITEMS;
        $blockData['allItemSetsOption'] = self::ITEM_SET_ALL_ITEM_SETS;
        $blockData['allPropertiesOption'] = self::PROPERTY_ALL_PROPERTIES;

        return $view->partial($templateViewScript, $blockData);
    }
}