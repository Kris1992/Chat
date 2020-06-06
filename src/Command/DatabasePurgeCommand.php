<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

class DatabasePurgeCommand extends Command
{
    protected static $defaultName = 'app:database:purge';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct(null);
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Purge the configured database data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $io->success('Purging database was complete!');

        return 0;
    }
}
