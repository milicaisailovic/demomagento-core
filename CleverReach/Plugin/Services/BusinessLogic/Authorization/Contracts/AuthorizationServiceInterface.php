<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Authorization\Contracts;

interface AuthorizationServiceInterface
{
    /**
     * Call proxy for authorization.
     *
     * @param string $code
     */
    public function authorize(string $code): void;
}
