<?php declare(strict_types=1);

namespace App\Services\AttachmentsHelper;

/**
 *  Provide helpers methods to attachments
 */
interface AttachmentsHelperInterface
{   

    /**
     * getAttachments Get attachments from content string
     * @param   string      $content    String with content of message
     * @return  array|null              Return array of attachments filenames or null
     */
    public function getAttachments(string $content): ?array;

}
