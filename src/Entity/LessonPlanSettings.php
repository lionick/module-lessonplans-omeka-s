<?php
namespace LessonPlans\Entity;

use Omeka\Entity\AbstractEntity;


/**
 * @Entity
 * 
 */
class LessonPlanSettings extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\ItemSet",
     * 
     * cascade={"persist", "remove"}
     * )
     * 
     */
    protected $item_set;

    /**
     * @OneToOne(targetEntity="Omeka\Entity\Site",
     * cascade={"persist", "remove"}
     * )
     * 
     */
    protected $site;

    /**
     * @OneToOne(targetEntity="Omeka\Entity\ResourceTemplate",
     * cascade={"persist", "remove"}
     * )
     * 
     */
    protected $resource_template;

    /**
     * @OneToOne(targetEntity="Omeka\Entity\Property",
     * cascade={"persist", "remove"}
     * )
     * 
     */
    protected $property;

    public function setResourceTemplate($resource_template)
    {
        $this->resource_template = $resource_template;
    }

    public function getResourceTemplate()
    {
        return $this->resource_template;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    public function getProperty()
    {
        return $this->property;
    }

    public function setSite($site)
    {
        $this->site = $site;
    }

    public function setItemSet($item_set)
    {
        $this->item_set = $item_set;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getItemSet()
    {
        return $this->item_set;
    }

    public function getId()
    {
        return $this->id;
    }


}
