<?php declare(strict_types=1);

namespace App\Services\AttachmentUploadSystem;

use App\Services\AttachmentCreator\AttachmentCreatorInterface;
use Symfony\Component\Messenger\{MessageBusInterface, Envelope};
use Symfony\Component\Messenger\Stamp\DelayStamp;
use App\Exception\Api\ApiBadRequestHttpException;
use App\Message\Command\CheckIsAttachmentUsed;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\{Message, User, Attachment};
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

    public function upload(User $user, ?Message $message, Request $request, string $type): Attachment
    {

        switch ($type) {
            case 'image':
                $file = $request->files->get('uploadImage');
                break;
            case 'file':
                $file = $request->files->get('uploadFile');
                break;
            default:
                throw new \UnexpectedValueException('Invalid request.');
        }
        
        if (!$file) {
            throw new ApiBadRequestHttpException('Invalid JSON.');
        }
        
        $attachment = $this->attachmentCreator->create($user, null, $file, ucfirst($type));
        
        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        //Check attachment is handled by message after 1 hour
        $attachmentUsedMessage = new CheckIsAttachmentUsed(
            $attachment->getId(),
            $user->getLogin()
        );

        $envelope = new Envelope($attachmentUsedMessage, [
            new DelayStamp(120000)//3600000)//1 hour delay 
        ]);

        $this->messageBus->dispatch($envelope);
        
        return $attachment;
    }

}
