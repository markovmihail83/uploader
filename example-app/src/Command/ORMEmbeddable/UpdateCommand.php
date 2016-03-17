<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Command\ORMEmbeddable;

use ExampleApp\Command\Base\ORMEmbeddableCommand;

class UpdateCommand extends ORMEmbeddableCommand
{
    protected function doConfigure()
    {
        $this->addIdArgument()->addFileArgument();
    }

    protected function doExecute()
    {
        $fileReference = $this->getEntity()->getFileReference();
        $fileReference->setFile($this->getFile());

        $this->view($this->getId(), $fileReference->toArray());
    }
}
