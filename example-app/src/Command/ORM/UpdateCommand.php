<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Command\ORM;

use ExampleApp\Command\Base\ORMCommand;

class UpdateCommand extends ORMCommand
{
    protected function doConfigure()
    {
        $this->addIdArgument()->addFileArgument();
    }

    protected function doExecute()
    {
        $entity = $this->getEntity();
        $entity->setFileField($this->getFile());

        $this->view($this->getId(), $entity->toArray());
    }
}
