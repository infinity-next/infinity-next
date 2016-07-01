<?php

namespace App\Validators;

use Sabberworm\CSS\Parsing\UnexpectedTokenException;

class CSSValidator
{
    /**
     * Create the validator.
     */
    public function __construct()
    {
        $this->allowRelativeUrls = config('sanitize.css.whitelist.relative');
        $this->allowedRules = config('sanitize.css.whitelist.rules');
        $this->allowedUrls = config('sanitize.css.whitelist.urls');
        $this->allowedImportUrls = config('sanitize.css.whitelist.imports');
        $this->allowedFileExtensions = config('sanitize.css.whitelist.extensions');
        $this->allowedMimeTypes = config('sanitize.css.whitelist.mimetypes');
    }

    /**
     * Pattern to match data URIs.
     *
     * @const string
     */
    const DATA_URI_REGEXP = '/^data:([a-zA-Z-\/]+)([a-zA-Z0-9-_;=.+]+)?,(.*)/';

    /**
     * Determine if a data URI is valid, and whether it is allowed to be used.
     *
     * @param string $uri
     *
     * @return bool
     */
    protected function isValidDataUri($uri)
    {
        if (!preg_match(self::DATA_URI_REGEXP, $uri, $matches)) {
            return false;
        }

        $mimeType = $matches[1];

        foreach ($this->allowedMimeTypes as $allowedMimeType) {
            if (strpos($mimeType, $allowedMimeType) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the file extension in a path is allowed to be used.
     *
     * Assumes the URLs you have whitelisted are setting the right file
     * extension for the Content-Type.
     *
     * @param string $url
     *
     * @return bool
     */
    protected function isAllowedExtension($url)
    {
        $parts = parse_url($url);
        $path = pathinfo($parts['path']);

        if (!isset($path['extension'])) {
            return false;
        }

        foreach ($this->allowedFileExtensions as $allowedExt) {
            if (strpos($path['extension'], $allowedExt) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a rule is allowed to be used in a custom stylesheet.
     *
     * @param string $rule
     *
     * @return bool
     */
    protected function isAllowedRule($rule)
    {
        foreach ($this->allowedRules as $allowedRule) {
            if (strpos($rule, $allowedRule) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a URL is absolute, and whether it is valid.
     *
     * @param string $url
     *
     * @return bool
     */
    protected function isValidAbsoluteUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            return false;
        }

        if (!$this->isAllowedExtension($url)) {
            return false;
        }

        return true;
    }


    /**
     * Determine if a URL is relative, and whether it is valid.
     *
     * @param string $url
     *
     * @return bool
     */
    protected function isValidRelativeUrl($url)
    {
        $parts = parse_url($url);

        if (!$parts || isset($parts['scheme']) || isset($parts['host'])) {
            return false;
        }

        if (!$this->isAllowedExtension($url)) {
            return false;
        }

        return true;
    }


    /**
     * Determine if a URL is allowed to be used.
     *
     * @param string $url
     *
     * @return bool
     */
    protected function isAllowedUrl($url)
    {
        if ($this->allowRelativeUrls && $this->isValidRelativeUrl($url)) {
            return true;
        }

        if (!$this->isValidAbsoluteUrl($url)) {
            return false;
        }

        foreach ($this->allowedUrls as $allowedUrl) {
            if (strpos($url, $allowedUrl) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if an import URL is allowed to be used.
     *
     * @param string $url
     *
     * @return bool
     */
    protected function isAllowedImportUrl($url)
    {
        if (!$this->isValidAbsoluteUrl($url)) {
            return false;
        }

        foreach ($this->allowedImportUrls as $allowedUrl) {
            if (strpos($url, $allowedUrl) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get URL string from object.
     *
     * @param \Sabberworm\CSS\Value\URL $oURL
     *
     * @return string
     */
    protected function getURLString($oURL)
    {
        return trim($oURL->getURL()->__toString(), '"');
    }

    /**
     * @param string $attribute
     * @param string $stylesheet
     * @param string $parameters
     *
     * @return bool
     */
    public function validateCSS($attribute, $stylesheet, $parameters)
    {
        $parser = new \Sabberworm\CSS\Parser($stylesheet);

        try {
            $style = $parser->parse();
        }
        catch (UnexpectedTokenException $e) {
            return false;
        }

        foreach ($style->getAllRulesets() as $rulesets) {
            foreach ($rulesets->getRules() as $rules) {
                $rule = $rules->getRule();

                if (!$this->isAllowedRule($rule)) {
                    return false;
                }
            }
        }

        foreach ($style->getAllValues() as $value) {
            switch (true) {
                case $value instanceof \Sabberworm\CSS\Value\URL:

                    $sValue = $this->getURLString($value);

                    if (!$this->isValidDataUri($sValue) && !$this->isAllowedUrl($sValue)) {
                        return false;
                    }

                    break;

                case $value instanceof \Sabberworm\CSS\Property\Import:

                    $oValue = $value->getLocation();
                    $sValue = $this->getURLString($oValue);

                    if (!$this->isValidDataUri($sValue) && !$this->isAllowedImportUrl($sValue)) {
                        return false;
                    }

                    break;
            }
        }

        return true;
    }
}
