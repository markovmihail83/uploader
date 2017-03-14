<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\ODM;

use ExampleApp\Command\Base\ODMCommand;
use ExampleApp\Document\ODM\UploadableDocument;

class UploadCommand extends ODMCommand
{
    protected function doConfigure()
    {
        $this->addFileArgument();
    }

    protected function doExecute()
    {
        $entity = new UploadableDocument($this->getFile());
        $this->em->persist($entity);
        $this->em->flush();
        $this->view($entity->getId(), $entity->toArray());
    }
}
