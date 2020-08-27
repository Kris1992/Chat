<?php declare(strict_types=1);

namespace App\Services\ReportSystem;

use App\Services\Factory\ReportModel\ReportModelFactoryInterface;
use App\Services\ModelValidator\ModelValidatorInterface;
use App\Services\Factory\Report\ReportFactoryInterface;
use App\Entity\{Report, User};
use App\Repository\ReportRepository;

class ReportSystem implements ReportSystemInterface 
{

    /** @var ReportRepository */
    private $reportRepository;

    /** @var ReportModelFactoryInterface */
    private $reportModelFactory;

    /** @var ModelValidatorInterface */
    private $modelValidator;

    /** @var ReportFactoryInterface */
    private $reportFactory;

    /**
     * ReportSystem Constructor
     * 
     * @param ReportRepository              $reportRepository
     * @param ReportModelFactoryInterface   $reportModelFactory
     * @param ModelValidatorInterface       $modelValidator
     * @param ReportFactoryInterface        $reportFactory
     */
    public function __construct(ReportRepository $reportRepository, ReportModelFactoryInterface $reportModelFactory, ModelValidatorInterface $modelValidator, ReportFactoryInterface $reportFactory)  
    {
        $this->reportRepository = $reportRepository;
        $this->reportModelFactory = $reportModelFactory;
        $this->modelValidator = $modelValidator;
        $this->reportFactory = $reportFactory;
    }

    public function create(?User $reportSender, ?User $reportedUser, ?array $reportData): Report
    {   
        if ($reportSender === $reportedUser) {
            throw new \Exception('You cannot report yourself');
        }
        
        $reportToday = $this->reportRepository->findOneByUsersAfterDate(
            $reportSender,
            $reportedUser,
            new \DateTime('yesterday')
        );

        if ($reportToday) {
            throw new \Exception("You already report this user in last 24 hours");
        }

        $reportModel = $this->reportModelFactory->createFromData($reportSender, $reportedUser, $reportData);

        if (!$this->modelValidator->isValid($reportModel)) {
            throw new \Exception($this->modelValidator->getErrorMessage());
        }

        return $this->reportFactory->create($reportModel);
    }

}
