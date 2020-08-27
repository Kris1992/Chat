<?php declare(strict_types=1);

namespace spec\App\Services\Factory\ReportModel;

use App\Services\Factory\ReportModel\{ReportModelFactory, ReportModelFactoryInterface};
use PhpSpec\ObjectBehavior;
use App\Model\Report\ReportModel;
use App\Entity\User;

class ReportModelFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReportModelFactory::class);
    }

    function it_implements_report_model_factory_interface()
    {
        $this->shouldImplement(ReportModelFactoryInterface::class);
    }

    function it_should_be_able_to_create_report_model()
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

        $reportData = [
            'type' => 'Offensive',
            'description' => 'Offensive language in messages.',
        ];

        $reportModel = $this->createFromData($reportSender, $reportedUser, $reportData);
        $reportModel->shouldBeAnInstanceOf(ReportModel::class);
        $reportModel->getReportSender()->getEmail()->shouldReturn('exampleuser@example.com');
        $reportModel->getReportSender()->getLogin()->shouldReturn('exampleUser');
        $reportModel->getReportedUser()->getEmail()->shouldReturn('reporteduser@example.com');
        $reportModel->getReportedUser()->getLogin()->shouldReturn('reportedUser');
        $reportModel->getType()->shouldReturn('Offensive');
        $reportModel->getDescription()->shouldReturn('Offensive language in messages.');

    }
}
