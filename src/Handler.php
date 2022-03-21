<?php declare(strict_types=1);

namespace drhino\Request;

use Exception;
use Throwable;
use FastRoute\Dispatcher;

use Psr\Http\Server\RequestHandlerInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;

use Laminas\Stratigility\Middleware\ErrorResponseGenerator;
use Laminas\Diactoros\Response\TextResponse;

use function rawurldecode;
use function implode;
use function json_decode;

/**
 * Handles a server request and produces a response.
 */
class Handler implements RequestHandlerInterface
{
    private $routes;
    private $headers = [];

    /**
     * Runs the application stack.
     *
     * @param Callable $routes
     * @param Array    $headers *optional* Global headers for each response.
     */
    public function __construct(Callable $routes, Array $headers = null)
    {
        $this->routes  = $routes;
        $this->headers = $headers;

        // Strict Content- header matching
        // @see https://docs.laminas.dev/laminas-diactoros/v2/usage/
        $_ENV['LAMINAS_DIACTOROS_STRICT_CONTENT_HEADER_LOOKUP'] = true;

        // @see https://docs.laminas.dev/laminas-httphandlerrunner/runner/
        (new RequestHandlerRunner($this, new SapiEmitter,
            [ServerRequestFactory::class, 'fromGlobals'],
            function (Throwable $e) {
                return $this->errorResponse($e, new ServerRequest);
            }
        ))->run();
    }

    /**
     * Handles a request and produces a response.
     *
     * @param ServerRequestInterface $request
     *
     * @throws Exception
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $pathname = $request->getUri()->getPath();
            $pathname = rawurldecode($pathname);
            $method   = $request->getMethod();

            $dispatch = \FastRoute\simpleDispatcher($this->routes);
            $dispatch = $dispatch->dispatch($method, $pathname);

            if (Dispatcher::NOT_FOUND === $dispatch[0]) {
                throw new HttpNotFoundException;
            }

            if (Dispatcher::METHOD_NOT_ALLOWED === $dispatch[0]) {
                throw new HttpMethodNotAllowedException($dispatch[1]);
            }

            $handler = $dispatch[1];
            $handler = new $handler;

            $handler->vars = $dispatch[2];

            if (empty($handler->middleware)) {
                $response = $handler->handle($request);
            } else {
                $middleware = $handler->middleware;
                $middleware = new $middleware;
    
                $response = $middleware->process($request, $handler);
            }
    
            $response = $this->addHeaders($response, $this->headers);

            return $response;
        }
        catch (HttpNotFoundException|HttpMethodNotAllowedException $e) {
            throw $e;
        }
        catch (Throwable $e) {
            return $this->errorResponse($e, $request);
        }
    }

    /**
     * Adds $headers to $response when the header key does not exist.
     *
     * @param ResponseInterface $response
     * @param Array $headers
     *
     * @return ResponseInterface $response
     */
    private function addHeaders(ResponseInterface $response, Array $headers): ResponseInterface
    {
        foreach ($headers as $key => $value)
            if (!$response->hasHeader($key))
                $response = $response->withHeader($key, $value);

        return $response;
    }

    /**
     * Returns an error response.
     *
     * @param Throwable $exception
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface $response
     */
    private function errorResponse(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $generate = new ErrorResponseGenerator($isDevelopmentMode = true);
        $response = new TextResponse('');
        $response = $generate($exception, $request, $response);
        $response = $this->addHeaders($response, $this->headers);

        return $response;
    }

    /**
     * Returns the request body.
     *
     * @param ServerRequestInterface $request
     *
     * @throws Exception
     *
     * @return Array $body
     */
    public static function requestBody(ServerRequestInterface $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');

        try {
            if ($contentType === 'application/json') {
                $body = $request->getBody()->getContents();
                $body = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } else {
                throw new Exception('Unknown Content-Type.');
            }
        }
        catch (Throwable $e) {
            throw new Exception('Unable to decode requestBody.', 415, $e);
        }

        return $body;
    }
}
