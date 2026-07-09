<?php

namespace App\Ldap\Rules;

use Illuminate\Database\Eloquent\Model as Eloquent;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapRecord;
use Illuminate\Support\Facades\Log;

class Moderadores implements Rule
{
    /**
     * Check if the rule passes validation.
     */
    public function passes(LdapRecord $user, Eloquent $model = null): bool
    {
 Log::info('Usuario LDAP', [
            'dn' => $user->getDn(),
        ]);

        foreach ($user->groups as $group) {
            Log::info('Grupo', [
                'dn' => $group->getDn(),
            ]);
        }

        $isInGroup = $user->groups()->exists(
            'CN=moderadoresdeblitzvideo,OU=Blitzcode-dev,DC=Blitzcode,DC=company'
        );

        Log::info('¿Pertenece al grupo?', [
            'resultado' => $isInGroup,
        ]);

        return $isInGroup;
    }
}
