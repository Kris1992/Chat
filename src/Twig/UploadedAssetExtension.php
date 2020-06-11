<?php
declare(strict_types=1);

namespace App\Twig;

use Symfony\Contracts\Service\ServiceSubscriberInterface;
use App\Services\ImagesManager\ImagesManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\{TwigFilter, TwigFunction};
use Psr\Container\ContainerInterface;

class UploadedAssetExtension extends AbstractExtension implements ServiceSubscriberInterface
{   

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedServices()
    {
        return [
            ImagesManagerInterface::class
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath'])
        ];
    }
    public function getUploadedAssetPath(string $path): string
    {
        return $this->container
            ->get(ImagesManagerInterface::class)
            ->getPublicPath($path);
    }
}
