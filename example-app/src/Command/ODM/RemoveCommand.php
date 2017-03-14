<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\ODM;

use ExampleApp\Command\Base\ODMCommand;

class RemoveCommand extends ODMCommand
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
