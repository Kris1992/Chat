<?php declare(strict_types=1);

namespace App\Services\AttachmentUploadSystem;

use App\Services\AttachmentCreator\AttachmentCreatorInterface;
use Symfony\Component\Messenger\{MessageBusInterface, Envelope};
use Symfony\Component\Messenger\Stamp\DelayStamp;
use App\Exception\Api\ApiBadRequestHttpException;
use App\Message\Command\CheckIsAttachmentUsed;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\{User, Attachment};
use Doctrine\ORM\EntityManagerInterface;

class AttachmentUploadSystem implements AttachmentUploadSystemInterface 
{

    /** @var AttachmentCreatorInterface */
    private $attachmentCreator;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var MessageBusInterface */
    private $messageBus;

    /**
     * AttachmentUploadSystem Constructor
     * 
     * @param AttachmentCreatorInterface $attachmentCreator
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $messageBus
     */
    public function __construct(AttachmentCreatorInterface $attachmentCreator, EntityManagerInterface $entityManager, MessageBusInterface $messageBus)  
    {
        $this->attachmentCreator = $attachmentCreator;
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
    }

    public function upload(User $user, Request $request, string $fileType): Attachment
    {
        switch ($fileType) {
            case 'image':
                $file = $request->files->get('uploadImage');
                break;
            case 'file':
                $file = $request->files->get('uploadFile');
                break;
            default:
                throw new \UnexpectedValueException('Invalid request.');
        }
        
        $attachmentType = $request->request->get('attachmentType');

        if (!$file || !$attachmentType) {
            throw new ApiBadRequestHttpException('Invalid JSON.');
        }
        
        $attachment = $this->attachmentCreator->create(
            $user, 
            $file,
            ucfirst($fileType),
            ucfirst($attachmentType)
        );
        
        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        //Check attachment is handled by message after 1 hour
        $attachmentUsedMessage = new CheckIsAttachmentUsed(
            $attachment->getId(),
            $user->getLogin(),
            ucfirst($attachmentType)
        );

        $envelope = new Envelope($attachmentUsedMessage, [
            new DelayStamp(60000)//3600000)//1 hour delay 
        ]);

        $this->messageBus->dispatch($envelope);
        
        return $attachment;
    }

}
