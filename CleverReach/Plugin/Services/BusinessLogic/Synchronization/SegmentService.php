<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Segment\DTO\Segment;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Segment\SegmentService as BaseSegmentService;

class SegmentService extends BaseSegmentService
{
    /**
     * SegmentService constructor.
     */
    public function __construct()
    {
    }

    /**
     * Retrieves list of available segments.
     *
     * @return Segment[] The list of available segments.
     */
    public function getSegments(): array
    {
        return [];
    }
}
