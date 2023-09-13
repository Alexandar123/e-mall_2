<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace MGS\Portfolio\Controller;


class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
		\Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_response = $response;
		$this->_objectManager = $objectManager;
    }

	public function getModel($model){
		return $this->_objectManager->create($model);
	}

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|void|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $_identifier = trim($request->getPathInfo(), '/');

		$arrRouter = explode('/', $_identifier);

        if ($arrRouter[0]!='portfolio') {
            return;
        }

		$success = false;
		if(count($arrRouter)==1){
			$request->setModuleName('portfolio')->setControllerName('category')->setActionName('view');
            $success = true;
		}else{
			$identifier = $arrRouter[1];
			$condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);

			$this->_eventManager->dispatch(
				'mgs_portfolio_controller_router_match_before',
				['router' => $this, 'condition' => $condition]
			);
			if ($condition->getRedirectUrl()) {
				$this->_response->setRedirect($condition->getRedirectUrl());
				$request->setDispatched(true);
				return $this->actionFactory->create(
					'Magento\Framework\App\Action\Redirect',
					['request' => $request]
				);
			}

			if (!$condition->getContinue()) {
				return null;
			}

			$identifier = $condition->getIdentifier();
			$info = explode('/', $identifier);
			if(count($info) == 1){
				$category  = $this->getModel('MGS\Portfolio\Model\Category')
					->getCollection()
					->addFieldToFilter('identifier', $info[0])
					->getFirstItem();

				$portfolio  = $this->getModel('MGS\Portfolio\Model\Portfolio')
					->getCollection()
					->addFieldToFilter('identifier', $info[0])
					->addFieldToFilter('status', 1)
					->getFirstItem();

				if($category->getId() || $portfolio->getId()){
					if($portfolio->getId()){
						$request->setModuleName('portfolio')->setControllerName('index')->setActionName('view')->setParam('id', $portfolio->getId());
						$success = true;
					}else{
						$request->setModuleName('portfolio')->setControllerName('category')->setActionName('view')->setParam('id', $category->getId());
						$success = true;
					}
				}
			}
		}

        if (!$success) {
            return null;
        }
        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $_identifier);

        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }

}
