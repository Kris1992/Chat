<?php declare(strict_types=1);

namespace App\Services\Factory\ReportModel;

use App\Model\Report\ReportModel;
use App\Entity\User;

class ReportModelFactory implements ReportModelFactoryInterface 
{

    public function createFromData(?User $reportSender, ?User $reportedUser, ?array $reportData): ReportModel
    {

        $reportModel = new ReportModel();
        $reportModel
            ->setType($reportData['type'])
            ->setDescription($reportData['description'])
            ->setReportSender($reportSender)
            ->setReportedUser($reportedUser)
            ;

        return $reportModel;
    }
    
}
