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

    public function install(ServiceLocatorInterface $services)
    {
        $connection = $services->get('Omeka\Connection');
        $connection->exec('CREATE TABLE lesson_plan_settings (id INT AUTO_INCREMENT NOT NULL, item_set_id INT DEFAULT NULL, site_id INT DEFAULT NULL, INDEX IDX_3F0C845D960278D7 (item_set_id), UNIQUE INDEX UNIQ_3F0C845DF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845D960278D7 FOREIGN KEY (item_set_id) REFERENCES item_set (id)');
        $connection->exec('ALTER TABLE lesson_plan_settings ADD CONSTRAINT FK_3F0C845DF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
    }

}
