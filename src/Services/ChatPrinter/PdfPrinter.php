<?php declare(strict_types=1);

namespace App\Services\ChatPrinter;

use Doctrine\Common\Collections\Collection;
use App\Entity\User;
use Twig\Environment;
use Knp\Snappy\Pdf;

class PdfPrinter implements ChatPrinterInterface 
{

    /** @var Environment */
    private $twig;

    /** @var Pdf */
    private $pdf;

    public function __construct(Environment $twig, Pdf $pdf)
    {
        $this->twig = $twig;
        $this->pdf = $pdf;
    }

    public function printToFile(Collection $messages, User $currentUser, \DateTimeInterface $startDate, \DateTimeInterface $stopDate): string
    {

        $html = $this->twig->render('printer/messages_printer.html.twig', [
            'messages' => $messages,
            'currentUser' => $currentUser,
            'startDate' => $startDate,
            'stopDate' => $stopDate
        ]);

        $options = [
            'margin-top'    => 0,
            'margin-right'  => 0,
            'margin-bottom' => 0,
            'margin-left'   => 0,
        ];

        $filePath = sprintf('uploads/%s/%s_%d.pdf',ChatPrinterConstants::CHAT_PRINTER, $currentUser->getLogin(), uniqid());
        $this->pdf->generateFromHtml($html, $filePath, $options, true);

        return $filePath;
    }

}
