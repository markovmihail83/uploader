<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Command\Base;

use ExampleApp\Entity\ORMEmbeddable\EntityHasEmbeddedFile;

abstract class ORMEmbeddableCommand extends ORMCommand
{
    final protected function registerDriver()
    {
        $this->em = require __DIR__.'/../../Resources/config/orm_embeddable/bootstrap.php';
        $this->em->getEventManager()->addEventSubscriber($this->container->getOrmEmbeddableListener());
    }

    /**
     * @param        $entityClass
     * @param string $driver
     *
     * @return EntityHasEmbeddedFile|object
     *
     * @throws \ExampleApp\Exception\ObjectNotFoundException
     */
    protected function getEntity($entityClass = EntityHasEmbeddedFile::class, $driver = 'orm_embeddable')
    {
        return parent::getEntity($entityClass, $driver);
    }
}
