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
