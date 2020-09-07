<?php declare(strict_types=1);

namespace spec\App\Services\FileWriter;

use App\Services\FileWriter\{FileWriter, FileWriterInterface};
use App\Services\FileWriter\{TextFileWriter, CsvFileWriter, FileWriterConstants};
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileWriterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FileWriter::class);
    }

    function it_is_able_to_choose_text_file_writer() 
    {
        $this->beConstructedThrough('chooseWriter', [FileWriterConstants::TXT_WRITER]);
        $this->shouldBeAnInstanceOf(TextFileWriter::class);
        $this->shouldImplement(FileWriterInterface::class);
    }

    function it_is_able_to_choose_csv_file_writer() 
    {
        $this->beConstructedThrough('chooseWriter', [FileWriterConstants::CSV_WRITER]);
        $this->shouldBeAnInstanceOf(CsvFileWriter::class);
        $this->shouldImplement(FileWriterInterface::class);
    }

    function it_should_throw_exception_when_choosen_writer_does_not_exist(){
        $this->shouldThrow('Exception')->during('chooseWriter', [Argument::type('string')]);
    }

}
