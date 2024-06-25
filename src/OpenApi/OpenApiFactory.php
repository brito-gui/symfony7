<?php
namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
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

        $openApi->withSecurity(['JWT']);

        $post = $authPath->getPost()->withSecurity([])->withTags(['Auth']);

        $openApi->getPaths()->addPath(
            '/auth',
            (new PathItem())->withPost($post),
        );

        return $openApi;
    }
}