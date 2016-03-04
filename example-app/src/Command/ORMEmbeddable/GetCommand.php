<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\ORMEmbeddable;


use ExampleApp\Command\Base\ORMEmbeddableCommand;

class GetCommand extends ORMEmbeddableCommand
{

    protected function doConfigure()
    {
        $this->addIdArgument();
    }

    protected function doExecute()
    {
        $fileReference = $this->getEntity()->getFileReference();

        $this->view($this->getId(), $fileReference->toArray());
    }
}