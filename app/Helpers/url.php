<?php

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;

if (!function_exists('esi_url')) {
    /**
     * Generate a url for Edge-Side Includes.
     *
     * @since  0.5.1
     *
     * @param string $path
     * @param mixed  $parameters
     *
     * @return Illuminate\Contracts\Routing\UrlGenerator|string
     */
    function esi_url($path = null, $parameters = [])
    {
        $gen = app(UrlGenerator::class);

        if (is_null($path)) {
            return $gen;
        }

        return $gen->to($path, $parameters, false).'?'.$gen->getRequest()->getScheme();
    }
}

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
        return app(Request::class)->server('HTTP_HOST') === env('APP_URL_HS', false);
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
    function media_url($path, $absolute = true)
    {
        $url = '/'.trim($path, '/');

        if ($absolute) {
            $gen = app(Request::class);
            $request = app(UrlGenerator::class);
            $scheme = $gen->getScheme();
            $media = config('app.url_media', false);

            if (is_hidden_service()) {
                return $gen->to($url);
            }

            if ($media) {
                return $scheme.$media.$url;
            }

            return asset($path);
        }

        return $url;
    }
}
