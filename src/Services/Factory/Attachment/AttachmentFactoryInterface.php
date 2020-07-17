<?php declare(strict_types=1);

namespace App\Services\Factory\Attachment;

use App\Model\Attachment\AttachmentModel;
use App\Entity\Attachment;

/**
 *  Take care about creating message attachment
 */
interface AttachmentFactoryInterface
{   
    /**
     * create Create message attachment
     * @param   AttachmentModel     $attachmentModel    Attachment model object
     * @return  Attachment                              Return attachment object 
     */
    public function create(AttachmentModel $attachmentModel): Attachment;

}
