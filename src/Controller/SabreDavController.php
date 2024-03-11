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

use Secotrust\Bundle\SabreDavBundle\SabreDav\Server;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SabreDavController
{
    public function __construct(
        private readonly Server $server,
    )
    {
    }

    public function execAction(Request $request): StreamedResponse
    {
        return $this->server->handle($request);
    }
}
