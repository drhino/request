<?php declare(strict_types=1);

namespace drhino\Request\Example;

use Psr\Http\Server\RequestHandlerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Laminas\Diactoros\Response\HtmlResponse;

class IndexHandler implements RequestHandlerInterface
{
    public $vars = [];
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
        $name = $this->vars['name'];
        
        $response = new HtmlResponse('<h1>Hello ' . $name . '</h1>');

        return $response;
    }
}
