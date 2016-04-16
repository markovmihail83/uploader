<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\DBAL;


use ExampleApp\Command\Base\DBALCommand;

class UpdateCommand extends DBALCommand
{

    protected function doConfigure()
    {
        $this->addIdArgument()->addFileArgument();
    }

    protected function doExecute()
    {
        $id = $this->getId();
        $file = $this->getFile();

        $oldUploadable = $this->getUploadable();
        $newUploadable = array_merge($oldUploadable, ['file' => $file]);
        $identity = uniqid();
        $this->uploader->update($identity, $newUploadable, $oldUploadable, 'dbal_uploadable');

        try {
            $this->conn->update('uploadable', ['file' => $newUploadable['file']], compact('id'));
            $this->uploader->updated($identity);
        } catch (\Exception $e) {
            // do nothing
        } finally {
            $this->uploader->flush();
        }

        $this->view($id, $newUploadable);
    }
}