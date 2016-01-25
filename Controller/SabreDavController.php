<?php

/*
 * This file is part of the SecotrustSabreDavBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Secotrust\Bundle\SabreDavBundle\Controller;

use Sabre\DAV\Server;
use Secotrust\Bundle\SabreDavBundle\SabreDav\HttpRequest;
use Secotrust\Bundle\SabreDavBundle\SabreDav\HttpResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SabreDavController.
 */
class SabreDavController
{
    /**
     * @var Server
     */
    private $dav;

    /**
     * Constructor.
     *
     * @param Server          $dav
     * @param RouterInterface $router
     */
    public function __construct(Server $dav, RouterInterface $router, $base_uri = '')
    {
        $router->getContext()->setBaseUrl($router->getContext()->getBaseUrl() . $base_uri);
        $this->dav = $dav;
        $this->dav->setBaseUri($router->generate('secotrust_sabre_dav', array()));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return StreamedResponse
     */
    public function execAction(Request $request)
    {
        $dav = $this->dav;
        $callback = function () use ($dav) {
            $dav->exec();
        };
        $response = new StreamedResponse($callback);
        $dav->httpRequest = new HttpRequest($request);
        $dav->httpResponse = new HttpResponse($response);

        return $response;
    }
}
