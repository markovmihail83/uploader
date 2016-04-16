<?php
/**
 * Copyright © 2016 Elbek Azimov. Contacts: <atom.azimov@gmail.com>
 */

namespace Atom\Uploader\Event;

use Atom\Uploader\Metadata\FileMetadata;

interface IUploadEvent
{
    const PRE_UPLOAD = 'atom_uploader.pre_upload';
    const POST_UPLOAD = 'atom_uploader.post_upload';

    const PRE_UPDATE = 'atom_uploader.pre_update';
    const POST_UPDATE = 'atom_uploader.post_update';

    const PRE_REMOVE = 'atom_uploader.pre_remove';
    const POST_REMOVE = 'atom_uploader.post_remove';

    const PRE_REMOVE_OLD_FILE = 'atom_uploader.pre_remove_old_file';
    const POST_REMOVE_OLD_FILE = 'atom_uploader.post_remove_old_file';

    const PRE_INJECT_URI = 'atom_uploader.pre_inject_uri';
    const POST_INJECT_URI = 'atom_uploader.post_inject_uri';

    const PRE_INJECT_FILE_INFO = 'atom_uploader.pre_inject_file_info';
    const POST_INJECT_FILE_INFO = 'atom_uploader.post_inject_file_info';

    /**
     * It should stop an action.
     */
    public function stopAction();

    /**
     * @return bool
     */
    public function isActionStopped();

    /**
     * @return object
     */
    public function &getFileReference();

    /**
     * @return FileMetadata
     */
    public function getMetadata();
}
