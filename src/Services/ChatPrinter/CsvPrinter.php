<?php declare(strict_types=1);

namespace App\Services\ChatPrinter;

use App\Services\FilesManager\FilesManagerInterface;
use App\Services\FileWriter\{FileWriterInterface, FileWriterConstants};
use Doctrine\Common\Collections\Collection;
use App\Entity\{User, Message};

class CsvPrinter implements ChatPrinterInterface 
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

        $relativePath = sprintf('%s/%s_%d.csv',ChatPrinterConstants::CHAT_PRINTER, $currentUser->getLogin(), uniqid());
        $absolutePath = $this->filesManager->getAbsolutePath($relativePath);
        $this->fileWriter->open($absolutePath);
        
        $text = "";
        foreach($messages as $i => $message) {
            $text .= $this->messageToText($message, $currentUser);
        }

        $this->fileWriter->write($text, $this->createHeaderText());

        $this->fileWriter->close();
        $filePath = sprintf('%s/%s', 'uploads', $relativePath);

        return $filePath;
    }

    /**
     * createHeaderText  Creates header string to file 
     * @return  string
     */
    private function createHeaderText(): string
    {
        return sprintf('User%1$sMessage%1$sDate', FileWriterConstants::CSV_DELIMITER);
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
            $text = 'You' . FileWriterConstants::CSV_DELIMITER;
        } else {
            $text = $owner->getLogin() . FileWriterConstants::CSV_DELIMITER;
        }
        
        $text .= trim(html_entity_decode(strip_tags($message->getSanitazedContent()))) . FileWriterConstants::CSV_DELIMITER;
        $text .= $message->getCreatedAt()->format("m/d/Y g:ia") . PHP_EOL;

        return $text;
    }

}
