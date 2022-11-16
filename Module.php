<?php 

namespace LessonPlans;

use Omeka\Module\AbstractModule;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\EventManager\Event;
use AdvancedSearch\Indexer\IndexerInterface;
use Omeka\Entity\Resource;
use Omeka\Stdlib\Message;
use Laminas\EventManager\Event as ZendEvent;
use Laminas\Mvc\MvcEvent;
use Composer\Semver\Comparator;

class Module extends AbstractModule
{

    /**
     * @var bool
     */
    protected $isBatchUpdate;
     
    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        
        $acl->allow(
            'editor',
            [
                'LessonPlans\Controller\Admin\LessonPlan'
            ],
            [
                'index',
                'browse',
                'show',
                'show-details',
            ]
        );
        $acl->allow(
            'editor',
            [
                'LessonPlans\Controller\Admin\LessonPlan'
            ],
            ['sidebar-select', 'search']
        );
        $acl->allow(
            'editor',
            [
                'LessonPlans\Controller\Admin\LessonPlan',
                'LessonPlans\Api\Adapter\LessonPlanSettingsAdapter'
            ],
            [
                'add',
                'edit',
                'delete',
                'delete-confirm',
                'read',
                'search'
            ]
        );
        $acl->allow(
            null,
            [
               
                'LessonPlans\Api\Adapter\LessonPlanSettingsAdapter'
            ],
            [
                
                'read',
                'search'
            ]
        );
        $acl->allow(
            'editor',
            [
                'LessonPlans\Api\Adapter\LessonPlanSettingsAdapter',
                //'Omeka\Api\Adapter\SiteAdapter',
            ],
            [
                'create',
                'update',
                'delete',
            ]
        );
        $acl->allow(
            'editor',
            [
                'LessonPlans\Api\Adapter\LessonPlanSettingsAdapter'
            ],
            [
                'batch_update',
                'batch_delete',
            ]
        );
    }
    /**
     * Get this module's configuration array.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $services)
    {
        $connection = $services->get('Omeka\Connection');
        //$connection->exec('CREATE TABLE lesson_plan_settings (id INT AUTO_INCREMENT NOT NULL, item_set_id INT DEFAULT NULL, site_id INT DEFAULT NULL, INDEX IDX_3F0C845D960278D7 (item_set_id), UNIQUE INDEX UNIQ_3F0C845DF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        //$connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845D960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id)');
        //$connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845DF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');

        $connection->exec('CREATE TABLE lesson_plan_settings (id INT AUTO_INCREMENT NOT NULL, item_set_id INT DEFAULT NULL, site_id INT DEFAULT NULL, resource_template_id INT DEFAULT NULL, property_id INT DEFAULT NULL, INDEX IDX_3F0C845D960278D7 (item_set_id), UNIQUE INDEX UNIQ_3F0C845DF6BD1646 (site_id), UNIQUE INDEX UNIQ_3F0C845D549213EC (property_id), UNIQUE INDEX UNIQ_3F0C845D16131EA (resource_template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845D960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id)');
        $connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845DF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845D549213EC FOREIGN KEY (property_id) REFERENCES property (id)');
        $connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845D16131EA FOREIGN KEY (resource_template_id) REFERENCES resource_template (id)');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $services)
    {
        $connection = $services->get('Omeka\Connection');
        if (Comparator::lessThan($oldVersion, '1.1')) { 
            $sql = 'ALTER TABLE lesson_plan_settings ADD COLUMN resource_template_id INT;
            ALTER TABLE lesson_plan_settings ADD property_id INT;'; // your upgrade SQL statements 
            $connection->exec($sql);
            $connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845D549213EC FOREIGN KEY (property_id) REFERENCES property (id)');
            $connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845D16131EA FOREIGN KEY (resource_template_id) REFERENCES resource_template (id)');
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        
        $sharedEventManager->attach(
            \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
            'api.create.post',
            [$this, 'updateSearchEngine']
        );
        $sharedEventManager->attach(
            \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
            'api.batch_update.pre',
            [$this, 'preBatchUpdateSearchEngine']
        );
        // $sharedEventManager->attach(
        //     \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
        //     'api.batch_update.post',
        //     [$this, 'postBatchUpdateSearchEngine']
        // );
        $sharedEventManager->attach(
            \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
            'api.update.post',
            [$this, 'updateSearchEngine']
        );
        $sharedEventManager->attach(
            \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
            'api.delete.post',
            [$this, 'updateSearchEngine']
        );

        $sharedEventManager->attach(
            \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
            'api.create.post',
            [$this, 'updateSearchEngine']
        );
        $sharedEventManager->attach(
            \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
            'api.batch_update.pre',
            [$this, 'preBatchUpdateSearchEngine']
        );
        // $sharedEventManager->attach(
        //     \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
        //     'api.batch_update.post',
        //     [$this, 'postBatchUpdateSearchEngine']
        // );
        $sharedEventManager->attach(
            \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
            'api.update.post',
            [$this, 'updateSearchEngine']
        );
        $sharedEventManager->attach(
            \LessonPlans\Api\Adapter\LessonPlanAdapter::class,
            'api.delete.post',
            [$this, 'updateSearchEngine']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Adapter\SiteAdapter',
            'UserIsAllowed',
            [$this, 'navigationPageIsAllowed']
        );
    }

    /**
     * Determine whether a navigation page is allowed.
     *
     * @param ZendEvent $event
     * @return bool
     */
    public function navigationPageIsAllowed(ZendEvent $event)
    {
        $accepted = true;
        $params = $event->getParams();
        $acl = $params['acl'];
        $page = $params['page'];
        var_dump($acl);
        exit;
        if (!$acl) {
            return $accepted;
        }

        $resource = $page->getResource();
        $privilege = $page->getPrivilege();
        
        if ($resource || $privilege) {
            $accepted = $acl->hasResource($resource)
                && $acl->userIsAllowed($resource, $privilege);
        }

        $event->stopPropagation();
        return $accepted;
    }

    public function preBatchUpdateSearchEngine(Event $event): void
    {
        // This is a background job if there is no route match.
        $routeMatch = $this->getServiceLocator()->get('application')->getMvcEvent()->getRouteMatch();
        $this->isBatchUpdate = !empty($routeMatch);
    }

    public function postBatchUpdateSearchEngine(Event $event): void
    {
        if (!$this->isBatchUpdate) {
            return;
        }

        $serviceLocator = $this->getServiceLocator();
        $api = $serviceLocator->get('Omeka\ApiManager');

        $request = $event->getParam('request');
        $requestResource = $request->getResource();
        $response = $event->getParam('response');
        $resources = $response->getContent();
        if($requestResource == "lesson-plans")
        {
            $requestResource = "items";
        }
        /** @var \AdvancedSearch\Api\Representation\SearchEngineRepresentation[] $searchEngines */
        $searchEngines = $api->search('search_engines')->getContent();
        foreach ($searchEngines as $searchEngine) {
            if (in_array($requestResource, $searchEngine->setting('resources', []))) {
                $indexer = $searchEngine->indexer();
                try {
                    $indexer->indexResources($resources);
                } catch (\Exception $e) {
                    $services = $this->getServiceLocator();
                    $logger = $services->get('Omeka\Logger');
                    $logger->err(new Message(
                        'Unable to batch index metadata for search engine "%s": %s', // @translate
                        $searchEngine->name(), $e->getMessage()
                    ));
                    $messenger = $services->get('ControllerPluginManager')->get('messenger');
                    $messenger->addWarning(new Message(
                        'Unable to batch update the search engine "%s": see log.', // @translate
                        $searchEngine->name()
                    ));
                }
            }
        }

        $this->isBatchUpdate = false;
    }

    public function updateSearchEngine(Event $event): void
    {
        if ($this->isBatchUpdate) {
            return;
        }
        $serviceLocator = $this->getServiceLocator();
        $api = $serviceLocator->get('Omeka\ApiManager');

        $request = $event->getParam('request');
        $response = $event->getParam('response');
        $requestResource = $request->getResource();
        if($requestResource == "lesson-plans")
        {
            $requestResource = "items";
        }
        /** @var \AdvancedSearch\Api\Representation\SearchEngineRepresentation[] $searchEngines */
        $searchEngines = $api->search('search_engines')->getContent();
        foreach ($searchEngines as $searchEngine) {
            if (in_array($requestResource, $searchEngine->setting('resources', []))) {
                $indexer = $searchEngine->indexer();
                if ($request->getOperation() == 'delete') {
                    $id = $request->getId();
                    $this->deleteIndexResource($indexer, $requestResource, $id);
                } else {
                    $resource = $response->getContent();
                    $this->updateIndexResource($indexer, $resource);
                }
            }
        }
    }

    /**
     * Update the index in search engine for a resource.
     *
     * @param IndexerInterface $indexer
     * @param Resource $resource
     */
    protected function updateIndexResource(IndexerInterface $indexer, Resource $resource): void
    {
        try {
            $indexer->indexResource($resource);
        } catch (\Exception $e) {
            $services = $this->getServiceLocator();
            $logger = $services->get('Omeka\Logger');
            $logger->err(new Message(
                'Unable to index metadata of resource #%d for search: %s', // @translate
                $resource->getId(), $e->getMessage()
            ));
            $messenger = $services->get('ControllerPluginManager')->get('messenger');
            $messenger->addWarning(new Message(
                'Unable to update the search index for resource #%d: see log.', // @translate
                $resource->getId()
            ));
        }
    }

    /**
     * Delete the index for the resource in search engine.
     *
     * @param IndexerInterface $indexer
     * @param string $resourceName
     * @param int $id
     */
    protected function deleteIndexResource(IndexerInterface $indexer, $resourceName, $id): void
    {
        try {
            $indexer->deleteResource($resourceName, $id);
        } catch (\Exception $e) {
            $services = $this->getServiceLocator();
            $logger = $services->get('Omeka\Logger');
            $logger->err(new Message(
                'Unable to delete the search index for resource #%d: %s', // @translate
                $id, $e->getMessage()
            ));
            $messenger = $services->get('ControllerPluginManager')->get('messenger');
            $messenger->addWarning(new Message(
                'Unable to delete the search index for the deleted resource #%d: see log.', // @translate
                $id
            ));
        }
    }

}
