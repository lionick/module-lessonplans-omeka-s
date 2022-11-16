<?php declare(strict_types=1);

/*
 * Copyright BibLibre, 2017
 * Copyright Daniel Berthereau, 2017-2021
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software.  You can use, modify and/ or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software's author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user's attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software's suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace LessonPlans\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class LessonPlanSettingsAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'site' => 'solrCore',
        'item_set' => 'itemSet',
    ];

    public function getResourceName()
    {
        return 'lesson_plan_settings';
    }

    public function getRepresentationClass()
    {
        return \LessonPlans\Api\Representation\LessonPlanSettingsRepresentation::class;
    }

    public function getEntityClass()
    {
        return \LessonPlans\Entity\LessonPlanSettings::class;
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ): void {
        if ($this->shouldHydrate($request, 'o:item_set_id')) {
            $itemSetAdapter = $this->getAdapter('item_sets');
       
            if(is_numeric($request->getValue('o:item_set_id'))) {
                $itemSet = $itemSetAdapter->findEntity($request->getValue('o:item_set_id'));
                $entity->setItemSet($itemSet);               
            }
            
        }
        if($this->shouldHydrate($request, 'o:site')){
            $sitesAdapter = $this->getAdapter('sites');
            $site = $sitesAdapter->findEntity($request->getValue('o:site'));
            $entity->setSite($site);
        }
        if($this->shouldHydrate($request, 'o:resource_template_id')){
            $resourceAdapter = $this->getAdapter('resource_templates');
            if(is_numeric($request->getValue('o:resource_template_id'))) {
                $resource_template = $resourceAdapter->findEntity($request->getValue('o:resource_template_id'));
                $entity->setResourceTemplate($resource_template);
            }
        }
        if($this->shouldHydrate($request, 'o:property_id')){
            $propertyAdapter = $this->getAdapter('properties');
            if(is_numeric($request->getValue('o:property_id'))) {        
                $property = $propertyAdapter->findEntity($request->getValue('o:property_id'));
                $entity->setProperty($property);
            }
        }

    }

    public function buildQuery(QueryBuilder $qb, array $query): void
    {
        parent::buildQuery($qb, $query);
        
        if (isset($query['site_id']) && is_numeric($query['site_id'])) {
            $siteAlias = $this->createAlias();
            $qb->innerJoin(
                'omeka_root.site', $siteAlias, 'WITH', $qb->expr()->eq(
                    "$siteAlias.id",
                    $this->createNamedParameter($qb, $query['site_id']))
                
            );
        }
        // if (isset($query['resource_name'])) {
        //     $qb->andWhere($expr->eq(
        //         'omeka_root.resourceName',
        //         $this->createNamedParameter($qb, $query['resource_name'])
        //     ));
        // }
    }


}
