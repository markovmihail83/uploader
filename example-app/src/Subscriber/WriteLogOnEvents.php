<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace ExampleApp\Subscriber;

use Atom\Uploader\Event\IUploadEvent;

class WriteLogOnEvents implements ISubscriber
{
    /**
     * @return array e.g: ['eventName' => 'method']
     *               'method' will be called on 'eventName' with 1 arg(instance of Atom\Uploader\Event\IUploadEvent)
     */
    public function getSubscribedEvents()
    {
        return [
            IUploadEvent::POST_UPLOAD => 'postUpload',
            IUploadEvent::POST_UPDATE => 'postUpdate',
            IUploadEvent::POST_REMOVE => 'postRemove',
            IUploadEvent::POST_REMOVE_OLD_FILE => 'postRemoveOldFile',
            IUploadEvent::POST_INJECT_URI => 'postInjectUri',
            IUploadEvent::POST_INJECT_FILE_INFO => 'postInjectFileInfo',
        ];
    }

    public function postUpload()
    {
        $this->log('postUpload.log');
    }

    private function log($filename, $message = '')
    {
        $path = sprintf('%s/../../var/log/%s', __DIR__, $filename);
        $stream = fopen($path, 'a');
        fwrite($stream, $message);
        fclose($stream);
    }

    public function postUpdate()
    {
        $this->log('postUpdate.log');
    }

    public function postRemove()
    {
        $this->log('postRemove.log');
    }

    public function postRemoveOldFile()
    {
        $this->log('postRemoveOldFile.log');
    }

    public function postInjectUri()
    {
        $this->log('postInjectUri.log');
    }

    public function postInjectFileInfo()
    {
        $this->log('postInjectFileInfo.log');
    }
}
