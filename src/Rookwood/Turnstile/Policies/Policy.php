<?php namespace Rookwood\Turnstile\Policies;

abstract class Policy {

    abstract public function execute(User $user, array $data = []);
}