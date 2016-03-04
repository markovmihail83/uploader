<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\Base;


use Doctrine\ORM\EntityManagerInterface;
use ExampleApp\Entity\ORM\UploadableEntity;
use ExampleApp\Exception\ObjectNotFoundException;

abstract class ORMCommand extends Command
{
    /**
     * @var null|EntityManagerInterface
     */
    protected $em;

    protected function registerDriver()
    {
        $this->em = require __DIR__ . '/../../Resources/config/orm/bootstrap.php';
        $this->em->getEventManager()->addEventSubscriber($this->container->getOrmListener());
    }

    /**
     * @param        $entityClass
     * @param string $driver
     *
     * @return UploadableEntity|object
     * @throws ObjectNotFoundException
     */
    protected function getEntity($entityClass = UploadableEntity::class, $driver = 'orm')
    {
        $id = $this->getId();

        $entity = $this->em->find($entityClass, $id);

        if (null === $entity) {
            throw new ObjectNotFoundException($id, $driver);
        }

        return $entity;
    }

    protected function view($id = null, array $fileReference = null)
    {
        $this->em->flush();

        parent::view($id, $fileReference);
    }
}