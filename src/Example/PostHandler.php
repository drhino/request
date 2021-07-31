<?php declare(strict_types=1);

namespace drhino\Request\Example;

use Psr\Http\Server\RequestHandlerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Laminas\Diactoros\Response\JsonResponse;

class PostHandler implements RequestHandlerInterface
{
    #public $vars = [];
    #public $middleware = AuthenticationMiddleware::class;

    /**
     * Handles a request and produces a response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface $response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestBody = \drhino\Request\Handler::requestBody($request);

        $response = new JsonResponse($requestBody);

        return $response;
    }
}
