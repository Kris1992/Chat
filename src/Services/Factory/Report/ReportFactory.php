<?php declare(strict_types=1);

namespace App\Services\Factory\Report;

use App\Model\Report\ReportModel;
use App\Entity\Report;

class ReportFactory implements ReportFactoryInterface 
{
    //TESTS
    public function create(ReportModel $reportModel): Report
    {
        $report = new Report();
        $report
            ->setType($reportModel->getType())
            ->setDescription($reportModel->getDescription())
            ->setReportSender($reportModel->getReportSender())
            ->setReportedUser($reportModel->getReportedUser())
            ;

        return $report;
    }
    
}
