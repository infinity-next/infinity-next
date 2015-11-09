<?php namespace App\Observers;

class BinaryObserver {
	
	// Update citation references
	public function saved($model)
	{
		$model->saving    = false;
		
		
		$attributes = $model->getAttributes();
		
		foreach ($this->casts as $key => $value)
		{
			if (array_key_exists($key, $attributes))
			{
				$attributes[$key] = $this->castAttribute(
					$key, $attributes[$key]
				);
			}
		}
		
		
		if (['author_ip'] !== null)
		{
			$model->author_ip = binary_unsql($model->getAttributes()['author_ip']);
		}
	}
	
	public function saving($model)
	{
		$model->saving = true;
	}
	
}
