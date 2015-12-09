<?php namespace App\Traits;

use App\Support\IP;

trait EloquentBinary {
	
	/**
	 * Create a collection of models from plain arrays.
	 *
	 * @static
	 * @param  array  $items
	 * @param  string|null  $connection
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public static function hydrate(array $items, $connection = null)
	{
		$instance = (new static)->setConnection($connection);
		
		$items = array_map(function ($item) use ($instance) {
			// This loop unwraps content from stream resource objects.
			// PostgreSQL will return binary data as a stream, which does not
			// cache correctly. Doing this allows proper attribute mutation and
			// type casting without any headache or checking which database
			// system we are using before doing business logic.
			// 
			// See: https://github.com/laravel/framework/issues/10847
			foreach ($item as $column => $datum)
			{
				if (is_resource($datum))
				{
					$item->{$column} = stream_get_contents($datum);
				}
			}
			
			return $instance->newFromBuilder($item);
		}, $items);
		
		return $instance->newCollection($items);
	}
	
	/**
	 * Cast an attribute to a native PHP type.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	protected function castAttribute($key, $value)
	{
		if (is_null($value)) {
			return $value;
		}
		
		switch ($this->getCastType($key)) {
			case 'ip':
				return new IP($value);
			case 'int':
			case 'integer':
				return (int) $value;
			case 'real':
			case 'float':
			case 'double':
				return (float) $value;
			case 'string':
				return (string) $value;
			case 'bool':
			case 'boolean':
				return (bool) $value;
			case 'object':
				return $this->fromJson($value, true);
			case 'array':
			case 'json':
				return $this->fromJson($value);
			case 'collection':
				return new BaseCollection($this->fromJson($value));
			case 'date':
			case 'datetime':
				return $this->asDateTime($value);
			default:
				return $value;
		}
	}
}
