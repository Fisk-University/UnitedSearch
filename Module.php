<?php

namespace UnitedSearch;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Module\AbstractModule;

class Module extends AbstractModule implements BootstrapListenerInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(EventInterface $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $sharedManager = $eventManager->getSharedManager();
        $this->attachListeners($sharedManager);
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        // ✅ Restrict to public site item browse page
        $sharedEventManager->attach(
            'Omeka\Controller\Site\ItemController',
            'view.browse.before',
            [$this, 'injectUnitedSearchForm']
        );
    }

    public function injectUnitedSearchForm($event): void
    {
        /** @var PhpRenderer $view */
        $view = $event->getTarget();

        // 🛑 Extra safeguard: avoid injecting on admin accidentally
        if (!method_exists($view, 'site') || !$view->site) {
            return;
        }

        error_log("🔥 DualPropertySearch injected via view.browse.before");

        echo $view->partial('common/block-layout/dualproperty-search', [
            'propertyOne' => 'rsf2:state',
            'propertyTwo' => 'rsf2:county',
            'joinType' => 'and',
        ]);
    }
}
