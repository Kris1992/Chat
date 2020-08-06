<?php declare(strict_types=1);

namespace App\Services\ChatPrinter;

use Twig\Environment;
use Knp\Snappy\Pdf;

/**
 *  Manage concrete printer
 */
class ChatPrinter
{   

    const PDF_PRINTER="pdf";

    /** @var Environment */
    private $twig;

    /** @var Pdf */
    private $pdf;

    public function __construct(Environment $twig, Pdf $pdf)
    {
        $this->twig = $twig;
        $this->pdf = $pdf;
    }
 
    public function choosePrinter($printerName) {
        switch($printerName) {
            case self::PDF_PRINTER:
                return new PdfPrinter($this->twig, $this->pdf);
            default:
                throw new \Exception("Unsupported type of printer.");
        }
    }
}
