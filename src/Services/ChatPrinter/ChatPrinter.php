<?php declare(strict_types=1);

namespace App\Services\ChatPrinter;

use App\Services\FilesManager\FilesManagerInterface;
use App\Services\FileWriter\FileWriter;
use Twig\Environment;
use Knp\Snappy\Pdf;

/**
 *  Manage concrete printer
 */
class ChatPrinter
{   

    /** @var Environment */
    private $twig;

    /** @var Pdf */
    private $pdf;

    /** @var FilesManagerInterface */
    private $filesManager;

    /**
     * ChatPrinter Constructor
     * 
     * @param Environment $twig
     * @param Pdf $pdf
     * @param FilesManagerInterface $filesManager
     */
    public function __construct(Environment $twig, Pdf $pdf, FilesManagerInterface $filesManager)
    {
        $this->twig = $twig;
        $this->pdf = $pdf;
        $this->filesManager = $filesManager;
    }
 
    public function choosePrinter($printerName) {
        switch($printerName) {
            case ChatPrinterConstants::PDF_PRINTER:
                return new PdfPrinter($this->twig, $this->pdf);
            case ChatPrinterConstants::TXT_PRINTER:
                $fileWriter = FileWriter::chooseWriter(ChatPrinterConstants::TXT_PRINTER);
                return new TxtPrinter($this->filesManager, $fileWriter);
            case ChatPrinterConstants::CSV_PRINTER:
                $fileWriter = FileWriter::chooseWriter(ChatPrinterConstants::CSV_PRINTER);
                return new CsvPrinter($this->filesManager, $fileWriter);
            default:
                throw new \Exception("Unsupported type of printer.");
        }
    }
}
