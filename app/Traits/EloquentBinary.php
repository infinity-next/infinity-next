<?php namespace App\Traits;

trait EloquentBinary {
	
	/**
	 * Create a collection of models from plain arrays.
	 *
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
			foreach ($item as $column => &$datum)
			{
				if (is_resource($datum))
				{
					$datum = stream_get_contents($datum);
				}
			}
			
			return $instance->newFromBuilder($item);
		}, $items);
		
		return $instance->newCollection($items);
	}
	
}
