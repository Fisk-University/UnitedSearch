<?php
namespace UnitedSearch\Site\BlockLayout;

use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Site\BlockLayout\TemplateableBlockLayoutInterface;
use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
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
         * Build item set options manually instead of using OmekaElement\ItemSetSelect.
         * Omeka's built-in select repopulates its own choices, which prevents synthetic
         * choices like "All items" from reliably displaying in the admin dropdown.
         */
        $itemSetOptions = [
            '' => 'Select item set…',
            self::ITEM_SET_ALL_ITEMS => 'All items',
            self::ITEM_SET_ALL_ITEM_SETS => 'All item sets',
        ];

        try {
            $itemSetsResponse = $this->api->search('item_sets', [
                'sort_by' => 'title',
                'sort_order' => 'asc',
                'limit' => 0,
            ]);

            $itemSets = $itemSetsResponse->getContent();

            foreach ($itemSets as $itemSet) {
                $itemSetOptions[(string) $itemSet->id()] = $itemSet->displayTitle();
            }
        } catch (\Exception $e) {
            $errors[] = 'Unable to load item sets: ' . $e->getMessage();
        }

        $layoutForm->add([
            'name' => 'o:block[__blockIndex__][o:data][selectedItemSet]',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Item Set', // @translate
                'value_options' => $itemSetOptions,
            ],
            'attributes' => [
                'value' => $data['selectedItemSet'] ?? '',
                'required' => true,
                'data-column-data-key' => 'item_set_id',
                'class' => 'chosen-select',
            ],
        ]);

        /*
         * Build property options manually instead of using OmekaElement\PropertySelect.
         * The first synthetic option lets the frontend template use fulltext_search.
         */
        $propertyOptions = [
            '' => 'Select property…',
            self::PROPERTY_ALL_PROPERTIES => 'All properties',
        ];

        try {
            $propertiesResponse = $this->api->search('properties', [
                'sort_by' => 'label',
                'sort_order' => 'asc',
                'limit' => 0,
            ]);

            $properties = $propertiesResponse->getContent();

            foreach ($properties as $property) {
                $propertyOptions[(string) $property->id()] = sprintf(
                    '%s: %s',
                    $property->vocabulary()->label(),
                    $property->label()
                );
            }
        } catch (\Exception $e) {
            $errors[] = 'Unable to load properties: ' . $e->getMessage();
        }

        $layoutForm->add([
            'name' => 'o:block[__blockIndex__][o:data][searchField]',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Property', // @translate
                'value_options' => $propertyOptions,
            ],
            'attributes' => [
                'value' => $data['searchField'] ?? '',
                'required' => true,
                'data-column-data-key' => 'searchField',
                'class' => 'chosen-select',
            ],
        ]);

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