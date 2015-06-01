<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'options';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'option_name';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['option_name', 'default_value', 'format', 'format_parameters', 'data_type'];
	
	/**
	 * Determines if Laravel should set created_at and updated_at timestamps.
	 *
	 * @var array
	 */
	public $timestamps = false;
	
	
	public function groups()
	{
		return $this->belongsToMany("\App\OptionGroup", 'option_group_assignments', 'option_name', 'option_group_id');
	}
	
	
	protected $decoded_format_parameters;
	
	public function getFormatParameter($parameter)
	{
		$parameters = $this->getFormatParameters();
		
		if (isset($parameters[$parameter]))
		{
			return $parameters[$parameter];
		}
		
		return null;
	}
	
	public function getFormatParameters()
	{
		if (!isset($this->decoded_format_parameters))
		{
			$this->decoded_format_parameters = json_decode($this->format_parameters, true);
		}
		
		return $this->decoded_format_parameters;
	}
	
	public function getSanitaryInput($input)
	{
		switch ($this->data_type)
		{
			case "boolean" :
				$input = !!$input;
				break;
		}
		
		return $input;
	}
	
	/**
	 * Gets the view path of the option's template for the config panel.
	 *
	 * @param  \App\Http\Controller|descendant $controller
	 * @return string
	 */
	public function getTemplate($controller)
	{
		switch ($this->format)
		{
			case "template":
				return $this->getFormatParameter('template');
			
			case "callback":
				## TODO ##
				return "Callback not supported.";
			
			default:
				return "widgets.config.option.{$this->format}";
		}
	}
	
	public function getValidation()
	{
		$requirement = "";
		switch ($this->data_type)
		{
			case "unsigned_integer" :
				$requirement = "integer";
				break;
			
			case "integer" :
				$requirement = "integer|min:0";
				break;
			
			case "boolean" :
				$requirement = "boolean";
				break;
		}
		
		$validation = $this->validation_parameters;
		$parameters = $this->getFormatParameters();
		
		if (is_array($parameters))
		{
			$validation = preg_replace_callback('/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', function($match) use ($parameters) {
				if (isset($parameters[$match[1]])) {
					return $parameters[$match[1]];
				}
				
				return $match[0];
			}, $validation);
		}
		
		if ($validation != "")
		{
			if ($requirement != "")
			{
				$requirement .= "|" . $validation;
			}
			else
			{
				$requirement = $validation;
			}
		}
		
		return $requirement;
	}
}