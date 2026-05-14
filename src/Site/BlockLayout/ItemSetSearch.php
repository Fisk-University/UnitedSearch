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
    /**
     * Synthetic option for searching all items.
     */
    const ITEM_SET_ALL_ITEMS = '__all_items__';

    /**
     * Synthetic option for searching item sets instead of items.
     */
    const ITEM_SET_ALL_ITEM_SETS = '__all_item_sets__';

    /**
     * Synthetic option for searching across all item properties.
     */
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

        try {
            $itemSetSelect = $this->formElements->get(OmekaElement\ItemSetSelect::class);
            $itemSetSelect->setName('o:block[__blockIndex__][o:data][selectedItemSet]');
            $itemSetSelect->setOptions([
                'label' => 'Item Set', // @translate
                'empty_option' => 'Select item set…', // @translate
                'term_as_value' => false,
            ]);
            $itemSetSelect->setAttributes([
                'value' => $data['selectedItemSet'] ?? null,
                'required' => true,
                'data-column-data-key' => 'item_set_id',
                'class' => 'chosen-select',
            ]);

            $layoutForm->add($itemSetSelect);

            /*
             * Add synthetic admin options after Omeka builds the normal item set list.
             *
             * All items:
             * - Searches all items.
             * - Does not submit item_set_id.
             *
             * All item sets:
             * - Routes to the item-set browse page instead of the item browse page.
             */
            $itemSetElement = $layoutForm->get('o:block[__blockIndex__][o:data][selectedItemSet]');
            $itemSetValueOptions = $itemSetElement->getValueOptions();

            $itemSetElement->setValueOptions(
                [
                    self::ITEM_SET_ALL_ITEMS => 'All items', // @translate
                    self::ITEM_SET_ALL_ITEM_SETS => 'All item sets', // @translate
                ] + $itemSetValueOptions
            );
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        try {
            $propertySelect = $this->formElements->get(OmekaElement\PropertySelect::class);
            $propertySelect->setName('o:block[__blockIndex__][o:data][searchField]');
            $propertySelect->setOptions([
                'label' => 'Property', // @translate
                'empty_option' => 'Select property…', // @translate
                'term_as_value' => false,
            ]);
            $propertySelect->setAttributes([
                'value' => $data['searchField'] ?? null,
                'required' => true,
                'data-column-data-key' => 'searchField',
                'class' => 'chosen-select',
            ]);

            $layoutForm->add($propertySelect);

            /*
             * Add synthetic admin option after Omeka builds the normal property list.
             *
             * All properties:
             * - Uses Omeka's fulltext_search parameter.
             * - This searches across indexed item metadata instead of one selected property.
             */
            $propertyElement = $layoutForm->get('o:block[__blockIndex__][o:data][searchField]');
            $propertyValueOptions = $propertyElement->getValueOptions();

            $propertyElement->setValueOptions(
                [
                    self::PROPERTY_ALL_PROPERTIES => 'All properties', // @translate
                ] + $propertyValueOptions
            );
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        $layoutForm->add([
            'name' => 'o:block[__blockIndex__][o:data][fieldPlaceholder]',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Placeholder', // @translate
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