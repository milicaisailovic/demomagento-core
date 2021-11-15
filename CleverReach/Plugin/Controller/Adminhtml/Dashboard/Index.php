<?php

namespace CleverReach\Plugin\Controller\Adminhtml\Dashboard;

use CleverReach\Plugin\Bootstrap;
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

        Bootstrap::init();

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Return CleverReach dashboard page.
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(CleverReachConfig::MENU_ID);

        return $resultPage;
    }
}
