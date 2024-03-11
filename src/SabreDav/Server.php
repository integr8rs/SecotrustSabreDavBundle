<?php


namespace Secotrust\Bundle\SabreDavBundle\SabreDav;


use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;

class Server
{
    /** @var \Sabre\DAV\Server */
    private $dav;

    public function __construct(
        \Sabre\DAV\Server $dav,
        HtmlErrorRenderer $errorHandler,
        RouterInterface  $router,
        bool $useSymfonyExceptionHandler)
    {
        $this->dav = $dav;

        $this->dav->setBaseUri($router->generate('secotrust_sabre_dav'));

        if($useSymfonyExceptionHandler){
            // Use symfony exceptionhandler - much easier
            $exceptionCallback = function($exception) use ($errorHandler){
                $namespace = "Sabre\DAV\Exception\\";
                // Only handle exceptions of non-sabre-dav-type
                if(substr(get_class($exception), 0, strlen($namespace)) !== $namespace){
                    http_response_code(500);
                    die($errorHandler->render($exception)->getAsString());
                }
            };
            $this->dav->on('exception',$exceptionCallback);
        }
    }

    public function handle(Request $request): StreamedResponse
    {
        $dav = $this->dav;
        $callback = function () use ($dav) {
            $dav->start();
        };
        $response = new StreamedResponse($callback);
        $dav->httpRequest = new HttpRequest($request);
        $dav->httpResponse = new HttpResponse($response);

        return $response;
    }

}