<?php
 
namespace Dynamic\OrderEmails\Plugin\Sales\Order\Email\Container;
 
class OrderIdentityPlugin
{
    /**
     * @var \Magento\Checkout\Model\Session $checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
 
    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
    }
 
    /**
     * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject
     * @param callable $proceed
     * @return bool
     */
    public function afterIsEnabled(\Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject, $result)
    {
        $isEmailSend = $this->registry->registry('is_email_send');
        $result = false;
        if ($isEmailSend) {
            $result = true;
        }
        return $result;
    }
}