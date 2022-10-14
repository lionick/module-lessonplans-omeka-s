<?php 

namespace LessonPlans;

use Omeka\Module\AbstractModule;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractController;

class Module extends AbstractModule
{
     
    /**
     * Get this module's configuration array.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

}
