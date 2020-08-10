<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, Request};
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ReportRepository;
use App\Entity\{User, Report};

/**
* @IsGranted("ROLE_ADMIN")
**/
class AdminReportController extends AbstractController
{
    
    /**
     * @param   ReportRepository        $reportRepository
     * @param   PaginatorInterface      $paginator
     * @param   Request                 $request
     * @return  Response
     * @Route("/admin/report", name="admin_report", methods={"GET"})
     */
    public function list(ReportRepository $reportRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $searchTerms = $request->query->getAlnum('filterValue');
        $reportQuery = $reportRepository->findAllQuery($searchTerms);

        $pagination = $paginator->paginate(
            $reportQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 5)/*limit per page*/
        );
        
        return $this->render('admin_report/list.html.twig', [
            'pagination' => $pagination
        ]);
    }
    /**
     * @param   User                            $reportedUser
     * @param   ReportRepository                $reportRepository
     * @param   PaginatorInterface              $paginator
     * @param   Request                         $request
     * @return  Response
     * 
     * @Route("admin/report/user/{id}", name="admin_get_reports_user", methods={"GET"})
     */
    public function getReportsByUser(User $reportedUser, ReportRepository $reportRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $searchTerms = $request->query->getAlnum('filterValue');
        $reportQuery = $reportRepository->findReportsQueryByUser($reportedUser, $searchTerms);

        $pagination = $paginator->paginate(
            $reportQuery, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('perPage', 5)/*limit per page*/
        );
        
        return $this->render('admin_report/user_list.html.twig', [
            'pagination' => $pagination,
            'reportedUser' => $reportedUser
        ]);
    }

    /**
     * @param   Report                          $report
     * @return  Response
     * 
     * @Route("api/admin/report/{id}", name="api_admin_get_report", methods={"GET"})
     */
    public function getReport(Report $report): Response
    {

        return $this->json(
            $report,
            200,
            [],
            [
                'groups' => ['report:show']
            ]
        );
    }
}
