<?php

namespace LessonPlans\Service\ViewHelper;

use LessonPlan\View\Helper\LessonPlanMedia;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * Service factory for the media view helper.
 */
class LessonPlanMediaFactory implements FactoryInterface
{
    /**
     * Create and return the media view helper
     *
     * @param ContainerInterface $viewServiceLocator
     * @return LessonPlanMedia
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new LessonPlanMedia(
            $services->get('Omeka\Media\Ingester\Manager'),
            $services->get('Omeka\Media\Renderer\Manager')
        );
    }
}
