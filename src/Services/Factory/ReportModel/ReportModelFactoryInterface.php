<?php declare(strict_types=1);

namespace App\Services\Factory\ReportModel;

use App\Model\Report\ReportModel;
use App\Entity\User;

/**
 *  Manage creating report models
 */
interface ReportModelFactoryInterface
{   

    /**
     * createFromData Create report model from data
     * @param   User|null           $reportSender       User object whose send report
     * @param   User|null           $reportedUser       User object whose reported user
     * @param   array|null          $reportData         Array with report data (type, description)
     * @return  ReportModel
     */
    public function createFromData(?User $reportSender, ?User $reportedUser, ?array $reportData): ReportModel;

}
