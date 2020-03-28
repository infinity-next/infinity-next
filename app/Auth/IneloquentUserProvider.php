<?php

namespace App\Auth;;

use App\Auth\AnonymousUser;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Extension of the Eloquent User Provider returning a generic user for guests.
 *
 * @category   Auth
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class IneloquentUserProvider extends EloquentUserProvider implements UserProvider
{
    /**
     * Return an Anonymous User to satisfy the Authenticable contract.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getAnonymousUser()
    {
        return new AnonymousUser;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function retrieveById($identifier)
    {
        return parent::retrieveById($identifier) ?? $this->getAnonymousUser();
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function retrieveByToken($identifier, $token)
    {
        return parent::retrieveByToken($identifier, $token) ?? $this->getAnonymousUser();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function retrieveByCredentials(array $credentials)
    {
        return parent::retrieveByCredentials($credentials) ?? $this->getAnonymousUser();
    }
}
