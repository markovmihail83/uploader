<?php
/**
 * Copyright © 2017 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\ODM;

use ExampleApp\Command\Base\ODMCommand;

class UpdateCommand extends ODMCommand
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
