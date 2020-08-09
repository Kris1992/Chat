<?php declare(strict_types=1);

namespace App\Services\Factory\Report;

use App\Model\Report\ReportModel;
use App\Entity\Report;

/**
 *  Manage creating reports
 */
interface ReportFactoryInterface
{   

    /**
     * create Create report from report model
     * @param   ReportModel         $reportModel       Report model object
     * @return  Report
     */
    public function create(ReportModel $reportModel): Report;

}
