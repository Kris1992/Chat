<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use App\Services\AttachmentFileUploader\AttachmentsConstants;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use League\Flysystem\FilesystemInterface;

class UploadedFilesRemoveCommand extends Command
{

    protected static $defaultName = 'app:uploaded-files:remove';

    /** @var FilesystemInterface */
    private $publicFilesystem;

    public function __construct(FilesystemInterface $publicUploadsFilesystem)
    {
        parent::__construct(null);
        $this->publicFilesystem = $publicUploadsFilesystem;
    }

    protected function configure()
    {
        $this
            ->setDescription('Remove all uploaded attachment files which are not images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        if($this->publicFilesystem->deleteDir(AttachmentsConstants::ATTACHMENTS_FILES)) {
            $io->success('Attachments files were removed.');
        }

        $io->success('All uploaded files were removed successfully.');

        return 0;
    }
}
