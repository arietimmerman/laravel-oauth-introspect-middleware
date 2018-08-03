<?php

namespace Laravel\Passport\Guards;

class IntrospectGuard
{
    public function __construct(Introspect $introspect)
    {
        $this->introspect = $introspect;
    }

    public function user()
    {
        $result = $this->introspect
            ->verify()
            ->getResult();

        if (!isset($result['user'])) {
            return null;
        }

        User::forceFill($result['user']);
    }
}
