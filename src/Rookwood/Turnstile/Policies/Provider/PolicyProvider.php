<?php namespace Rookwood\Turnstile\Policies\Provider;

use Illuminate\Support\Collection;

/**
 * Convenient way to store an array of policy classes and use as a Collection for access
 *
 */
class PolicyProvider {

    /**
     * This is where you place your key => value storage to point to your policies
     * e.g. "register" => "Some\\Namespace\\RegistrationPolicy"
     *
     * @var array
     */
    protected static $data = [];


    /**
     * Generate an Illuminate\Collection for easy access of policy list
     *
     * @return Collection
     */
    public static function make()
    {
        return new Collection(static::$data);
    }

} 