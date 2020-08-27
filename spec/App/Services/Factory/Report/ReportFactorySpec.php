<?php declare(strict_types=1);

namespace spec\App\Services\Factory\Report;

use App\Services\Factory\Report\{ReportFactory, ReportFactoryInterface};
use PhpSpec\ObjectBehavior;
use App\Model\Report\ReportModel;
use App\Entity\{Report, User};

class ReportFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReportFactory::class);
    }

    function it_implements_report_factory_interface()
    {
        $this->shouldImplement(ReportFactoryInterface::class);
    }

    function it_should_be_able_to_create_report()
    {
        $reportSender = new User();
        $reportSender
            ->setEmail('exampleuser@example.com')
            ->setLogin('exampleUser')
            ;

        $reportedUser = new User();
        $reportedUser
            ->setEmail('reporteduser@example.com')
            ->setLogin('reportedUser')
            ;

        $reportModel = new ReportModel();
        $reportModel
            ->setType('Offensive')
            ->setDescription('Offensive language in messages.')
            ->setReportSender($reportSender)
            ->setReportedUser($reportedUser)
            ;

        $report = $this->create($reportModel);
        $report->shouldBeAnInstanceOf(Report::class);
        $report->getReportSender()->getEmail()->shouldReturn('exampleuser@example.com');
        $report->getReportSender()->getLogin()->shouldReturn('exampleUser');
        $report->getReportedUser()->getEmail()->shouldReturn('reporteduser@example.com');
        $report->getReportedUser()->getLogin()->shouldReturn('reportedUser');
        $report->getType()->shouldReturn('Offensive');
        $report->getDescription()->shouldReturn('Offensive language in messages.');

    }
}
