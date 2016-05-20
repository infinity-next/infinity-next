<?php namespace App\Validators;

use Settings;

class CSSValidator
{
	/**
	 * Create the validator.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->allowedRules          = config('sanitize.css.whitelist.rules');
		$this->allowedUrls           = config('sanitize.css.whitelist.urls');
		$this->allowedImportUrls     = config('sanitize.css.whitelist.imports');
		$this->allowedFileExtensions = config('sanitize.css.whitelist.extensions');
		$this->allowedMimeTypes      = config('sanitize.css.whitelist.mimetypes');
	}


	/**
	 * Pattern to match data URIs.
	 *
	 * @const string
	 */
	const DATA_URI_REGEXP = '/data:([a-zA-Z-\/]+)([a-zA-Z0-9-_;=.+]+)?,(.*)/';


	/**
	 * Determine if a data URI is valid, and whether it is allowed to be used.
	 *
	 * @param  string $uri
	 * @return boolean
	 */
	protected function isAllowedDataUri($uri)
	{
		if (!preg_match(self::DATA_URI_REGEXP, $uri, $matches))
		{
			return false;
		}

		$mimeType = $matches[1];

		foreach ($this->allowedMimeTypes as $allowedMimeType)
		{
			if (strpos($mimeType, $allowedMimeType) !== false)
			{
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
	 * @param  string $url
	 * @return boolean
	 */
	protected function isAllowedExtension($url)
	{
		$path = pathinfo($url);

		foreach ($this->allowedFileExtensions as $allowedExt)
		{
			if (strpos($path['extension'], $allowedExt) !== false)
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * Determine if a rule is allowed to be used in a custom stylesheet.
	 *
	 * @param  string $rule
	 * @return boolean
	 */
	protected function isAllowedRule($rule)
	{
		foreach ($this->allowedRules as $allowedRule)
		{
			if (strpos($rule, $allowedRule) !== false)
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * Determine if a URL is valid.
	 *
	 * @param  string $url
	 * @return boolean
	 */
	protected function isValidUrl($url)
	{
		return (bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
	}


	/**
	 * Determine if a URL is valid, and whether it is allowed to be used.
	 *
	 * @param  string $url
	 * @return boolean
	 */
	protected function isAllowedUrl($url)
	{
		if (!$this->isValidUrl($url))
		{
			return false;
		}

		if (!$this->isAllowedExtension($url))
		{
			return false;
		}

		foreach ($this->allowedUrls as $allowedUrl)
		{
			if (strpos($url, $allowedUrl) !== false)
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * Determine if an import URL is valid, and whether it is allowed to be used.
	 *
	 * @param  string $url
	 * @return boolean
	 */
	protected function isAllowedImportUrl($url)
	{
		if (!$this->isValidUrl($url))
		{
			return false;
		}

		foreach ($this->allowedImportUrls as $allowedUrl)
		{
			if (strpos($url, $allowedUrl) !== false)
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * Get URL string from object.
	 *
	 * @param  \Sabberworm\CSS\Value\URL $oURL
	 * @return string
	 */
	protected function getURLString($oURL)
	{
		return trim($oURL->getURL()->__toString(), '"');
	}


	/**
	 * @param  string $attribute
	 * @param  string $stylesheet
	 * @param  string $parameters
	 * @return boolean
	 */
	public function validateCSS($attribute, $stylesheet, $parameters)
	{
		$parser = new \Sabberworm\CSS\Parser($stylesheet);
		$style  = $parser->parse();

		foreach ($style->getAllRulesets() as $rulesets)
		{
			foreach($rulesets->getRules() as $rules)
			{
				$rule = $rules->getRule();

				if (!$this->isAllowedRule($rule))
				{
					return false;
				}
			}
		}

		foreach ($style->getAllValues() as $value)
		{
			switch(true)
			{
				case $value instanceof \Sabberworm\CSS\Value\URL:

					$sValue = $this->getURLString($value);

					if (!$this->isAllowedDataUri($sValue) && !$this->isAllowedUrl($sValue))
					{
						return false;
					}

					break;

				case $value instanceof \Sabberworm\CSS\Property\Import:

					$oValue = $value->getLocation();
					$sValue = $this->getURLString($oValue);

					if (!$this->isAllowedDataUri($sValue) && !$this->isAllowedImportUrl($sValue))
					{
						return false;
					}

					break;
			}
		}

		return true;
	}
}
