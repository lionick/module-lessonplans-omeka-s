<?php
namespace LessonPlans\Service\ViewHelper;

use LessonPlans\View\Helper\DeleteConfirm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class DeleteConfirmFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new DeleteConfirm($services->get('FormElementManager'));
    }
}
