<?php declare(strict_types=1);

namespace spec\App\Services\AttachmentFileDeleter;

use App\Services\AttachmentFileDeleter\{AttachmentFileDeleter, AttachmentFileDeleterInterface};
use App\Services\ImagesManager\ImagesManagerInterface;
use App\Services\FilesManager\FilesManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttachmentFileDeleterSpec extends ObjectBehavior
{
    function let(ImagesManagerInterface $attachmentImagesManager, FilesManagerInterface $filesManager)
    {
        $this->beConstructedWith($attachmentImagesManager, $filesManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttachmentFileDeleter::class);
    }

    function it_implements_attachment_file_deleter_interface()
    {
        $this->shouldImplement(AttachmentFileDeleterInterface::class);
    }

    function it_should_be_able_to_delete_chat_image_attachment($attachmentImagesManager)
    {
        $attachmentImagesManager->deleteImage('image.jpeg', Argument::type('string'))->shouldBeCalledTimes(1)->willReturn(true);
        $this->delete('example/path', 'image.jpeg', 'Image', 'Chat');
    }

    function it_should_be_able_to_delete_petition_image_attachment($attachmentImagesManager)
    {
        $attachmentImagesManager->deleteImage('image.jpeg', Argument::type('string'))->shouldBeCalledTimes(1)->willReturn(true);
        $this->delete('example/path', 'image.jpeg', 'Image', 'Petition');
    }

    function it_should_throw_exception_when_try_delete_image_with_unsupported_attachment_type()
    {
        $this->shouldThrow('Exception')->during('delete', ['example/path', 'image.jpeg', 'Image', Argument::type('string')]);
    }

    function it_should_be_able_to_delete_chat_file_attachment($filesManager)
    {
        $filesManager->delete(Argument::type('string'))->shouldBeCalledTimes(1)->willReturn(true);
        $this->delete('example/path', 'file.pdf', 'File', 'Chat');
    }

    function it_should_be_able_to_delete_petition_file_attachment($filesManager)
    {
        $filesManager->delete(Argument::type('string'))->shouldBeCalledTimes(1)->willReturn(true);
        $this->delete('example/path', 'file.pdf', 'File', 'Petition');
    }

    function it_should_throw_exception_when_try_delete_file_with_unsupported_attachment_type()
    {
        $this->shouldThrow('Exception')->during('delete', ['example/path', 'file.pdf', 'File', Argument::type('string')]);
    }

    function it_should_throw_exception_when_try_delete_unsupported_file_type()
    {
        $this->shouldThrow('Exception')->during('delete', ['example/path', 'file.pdf', Argument::type('string'), 'Chat']);
    }

}
