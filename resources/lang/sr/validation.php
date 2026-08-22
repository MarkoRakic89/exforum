<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'required' => 'Polje :attribute je obavezno.',
    'numeric' => 'Polje :attribute mora biti broj.',
    'min' => [
        'numeric' => 'Minimalna vrednost polja :attribute je :min.',
        'string' => 'Polje :attribute mora sadržati najmanje :min karaktera.',
        'array'  => 'Morate odabrati najmanje :min stavki za polje :attribute.',
    ],
    'max' => [
        'numeric' => 'Maksimalna vrednost polja :attribute je :max.',
        'string'  => 'Polje :attribute ne sme sadržati više od :max karaktera.',
        'array'   => 'Polje :attribute ne sme imati više od :max stavki.',
    ],
    'in' => 'Odabrana vrednost za polje :attribute nije validna.',
    'date' => 'Polje :attribute mora biti validan datum.',
    'after_or_equal' => 'Polje :attribute mora biti datum nakon ili jednak datumu :date.',
    'array' => 'Polje :attribute mora biti lista (niz).',
    'exists' => 'Odabrana vrednost za polje :attribute nije validna.',
    'email' => 'Polje :attribute mora biti ispravna email adresa.',
    'unique' => 'Polje :attribute je već zauzeto.',
    'confirmed' => 'Polje :attribute mora biti potvrđeno.',
    'same' => 'Polje :attribute i :other moraju se poklapati.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        // Example: 'email.required' => 'Email je obavezan.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'type' => 'tip',
        'amount_eur' => 'iznos',
        'percent' => 'procenat',
        'repeat_type' => 'tip ponavljanja',
        'repeat_until' => 'ponavljaj do',
        'cities' => 'gradovi',
        'cities.*' => 'grad',
        'industries' => 'delatnosti',
        'industries.*' => 'delatnost',
        'maticni_broj' => 'matični broj',
        'naziv' => 'naziv kompanije',
        'email' => 'email',
        'grad_id' => 'grad',
        'password' => 'lozinka',
        'amount_reserved_eur' => 'rezervisani iznos',
    ],
];