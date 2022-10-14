<?php
namespace LessonPlans\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

// Create an action controller.
class IndexController extends AbstractActionController
{
    // Define an action "index".
    public function indexAction()
    {
        // Get "message" from the query parameters.
        // In production code, it's a good idea to sanitize user input.
        $message = $this->params()->fromQuery('message', 'hello');

        // Pass variables to the view.
        return new ViewModel(['message' => $message]);
    }
}