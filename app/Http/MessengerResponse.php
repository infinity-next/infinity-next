<?php

namespace App\Http;

use App;
use App\Board;
use InfinityNext\LaravelCaptcha\Captcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\ResponseTrait;
use Session;

class MessengerResponse extends JsonResponse
{
    use ResponseTrait;

    /**
     * Additional information to be supplied alongside the original response.
     *
     * @var array
     */
    protected $siblingContent = [];

    /**
     * Constructor.
     * Triggers compilation of additional information when constructed.
     *
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct($data = null, $status = 200, $headers = array())
    {
        $this->buildSiblingContent();

        return parent::__construct($data, $status, $headers);
    }

    /**
     * Builds additional information not nessecarily specific to this response.
     *
     * @return array
     */
    protected function buildSiblingContent()
    {
        return $this->siblingContent = [
            'captcha'   => $this->buildSiblingCaptcha(),
            'messenger' => true,
            'session'   => $this->buildSiblingSession(),
        ];
    }

    /**
     * Retrieves the datum for the captcha sibling.
     *
     * @return \App\Captcha|bool False if no captcha required.
     */
    protected function buildSiblingCaptcha()
    {
        $needCaptcha = user()->cannot('bypass-captcha');

        return $needCaptcha ? Captcha::findOrCreateCaptcha() : false;
    }

    /**
     * Returns the session ID.
     *
     * @return  string Session ID.
     */
    protected function buildSiblingSession()
    {
        return Session::getId();
    }

    /**
     * Intercepts data to be set to JSON and includes additional information.
     *
     * @param mixed $data
     *
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException
     */
    public function setData($data = array())
    {
        $fullData = $this->siblingContent;
        $fullData['data'] = $data;

        return parent::setData($fullData);
    }
}
