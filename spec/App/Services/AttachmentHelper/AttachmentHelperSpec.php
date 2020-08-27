<?php declare(strict_types=1);

namespace spec\App\Services\AttachmentHelper;

use App\Services\AttachmentHelper\{AttachmentHelper, AttachmentHelperInterface};
use App\Repository\AttachmentRepository;
use PhpSpec\ObjectBehavior;
use App\Entity\User;

class AttachmentHelperSpec extends ObjectBehavior
{

    function let(AttachmentRepository $attachmentRepository)
    {
        $this->beConstructedWith($attachmentRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttachmentHelper::class);
    }

    function it_implements_attachment_helper_interface()
    {
        $this->shouldImplement(AttachmentHelperInterface::class);
    }

    function it_should_be_able_to_get_attachments_filenames()
    {
        $content = 'test content <img src="image.jpeg"/> test'; 
        $filenames = $this->getAttachmentsFilenames($content);
        $filenames->shouldBeArray();
        $filenames[0]->shouldBeString();
        $filenames[0]->shouldReturn('image.jpeg');

    }

    function it_should_return_null_when_content_has_not_attachmets()
    {
        $content = 'test content <img s="image.jpeg"/> test';
        $filenames = $this->getAttachmentsFilenames($content);
        $filenames->shouldReturn(null);
    }

    function it_should_return_null_when_content_is_null()
    {
        $content = null;
        $filenames = $this->getAttachmentsFilenames($content);
        $filenames->shouldReturn(null);
    }

}
