<?php namespace Rookwood\Turnstile\User;

use App;
use Role;
use Illuminate\Database\Eloquent;
use Rookwood\Turnstile\Policies\Policy;
use Rookwood\Turnstile\Exceptions\PolicyException;

/**
 * Collection of functions to test against system policies
 *
 * The ideas herein have been heavily inspired by (or copied from) Jeremy Bush (https://github.com/zombor)
 *
 */
trait PolicyTrait {

    /**
     * Test for authorization via policy class
     *
     * @param  string
     * @param  array
     * @throws PolicyException
     * @return boolean
     */
    public function can($policyKey, $data = array())
    {
        // Get the policy class name
        $policyClass = App::make('policy')->get($policyKey);

        try
        {
            // Execute the policy
            $reflectedClass = new \ReflectionClass($policyClass);
            $instance = $reflectedClass->newInstanceArgs();
            $status = $instance->execute($this, $data);
        }
        catch (\ReflectionException $e)
        {
            // No policy found
            throw new PolicyException("Policy $policyKey does not exist.");
        }

        if (TRUE === $status)
        {
            return TRUE;
        }
        else
        {
            // Store the error
            Policy::$policyFailureState = $status;

            return FALSE;
        }

    }

    /**
     * Test for a relation between the current user and a supplied object
     *
     * @param  Eloquent
     * @return boolean
     */
    public function owns($object)
    {
        if (property_exists($object, 'user_id'))
        {
            if ($object->user_id == $this->id)
            {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Tests if the user has a given role
     *
     * @param mixed $role
     * @return boolean
     */
    public function isA($role)
    {
        if ( ! $role instanceOf Model)
        {
            $role = Role::where('name', '=', $role)->firstOrFail();
        }

        if ($this->roles->contains($role))
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Wrapper for isA() for roles that start with vowels. The presence of this
     * function may indicate that I'm a little overly concerned with such things.
     *
     * @param $role
     * @return boolean
     */
    public function isAn($role)
    {
        return $this->isA($role);
    }

    /**
     * User has many roles
     *
     * @return mixed
     */
    public function roles()
    {
        return $this->belongsToMany('Role');
    }
} 