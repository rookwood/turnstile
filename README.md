# Turnstile
Turnstile is a policy-based ACL library for Laravel 4 designed to give very fine-grained control over user permissions.  This is accomplished by mapping any given user action to a policy class that evaluates if the action is allowable.  Doing such allows for more control than a simple user/role system (though roles are still easily used in Turnstile). The beauty of this is that the same system can be used for everything from protecting an admin panel to checking if a user can see a login button (if already logged in, just deny access for visibility, etc).

### Example
Since no one has ever used a blog example before, we'll do that here:

````php
// CommentController.php (or somesuch)
if ($this->user->can("edit_comment", ['comment' => $comment]))
{
    // logic
}
else
{
    // pass back an error telling them why they were denied
}
````

The "edit_comment" action is mapped to a policy class (CommentEditingPolicy for this example).

````php
<?php namespace Vendor\Application\Policies;

// Abstract class enforcing the correct signature on the execute() method
use Rookwood\Turnstile\Policies\Policy;

class CommentEditingPolicy extends Policy {

    const UNSPECIFIED_FAILURE = "policy.comments.failure";
    const NOT_USERS_COMMENT   = "policy.comments.not_users_comment";
    const PAST_EDIT_DEADLINE  = "policy.comments.past_edit_deadline";

    public function execute($user, $data)
    {
        $comment = $data['comment'];

        if ($user->isAn('admin'))
        {
            return TRUE
        }

       if (time() > $comment->editTimeLimit)
       {
            return self::PAST_EDIT_DEADLINE;
       }

       if ($user->owns($comment))
       {
            return TRUE;
       }
       else
       {
            return self::NOT_USERS_COMMENT
       }
    }
}
````

As you see, you can have a policy deny permission for any number of reasons and provide a specific error stating why. I like to use class constants to hold message keys that I can later feed to `Lang::get()` for an actual, clean message.  Added benefit: ease of translation.

### Installation
1: Grab everything via composer

````js
"require" : {
    "rookwood/turnstile" : "dev-master"
}
````

2: Add `Rookwood\Turnstile\TurnstileServiceProvider` to the list of service providers in `app/config/app.php`

3: Publish configuration and run migrations. Note that your users table migration must be run before the role_user pivot table can be created.  It is dated sufficiently far enough into the future that this shouldn't be a problem as long as you have already created the migration file for your users table.

````bash
$ php artisan config:publish rookwood/turnstile
$ php artisan migrate --package="rookwood/turnstile"
````

4: Create your PolicyProvider file (can be put anywhere as long as the autoloader can find it).

````php

<?php namespace Wherever\You\Want\It;

use Rookwood\Turnstile\Policies\Provider\PolicyProvider as BaseProvider;

class PolicyProvider extends BaseProvider {

    public static $data = [
        // Add your policy mappings here
        "edit_comment" => "Rookwood\\Policies\\Comments\\CommentEditingPolicy",

        // etc
    ];
}
````

5: Edit your `config/packages/rookwood/turnstile/turnstile.php` to have the namespace key point to your provider's namespace. That's how the PolicyServiceProvider class will know where to find it.

6: Have your User model use the `Rookwood\Turnstile\User\PolicyTrait`.

7: Make sure that any controller necessary has access to the current user object.  I usually do it via the constructor like thus:

````php
public function __construct()
{
    if (Auth::check())
    {
        $this->user = Auth::user();
    }
    else
    {
        $this->user = App::make('User');
    }
}
````

8: In `app/config/app.php` add an alias for `Role` to `Rookwood\Turnstile\User\Role`. As an alternative, you can create your own Role class as long as you have a m:n relationship with your User class. If your User class is namespaced, do the same for it.

### Usage
Now that you've done all the set-up, actually using this is fairly easy. Any time you want to see if the user is allowed to do something, you just call `$user->can('do_something', array('someData' => 'data needed for evaluation'))`.  This will return a boolean.  If it's False, you can get the status code sent by the policy at `Rookwood\Turnstile\Policies\Policy::$policyFailureState`.  Probably makes sense to import that namespace and have a separate method or class to deal with failures.  Depending on what the user was trying to do, it might even make sense to set a 403 NOT AUTHORIZED on the response header.  Obviously this makes more sense for actions trying to access a sensitive area of the site vs checking to see if the user can see a registration link.

Then you just create your policy class with the execute method and have it return either TRUE or an error status (see example above).  Add the action name and policy class to the PolicyProvider, and you're good to go.

The PolicyTrait included for your User model provides a few useful methods to help your policy classes and user roles:

````php
$user->owns($object)
````
Test if a relationship exists between the user and the provided object in the database:

````php
$user->isA($role)
````
Test if a user has a particular role.  `isAn()` is also available for people like me who would frown at something like `isA('admin')`:

````php
$user->addRole($role)
````
Add a role to a user. Can pass either a string of the role name or a role object. `removeRole($role)` is also available.