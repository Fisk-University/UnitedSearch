<?php
namespace UnitedSearch\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;

class DualPropertySearchForm extends AbstractHelper
{
    public function __invoke(array $data = [])
    {
        $view = $this->getView();

        // 🎯 Pull from block if available
        $propertyOne = null;
        $propertyTwo = null;
        $joinType = null;

        if (isset($view->block) && method_exists($view->block, 'data')) {
            $blockData = $view->block->data();
            $propertyOne = $blockData['propertyOne'] ?? null;
            $propertyTwo = $blockData['propertyTwo'] ?? null;
            $joinType    = $blockData['joinType']    ?? null;
            error_log("✅ DualPropertySearchForm: Pulled from block data");
        } else {
            $propertyOne = $data['propertyOne'] ?? null;
            $propertyTwo = $data['propertyTwo'] ?? null;
            $joinType    = $data['joinType']    ?? null;
            error_log("✅ DualPropertySearchForm: Pulled from manual fallback");
        }

        if (!$propertyOne || !$propertyTwo || !$joinType) {
            error_log("⚠️ DualPropertySearchForm: Missing config — propertyOne: $propertyOne, propertyTwo: $propertyTwo, joinType: $joinType");
            return '';
        }

        return $view->partial('common/block-layout/dualproperty-search', [
            'propertyOne' => $propertyOne,
            'propertyTwo' => $propertyTwo,
            'joinType'    => $joinType,
        ]);
    }
}
