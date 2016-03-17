<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Command\ORM;

use ExampleApp\Command\Base\ORMCommand;
use ExampleApp\Entity\ORM\UploadableEntity;

class UploadCommand extends ORMCommand
{
    protected function doConfigure()
    {
        $this->addFileArgument();
    }

    protected function doExecute()
    {
        $entity = new UploadableEntity($this->getFile());
        $this->em->persist($entity);
        $this->em->flush();
        $this->view($entity->getId(), $entity->toArray());
    }
}
