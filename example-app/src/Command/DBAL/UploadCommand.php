<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Command\DBAL;


use ExampleApp\Command\Base\DBALCommand;

class UploadCommand extends DBALCommand
{

    protected function doConfigure()
    {
        $this->addFileArgument();
    }

    protected function doExecute()
    {
        $file = $this->getFile();
        $identity = uniqid();
        $uploadable = compact('file');

        $this->uploader->persist($identity, $uploadable, 'dbal_uploadable');

        try {
            $uploadable['id'] = $this->conn->insert('uploadable', ['file' => $uploadable['file']]);
            $this->uploader->saved($identity);
        } catch (\Exception $e) {
            // do nothing
        } finally {
            $this->uploader->flush();
        }

        $this->view($uploadable['id'], $uploadable);
    }
}