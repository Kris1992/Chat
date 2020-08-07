<?php declare(strict_types=1);

namespace App\Services\ChatPrinter;

use App\Services\FilesManager\FilesManagerInterface;
use App\Services\FileWriter\FileWriterInterface;
use Doctrine\Common\Collections\Collection;
use App\Entity\{User, Message};

class TxtPrinter implements ChatPrinterInterface 
{

    /** @var FilesManagerInterface */
    private $filesManager;

    /** @var FileWriterInterface */
    private $fileWriter;

    public function __construct(FilesManagerInterface $filesManager, FileWriterInterface $fileWriter)
    {
        $this->filesManager = $filesManager;
        $this->fileWriter = $fileWriter;
    }

    public function printToFile(Collection $messages, User $currentUser, \DateTimeInterface $startDate, \DateTimeInterface $stopDate): string
    {

        $relativePath = sprintf('%s/%s_%d.txt',ChatPrinterConstants::CHAT_PRINTER, $currentUser->getLogin(), uniqid());
        $absolutePath = $this->filesManager->getAbsolutePath($relativePath);
        $this->fileWriter->open($absolutePath);
        $this->fileWriter->setHeader($this->createHeaderText($currentUser, $startDate, $stopDate));
        
        $text  = "";
        foreach($messages as $i => $message) {
            $text .= $this->messageToText($message, $currentUser);
        }

        $this->fileWriter->write($text);

        $this->fileWriter->close();
        $filePath = sprintf('%s/%s', 'uploads', $relativePath);

        return $filePath;
    }

    /**
     * createHeaderText  Creates header string to file
     * @param   User               $currentUser   User object with current logged in user
     * @param   \DateTimeInterface $startDate     Date of start recording chat messages
     * @param   \DateTimeInterface $stopDate      Date of end recording chat messages 
     * @return  string
     */
    private function createHeaderText(User $currentUser, \DateTimeInterface $startDate, \DateTimeInterface $stopDate): string
    {
        $header = sprintf('Messages (%s - %s) from %s chat room', $startDate->format("m/d/Y g:ia"), $stopDate->format("m/d/Y g:ia"), $currentUser->getLogin());

        return $header;
    }

    /**
     * messageToText convert message to string 
     * @param  Message $message     Message object to convert
     * @param  User    $currentUser User object with current logged in user
     * @return string
     */
    private function messageToText(Message $message, User $currentUser): string
    {
        $owner = $message->getOwner();
        if ($owner === $currentUser) {
            $text = PHP_EOL . 'You' . PHP_EOL;
        } else {
            $text = PHP_EOL . $owner->getLogin() . PHP_EOL;
        }
        
        $text .= trim(html_entity_decode(strip_tags($message->getSanitazedContent()))) . PHP_EOL;
        $text .= $message->getCreatedAt()->format("m/d/Y g:ia") . PHP_EOL;

        return $text;
    }

}
