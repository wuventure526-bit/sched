<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Trust every proxy. The app runs behind a platform load balancer that
     * terminates TLS and forwards plain HTTP to the container, so without this
     * Laravel ignores X-Forwarded-Proto, believes the request is HTTP, and
     * builds every asset() and route() URL as http:// on an https:// page.
     * Browsers then refuse to load those stylesheets and scripts as mixed
     * active content, which renders the site completely unstyled.
     *
     * A wildcard is appropriate here because the container is only reachable
     * through that load balancer -- it is not exposed directly -- so the
     * forwarded headers cannot be spoofed by an outside client.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
