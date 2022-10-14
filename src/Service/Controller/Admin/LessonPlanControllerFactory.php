<?php
namespace LessonPlans\Service\Controller\Admin;

use Interop\Container\ContainerInterface;
use LessonPlans\Controller\Admin\LessonPlanController;

use Laminas\ServiceManager\Factory\FactoryInterface;

class LessonPlanControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new LessonPlanController(
            $services->get('Omeka\Media\Ingester\Manager')
        );
    }
}
