<?php

namespace CleverReach\Plugin\Services\BusinessLogic\Synchronization;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Mailing\Contracts\DefaultMailingService;
use CleverReach\Plugin\IntegrationCore\BusinessLogic\Mailing\DTO\MailingContent;

class MailingService implements DefaultMailingService
{
    /**
     * MailingService constructor.
     */
    public function __construct()
    {
    }

    /**
     * Provides default mailing name.
     *
     * @return string Default mailing name.
     */
    public function getName(): string
    {
        return 'mailingName';
    }

    /**
     * Provides default mailing subject.
     *
     * @return string Default mailing subject.
     */
    public function getSubject(): string
    {
        return 'mailingSubject';
    }

    /**
     * Provides default mailing content.
     *
     * @return MailingContent Content of the default mailing.
     */
    public function getContent(): MailingContent
    {
        return new MailingContent();
    }
}
