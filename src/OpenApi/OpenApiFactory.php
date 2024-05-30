<?php
namespace App\OpenApi;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\PathItem;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[AsDecorator(
    decorates: 'api_platform.openapi.factory',
    priority: -25,
    onInvalid: ContainerInterface::IGNORE_ON_INVALID_REFERENCE,
)]
class OpenApiFactory implements OpenApiFactoryInterface
{

    public function __construct(
        private readonly OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $authPath = $openApi->getPaths()->getPath('/auth');

        $post = $authPath->getPost()->withTags(['Auth']);

        $openApi->getPaths()->addPath(
            '/auth',
            (new PathItem())->withPost($post),
        );

        return $openApi;
    }
}