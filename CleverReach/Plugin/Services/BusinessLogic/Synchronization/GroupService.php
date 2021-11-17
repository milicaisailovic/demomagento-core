<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Group\GroupService as BaseGroupService;

class GroupService extends BaseGroupService
{
    /**
     * GroupService constructor.
     */
    public function __construct()
    {
    }

    /**
     * Retrieves integration specific group name.
     *
     * @return string Integration provided group name.
     */
    public function getName(): string
    {
        return 'Magento 2.3';
    }

    /**
     * Retrieves integration specific blacklisted emails suffix.
     *
     * @NOTICE SUFFIX MUST START WITH DASH (-)!
     *
     * @return string Blacklisted emails suffix.
     */
    public function getBlacklistedEmailsSuffix(): string
    {
        return '-';
    }
}
