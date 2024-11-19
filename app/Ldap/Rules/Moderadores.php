<?php

namespace App\Ldap\Rules;

use Illuminate\Database\Eloquent\Model as Eloquent;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapRecord;

class Moderadores implements Rule
{
    /**
     * Check if the rule passes validation.
     */
    public function passes(LdapRecord $user, Eloquent $model = null): bool
    {
       return $user->groups()->exists(
        'CN=moderadoresdeblitzvideo,OU=Blitzcode-dev,DC=Blitzcode,DC=company'
       );
        
    }
}
