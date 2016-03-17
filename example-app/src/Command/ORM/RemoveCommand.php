<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\ORM;

use ExampleApp\Command\Base\ORMCommand;

class RemoveCommand extends ORMCommand
{
    protected function doConfigure()
    {
        $this->addIdArgument();
    }

    protected function doExecute()
    {
        $this->em->remove($this->getEntity());
        $this->view();
    }
}
