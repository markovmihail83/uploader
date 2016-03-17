<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Command\ORMEmbeddable;

use Atom\Uploader\Model\Embeddable\FileReference;
use ExampleApp\Command\Base\ORMEmbeddableCommand;
use ExampleApp\Entity\ORMEmbeddable\EntityHasEmbeddedFile;

class UploadCommand extends ORMEmbeddableCommand
{
    protected function doConfigure()
    {
        $this->addFileArgument();
    }

    protected function doExecute()
    {
        $fileReference = new FileReference($this->getFile());
        $entity = new EntityHasEmbeddedFile($fileReference);
        $this->em->persist($entity);
        $this->em->flush();

        $this->view($entity->getId(), $fileReference->toArray());
    }
}
