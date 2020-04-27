<?php

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;

if (!function_exists('is_hidden_service')) {
    /**
     * Determines if the active request cycle is via Tor.
     *
     * @since  0.6.0
     *
     * @param string $path
     * @param mixed  $parameters
     *
     * @return Illuminate\Contracts\Routing\UrlGenerator|string
     */
    function is_hidden_service()
    {
        return config('tor.request', false);
    }
}

if (!function_exists('media_url')) {
    /**
     * Generate an absolute or relative URL depending on our CDN domain.
     *
     * @since  0.6.0
     *
     * @param string $path
     * @param bool   $absolute Defaults true for fully-qualified URL.
     *
     * @return Illuminate\Contracts\Routing\UrlGenerator|string
     */
    function media_url($path = "", $absolute = false)
    {
        $url = '/'.trim($path, '/');

        if ($absolute) {
            $gen = app(Request::class);
            $request = app(UrlGenerator::class);
            $scheme = $gen->getScheme();
            $media = config('app.url_media', false);

            if (is_hidden_service()) {
                return $request->to($url);
            }

            if ($media) {
                return $scheme.$media.$url;
            }
        }

        return $url;
    }
}
