<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Services\ImagesManager\ImagesConstants;
use League\Flysystem\FilesystemInterface;

class UploadedImagesRemoveCommand extends Command
{

    protected static $defaultName = 'app:uploaded-images:remove';

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
            ->setDescription('Remove all uploaded images (uploaded path depends of env)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        if($this->publicFilesystem->deleteDir(ImagesConstants::USERS_IMAGES)) {
            $io->success('Users images was removed.');
        }

        if($this->publicFilesystem->deleteDir(ImagesConstants::ATTACHMENTS_IMAGES)) {
            $io->success('Attachments images was removed.');
        }

        $io->success('All uploaded images was removed successfully.');

        return 0;
    }
}
