<?php namespace Rookwood\Turnstile\User;

use Eloquent;

class Role extends Eloquent {

    public function users()
    {
        return $this->belongsToMany('User');
    }

} 