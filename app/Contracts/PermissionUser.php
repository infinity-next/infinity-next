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
	
	
	public function can($permission, $board = null);
	
	public function canAttach(\App\Board $board);
	
	public function canDelete(\App\Post $post);
	
	public function canEdit(\App\Post $post);
	
	public function canReport(\App\Post $post);
	
	public function canSticky(\App\Post $post);
}
