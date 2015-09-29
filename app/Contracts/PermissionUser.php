<?php namespace App\Contracts;

interface PermissionUser {
	
	/**
	 * Getter for the $accountable property.
	 *
	 * @return boolean
	 */
	public function isAccountable();
	
	/**
	 * Getter for the $anonymous property.
	 *
	 * @return boolean
	 */
	public function isAnonymous();
}
