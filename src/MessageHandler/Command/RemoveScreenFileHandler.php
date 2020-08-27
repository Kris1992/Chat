<?php declare(strict_types=1);

namespace App\MessageHandler\Command;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use App\Services\FilesManager\FilesManagerInterface;
use App\Services\ChatPrinter\ChatPrinterConstants;
use App\Message\Command\RemoveScreenFile;

class RemoveScreenFileHandler implements  MessageSubscriberInterface
{

    /** @var FilesManagerInterface */
    private $filesManager;    

    /**
     * RemoveScreenFileHandler Constructor 
     * @param FilesManagerInterface       $filesManager
     */
    public function __construct(FilesManagerInterface $filesManager)
    {
        $this->filesManager = $filesManager;
    }

    public function __invoke(RemoveScreenFile $removeScreenFile)
    {

        $filename = $removeScreenFile->getFilename();
        $filePath = sprintf('%s/%s', ChatPrinterConstants::CHAT_PRINTER, $filename);
     
        $this->filesManager->delete($filePath);
    }

    public static function getHandledMessages(): iterable
    {
        yield RemoveScreenFile::class => [
            'method' => '__invoke',
        ];
    }
}
