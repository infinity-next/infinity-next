<?php namespace App\Traits;

use DB;

trait TakePerGroup {
	
	/**
	 * Reduces the number of returned rows to X per grouped partition.
	 * 
	 * @return void
	 */
	public function scopeTakePerGroup($query, $group, $n = 10)
	{
		// Get queried table
		$table = $this->getTable();
		
		// Get primary key
		$key   = $this->primaryKey;
		
		if ($this->getConnection() instanceof \Illuminate\Database\MySqlConnection)
		{
			// Initialize MySQL variables inline
			$query->from( DB::raw("(SELECT @rank:=0, @group:=0) as vars, {$table}") );
			
			// If no columns already selected, let's select the column.
			if (!$query->getQuery()->columns) 
			{
				$query->select("{$table}.{$group}"); 
			}
			
			// Make sure column aliases are unique
			$groupAlias = 'group_'.md5(time());
			$rankAlias  = 'rank_'.md5(time());
			
			// Apply mysql variables
			$query->addSelect(DB::raw(
				"@rank := IF(@group = {$group}, @rank+1, 1) as {$rankAlias}, @group := {$group} as {$groupAlias}"
			));
			
			// Nake sure first order clause is the group order
			$query->getQuery()->orders = (array) $query->getQuery()->orders;
			array_unshift($query->getQuery()->orders, ['column' => $group, 'direction' => 'asc']);
			
			// prepare subquery
			$subQuery = $query->toSql();
			
			// prepare new main base Query\Builder
			$newBase = $this->newQuery()
				->from(DB::raw("({$subQuery}) as {$table}"))
				->mergeBindings($query->getQuery())
				->where($rankAlias, '<=', $n)
				->getQuery();
			
			// replace underlying builder to get rid of previous clauses
			$query->setQuery($newBase);
		}
		else if ($this->getConnection() instanceof \Illuminate\Database\PostgresConnection)
		{
			$query->from(DB::raw("(select (row_number() over (partition by \"{$group}\" order by \"{$key}\")) as \"take_per_group_number\", \"{$table}\".* FROM \"{$table}\" ) as \"{$table}\""));
			$query->where("{$table}.take_per_group_number", "<=", $n);
		}
		else
		{
			// Doesn't work completely, but it's the best I can do for now.
			// Add in support for SQLite later, maybe.
			return $query->skip(0)->take($n);
		}
	}
}
