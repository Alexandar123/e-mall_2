<?php
 
namespace MGS\ThemeSettings\Plugin;
 
use Magento\Framework\Exception\LocalizedException;
class StoreConfigPlugin
{
    protected $customerSession;
 
    /**
     * Plugin constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSession
    ) {
        $this->customerSession = $customerSession;    
    }
 
    /**
     * afterAddProduct
     *
     * @param      $subject
     * @param      $result    Returned value from core observed method 'addProduct'     
     */
    public function afterGetValue($subject, $result)
    {
        return $result;  
    }
}
?>