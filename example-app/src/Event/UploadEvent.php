<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>.
 */

namespace ExampleApp\Event;

use Atom\Uploader\Event\IUploadEvent;

class UploadEvent implements IUploadEvent
{
    use \Atom\Uploader\Event\UploadEvent;
}
