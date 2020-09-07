<?php declare(strict_types=1);

namespace App\Services\ReportSystem;

use App\Entity\{User, Report};

/**
 *  Take care about all processes with reports
 */
interface ReportSystemInterface
{   

    /**
     * create Create report
     * @param   User|null           $reportSender       User object whose send report
     * @param   User|null           $reportedUser       User object whose reported user
     * @param   array|null          $reportData         Array with report data (type, description)
     * @throws  \Exception                              Throws an \Exception when cannot create valid report
     * @return  Report
     */
    public function create(?User $reportSender, ?User $reportedUser, ?array $reportData): Report;

}
