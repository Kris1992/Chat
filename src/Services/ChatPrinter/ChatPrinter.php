<?php declare(strict_types=1);

namespace App\Services\ChatPrinter;

/**
 *  Manage concrete printer
 */
class ChatPrinter
{   

    const PDF_PRINTER="pdf";
 
    public static function choosePrinter($printerName) {
        switch($printerName) {
            case self::PDF_PRINTER:
                return new PdfPrinter();
            default:
                throw new \Exception("Unsupported type of printer");
        }
    }
}
