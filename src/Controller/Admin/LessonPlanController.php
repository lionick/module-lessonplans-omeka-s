<?php
namespace LessonPlans\Controller\Admin;

use Omeka\Form\ConfirmForm;
use Omeka\Form\ResourceForm;
use Omeka\Form\ResourceBatchUpdateForm;
use Omeka\Media\Ingester\Manager;
use Omeka\Stdlib\Message;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Api\Exception as ApiException;

class LessonPlanController extends AbstractActionController
{
    /**
     * @var Manager
     */
    protected $mediaIngesters;

    /**
     * @param Manager $mediaIngesters
     */
    public function __construct(Manager $mediaIngesters)
    {
        $this->mediaIngesters = $mediaIngesters;
    }

    public function configureAction()
    {
        $form = $this->getForm(ResourceForm::class);
        $form->setAttribute('action', $this->url()->fromRoute(null, [], true));
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('id', 'configure-lesson-plan');

        $site = $this->currentSite();
        try {
            $configuration = $this->api()->search('lesson-plan-settings', [ 'site_id' => $site->id()])->getContent();
        } catch (ApiException\NotFoundException $e) {
            // do nothing
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            // Prevent the API from setting sites automatically if no sites are set.        
            $data['o:item_set_id'] = $data['item_set_id'] ?? [];
            $data['o:resource_template_id'] = $data['resource_template_id'] ?? [];
            $data['o:property_id'] = $data['property_id'] ?? [];
            $data['o:site'] = $site->id() ?? [];
            $form->setData($data);
            if ($form->isValid()) {
                
                if(empty($configuration))
                    $response = $this->api($form)->create('lesson-plan-settings', $data);
                else {     
                    $response = $this->api($form)->update('lesson-plan-settings', $configuration[0]->id(), $data);
                }

                if ($response) {
                    $message = new Message(
                        'Configuration saved successfully');
                    $message->setEscapeHtml(false);
                    $this->messenger()->addSuccess($message);
                    $site = $this->currentSite();

                    $view = new ViewModel;
                    $view->setVariable('form', $form);
                    $view->setVariable('site', $this->currentSite());
                    $view->setVariable('itemSets', $this->api()->search('item_sets')->getContent());
                    $view->setVariable('resourceTemplates', $this->api()->search('resource_templates')->getContent());
                    //$view->setVariable('properties', $this->api()->search('properties', ['resource_template_id' => 8])->getContent());
                    if(empty($configuration))  { //for 1st time save
                        $view->setVariable('item_set_id', $data['o:item_set_id']);
                        $view->setVariable('resource_template_id', $data['o:resource_template_id']);
                        $view->setVariable('property_id', $data['o:property_id']);
                    }
                    else {
                        $view->setVariable('item_set_id', $configuration[0]->getJsonLd()["o:item_set_id"]->getId());
                        if(!empty($configuration[0]->getJsonLd()["o:resource_template_id"])){
                            $view->setVariable('resource_template_id', $configuration[0]->getJsonLd()["o:resource_template_id"]->getId());
                            
                        }
                        if(!empty($configuration[0]->getJsonLd()["o:property_id"])){
                            $view->setVariable('property_id', $configuration[0]->getJsonLd()["o:property_id"]->getId());
                        }
                    }
                    return $view;

                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('site', $this->currentSite());
        $view->setVariable('itemSets', $this->api()->search('item_sets')->getContent());
        $view->setVariable('resourceTemplates', $this->api()->search('resource_templates')->getContent());
        $view->setVariable('blabla', $this->api()->search('resource_templates', ['label'=>'Lesson Plan'])->getContent());
        //$view->setVariable('properties', $this->api()->search('properties', ['resource_template_id' => 8])->getContent());
        if(!empty($configuration)) {
            if(!empty($configuration[0]->getJsonLd()["o:resource_template_id"])){
                $view->setVariable('resource_template_id', $configuration[0]->getJsonLd()["o:resource_template_id"]->getId());
                //$view->setVariable('properties',$configuration[0]->getJsonLd()["o:resource_template_id"]->getResourceTemplateProperties());
            }
            if(!empty($configuration[0]->getJsonLd()["o:property_id"])){
                $view->setVariable('property_id', $configuration[0]->getJsonLd()["o:property_id"]->getId());
            }
            $view->setVariable('item_set_id', $configuration[0]->getJsonLd()["o:item_set_id"]->getId());
        }
        return $view;
    }

    public function searchAction()
    {
        $view = new ViewModel;
        $view->setVariable('query', $this->params()->fromQuery());
        return $view;
    }

    public function browseAction()
    {
       
        $this->setBrowseDefaults('created');

        try {
            $configuration = $this->api()->search('lesson-plan-settings', [ 'site_id' => $this->currentSite()->id()])->getContent();        
            //var_dump($data['o:site']);
        } catch (ApiException\NotFoundException $e) {
            // do nothing
        }
        if(empty($configuration)) {
            $message = new Message(
                'No configuration found. Please configure first the module.');
            $message->setEscapeHtml(false);
            $this->messenger()->addWarning($message);

            $view = new ViewModel;
            $view->setVariable('site', $this->currentSite());
            $view->setVariable('items', array());
            $view->setVariable('resources', array());
            return $view;
        }
        $itemSetId = $configuration[0]->getJsonLd()["o:item_set_id"]->getId();
        $response = $this->api()->search('items', array("item_set_id"=> $itemSetId, "site_id"=> $this->currentSite()->id()) + $this->params()->fromQuery());
        $this->paginator($response->getTotalResults());

        $formDeleteSelected = $this->getForm(ConfirmForm::class);
        $formDeleteSelected->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'batch-delete'], true));
        $formDeleteSelected->setButtonLabel('Confirm Delete'); // @translate
        $formDeleteSelected->setAttribute('id', 'confirm-delete-selected');

        $formDeleteAll = $this->getForm(ConfirmForm::class);
        $formDeleteAll->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'batch-delete-all'], true));
        $formDeleteAll->setButtonLabel('Confirm Delete'); // @translate
        $formDeleteAll->setAttribute('id', 'confirm-delete-all');
        $formDeleteAll->get('submit')->setAttribute('disabled', true);

        $view = new ViewModel;
        $items = $response->getContent();
        $view->setVariable('items', $items);
        $view->setVariable('resources', $items);
        $view->setVariable('formDeleteSelected', $formDeleteSelected);
        $view->setVariable('formDeleteAll', $formDeleteAll);
        $view->setVariable('site', $this->currentSite());
        return $view;
    }

    public function showAction()
    {
        $response = $this->api()->read('items', $this->params('id'));

        $view = new ViewModel;
        $item = $response->getContent();
        $view->setVariable('item', $item);
        $view->setVariable('resource', $item);
        return $view;
    }

    public function showDetailsAction()
    {
        $linkTitle = (bool) $this->params()->fromQuery('link-title', true);
        $response = $this->api()->read('items', $this->params('id'));
        $item = $response->getContent();
        $values = $item->valueRepresentation();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('linkTitle', $linkTitle);
        $view->setVariable('resource', $item);
        $view->setVariable('values', json_encode($values));
        return $view;
    }

    public function sidebarSelectAction()
    {
        $this->setBrowseDefaults('created');
        $response = $this->api()->search('items', $this->params()->fromQuery());
        $this->paginator($response->getTotalResults());

        $view = new ViewModel;
        $view->setVariable('items', $response->getContent());
        $view->setVariable('search', $this->params()->fromQuery('search'));
        $view->setVariable('resourceClassId', $this->params()->fromQuery('resource_class_id'));
        $view->setVariable('itemSetId', $this->params()->fromQuery('item_set_id'));
        $view->setVariable('id', $this->params()->fromQuery('id'));
        $view->setVariable('showDetails', true);
        $view->setTerminal(true);
        return $view;
    }

    public function deleteConfirmAction()
    {
        $linkTitle = (bool) $this->params()->fromQuery('link-title', true);
        $response = $this->api()->read('items', $this->params('id'));
        $item = $response->getContent();
        $values = $item->valueRepresentation();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $item);
        $view->setVariable('resourceLabel', 'item'); // @translate
        $view->setVariable('partialPath', 'omeka/admin/item/show-details');
        $view->setVariable('linkTitle', $linkTitle);
        $view->setVariable('item', $item);
        $view->setVariable('values', json_encode($values));
        $view->setVariable('site', $this->currentSite());

        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('lesson-plans', $this->params('id'));
                if ($response) {
                    $this->messenger()->addSuccess('Lesson plan successfully deleted'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        $site = $this->currentSite();
        return $this->redirect()->toRoute(
            'admin/site/slug/lesson-plan/action',
            ['action' =>  'browse', 'site-slug' => $site->slug()]
        );
        //return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        // return $this->redirect()->toRoute(
        //     'admin/default',
        //     ['action' => 'browse'],
        //     true
        // );
    }

    public function batchDeleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one item to batch delete.'); // @translate
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $response = $this->api($form)->batchDelete('items', $resourceIds, [], ['continueOnError' => true]);
            if ($response) {
                $this->messenger()->addSuccess('Items successfully deleted'); // @translate
            }
        } else {
            $this->messenger()->addFormErrors($form);
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function batchDeleteAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        // Derive the query, removing limiting and sorting params.
        $query = json_decode($this->params()->fromPost('query', []), true);
        unset($query['submit'], $query['page'], $query['per_page'], $query['limit'],
            $query['offset'], $query['sort_by'], $query['sort_order']);

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $job = $this->jobDispatcher()->dispatch('Omeka\Job\BatchDelete', [
                'resource' => 'items',
                'query' => $query,
            ]);
            $this->messenger()->addSuccess('Deleting items. This may take a while.'); // @translate
        } else {
            $this->messenger()->addFormErrors($form);
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function addAction()
    {
        
        $form = $this->getForm(ResourceForm::class);
        $form->setAttribute('action', $this->url()->fromRoute(null, [], true));
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('id', 'add-item');
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data = $this->mergeValuesJson($data);
            // Prevent the API from setting sites automatically if no sites are set.
            $data['o:site'] = $data['o:site'] ?? [];
            $form->setData($data);
            if ($form->isValid()) {
                $fileData = $this->getRequest()->getFiles()->toArray();
                $response = $this->api($form)->create('lesson-plans', $data, $fileData);

                if ($response) {
                    $message = new Message(
                        'Lesson Plan successfully created. %s', // @translate
                        sprintf(
                            '<a href="%s">%s</a>',
                            htmlspecialchars($this->url()->fromRoute(null, [], true)),
                            $this->translate('Add another lesson plan?') 
                        ));
                    $message->setEscapeHtml(false);
                    $this->messenger()->addSuccess($message);
                    $site = $this->currentSite();

                    //return ViewModel('site' => $site)
                    return $this->redirect()->toRoute(
                        'admin/site/slug/lesson-plan/lesson_plan-id',
                        ['id' =>  $response->getContent()->id(), 'site-slug' => $site->slug()]
                    );
                    //return $this->redirect()->toUrl($response->getContent()->url());
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $site = $this->currentSite();
        $view->setVariable('site', $site);
        $view->setVariable('mediaForms', $this->getMediaForms());
        try {
            $configuration = $this->api()->search('lesson-plan-settings', [ 'site_id' => $site->id()])->getContent();
            
        } catch (ApiException\NotFoundException $e) {
            // do nothing

        }
        if(!empty($configuration)){
            $view->setVariable('item_set_default', $configuration[0]->getJsonLd()["o:item_set_id"]);
            $view->setVariable('resource_template_default', $configuration[0]->getJsonLd()["o:resource_template_id"]);
            if(!empty($configuration[0]->getJsonLd()["o:property_id"])) {
                $view->setVariable('property_default', $configuration[0]->getJsonLd()["o:property_id"]);
                $property = $this->api()->search('properties', 
                    [ 'id' => $configuration[0]->getJsonLd()["o:property_id"]])->getContent();
                $view->setVariable('property_term_default', $property[0]->term());         
            }
            if(!empty($configuration[0]->getJsonLd()["o:resource_template_id"])){
                $resource_template = $this->api()->search('resource_templates', 
                    [ 'id' => $configuration[0]->getJsonLd()["o:resource_template_id"]])->getContent();
                $view->setVariable('resource_template_label_default',$resource_template[0]->label());
            }
        }
        return $view;
    }

    public function editAction()
    {
        $form = $this->getForm(ResourceForm::class);
        $form->setAttribute('action', $this->url()->fromRoute(null, [], true));
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('id', 'edit-item');
        $item = $this->api()->read('items', $this->params('id'))->getContent();

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data = $this->mergeValuesJson($data);
            $form->setData($data);
            if ($form->isValid()) {
                $fileData = $this->getRequest()->getFiles()->toArray();
                $response = $this->api($form)->update('lesson-plans', $this->params('id'), $data, $fileData);
                if ($response) {
                    $this->messenger()->addSuccess('Lesson Plan successfully updated'); // @translate
                    $site = $this->currentSite();
                    
                    return $this->redirect()->toRoute(
                        'admin/site/slug/lesson-plan/lesson_plan-id',
                        ['id' =>  $response->getContent()->id(), 'site-slug' => $site->slug()]);
                    //return $this->redirect()->toUrl($response->getContent()->url());
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $site = $this->currentSite();
        try {
            $configuration = $this->api()->search('lesson-plan-settings', [ 'site_id' => $site->id()])->getContent();
        } catch (ApiException\NotFoundException $e) {
            // do nothing
        }
        if(!empty($configuration)){
            $view->setVariable('item_set_default', $configuration[0]->getJsonLd()["o:item_set_id"]);
            $view->setVariable('resource_template_default', $configuration[0]->getJsonLd()["o:resource_template_id"]);
            if(!empty($configuration[0]->getJsonLd()["o:property_id"])) {
                $view->setVariable('property_default', $configuration[0]->getJsonLd()["o:property_id"]);
                $resource_template = $this->api()->search('resource_templates', 
                    [ 'id' => $configuration[0]->getJsonLd()["o:resource_template_id"]])->getContent();
                $view->setVariable('resource_template_label_default',$resource_template[0]->label());
                $property = $this->api()->search('properties', 
                    [ 'id' => $configuration[0]->getJsonLd()["o:property_id"]])->getContent();
                $view->setVariable('property_term_default', $property[0]->term());
                
            }
        }
        $view->setVariable('form', $form);
        $view->setVariable('item', $item);
        $view->setVariable('resource', $item);
        $view->setVariable('mediaForms', $this->getMediaForms());
        $view->setVariable('site', $this->currentSite());
        return $view;
    }

    /**
     * Batch update selected items.
     */
    public function batchEditAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one item to batch edit.'); // @translate
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $form = $this->getForm(ResourceBatchUpdateForm::class, ['resource_type' => 'item']);
        $form->setAttribute('id', 'batch-edit-item');
        if ($this->params()->fromPost('batch_update')) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->preprocessData();

                foreach ($data as $collectionAction => $properties) {
                    $this->api($form)->batchUpdate('items', $resourceIds, $properties, [
                        'continueOnError' => true,
                        'collectionAction' => $collectionAction,
                        'detachEntities' => false,
                    ]);
                }

                $this->messenger()->addSuccess('Items successfully edited'); // @translate
                return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $resources = [];
        foreach ($resourceIds as $resourceId) {
            $resources[] = $this->api()->read('items', $resourceId)->getContent();
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('resources', $resources);
        $view->setVariable('query', []);
        $view->setVariable('count', null);
        return $view;
    }

    /**
     * Batch update all items returned from a query.
     */
    public function batchEditAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        // Derive the query, removing limiting and sorting params.
        $query = json_decode($this->params()->fromPost('query', []), true);
        unset($query['submit'], $query['page'], $query['per_page'], $query['limit'],
            $query['offset'], $query['sort_by'], $query['sort_order']);
        $count = $this->api()->search('items', ['limit' => 0] + $query)->getTotalResults();

        $form = $this->getForm(ResourceBatchUpdateForm::class, ['resource_type' => 'item']);
        $form->setAttribute('id', 'batch-edit-item');
        if ($this->params()->fromPost('batch_update')) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->preprocessData();

                $job = $this->jobDispatcher()->dispatch('Omeka\Job\BatchUpdate', [
                    'resource' => 'items',
                    'query' => $query,
                    'data' => isset($data['replace']) ? $data['replace'] : [],
                    'data_remove' => isset($data['remove']) ? $data['remove'] : [],
                    'data_append' => isset($data['append']) ? $data['append'] : [],
                ]);

                $this->messenger()->addSuccess('Editing items. This may take a while.'); // @translate
                return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setTemplate('omeka/admin/item/batch-edit.phtml');
        $view->setVariable('form', $form);
        $view->setVariable('resources', []);
        $view->setVariable('query', $query);
        $view->setVariable('count', $count);
        return $view;
    }

    protected function getMediaForms()
    {
        $mediaHelper = $this->viewHelpers()->get('media');
        $forms = [];
        foreach ($this->mediaIngesters->getRegisteredNames() as $ingester) {
            $forms[$ingester] = [
                'label' => $this->mediaIngesters->get($ingester)->getLabel(),
                'form' => $mediaHelper->form($ingester),
            ];
        }
        return $forms;
    }
}
