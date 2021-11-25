<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Login;

use CleverReach\Plugin\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceContract;
use CleverReach\Plugin\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\Plugin\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\Plugin\Services\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\Plugin\Services\BusinessLogic\Config\CleverReachConfig;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Render CleverReach landing page if token doesn't exists.
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        if ($this->isAuthInfoExisting()) {
            $this->_redirect('cleverreach/dashboard/index');
        }

        $resultPage->setActiveMenu(CleverReachConfig::MENU_ID);

        return $resultPage;
    }

    /**
     * @return bool
     */
    private function isAuthInfoExisting(): bool
    {
        try {
            $this->getAuthorizationService()->getAuthInfo();

            return true;
        } catch (BaseException $e) {
            return false;
        }
    }

    /**
     * @return AuthorizationService
     */
    private function getAuthorizationService(): AuthorizationService
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ServiceRegister::getService(AuthorizationServiceContract::CLASS_NAME);
    }
}
