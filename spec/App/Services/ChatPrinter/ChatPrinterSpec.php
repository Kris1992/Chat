<?php declare(strict_types=1);

namespace spec\App\Services\ChatPrinter;

use App\Services\FilesManager\FilesManagerInterface;
use App\Services\ChatPrinter\{ChatPrinter, PdfPrinter, TxtPrinter, CsvPrinter, ChatPrinterConstants};
use App\Services\ChatPrinter\ChatPrinterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Twig\Environment;
use Knp\Snappy\Pdf;

class ChatPrinterSpec extends ObjectBehavior
{
    function let(Environment $twig, Pdf $pdf, FilesManagerInterface $filesManager)
    {
        $this->beConstructedWith($twig, $pdf, $filesManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChatPrinter::class);
    }

    function it_is_able_to_choose_pdf_printer() 
    {
        $printer = $this->choosePrinter(ChatPrinterConstants::PDF_PRINTER);
        $printer->shouldBeAnInstanceOf(PdfPrinter::class);
        $printer->shouldImplement(ChatPrinterInterface::class);
    }

    function it_is_able_to_choose_txt_printer() 
    {
        $printer = $this->choosePrinter(ChatPrinterConstants::TXT_PRINTER);
        $printer->shouldBeAnInstanceOf(TxtPrinter::class);
        $printer->shouldImplement(ChatPrinterInterface::class);
    }

    function it_is_able_to_choose_csv_printer() 
    {
        $printer = $this->choosePrinter(ChatPrinterConstants::CSV_PRINTER);
        $printer->shouldBeAnInstanceOf(CsvPrinter::class);
        $printer->shouldImplement(ChatPrinterInterface::class);
    }

    function it_should_throw_exception_when_choosen_printer_does_not_exist(){
        $this->shouldThrow('Exception')->during('choosePrinter', [Argument::type('string')]);
    }
}
