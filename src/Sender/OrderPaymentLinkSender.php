<?php

declare (strict_types = 1);

namespace Sylius\AdminOrderCreationPlugin\Sender;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;

final class OrderPaymentLinkSender implements OrderPaymentLinkSenderInterface
{
    /** @var SenderInterface */
    private $sender;

    public function __construct(SenderInterface $sender)
    {
        $this->sender = $sender;
    }

    public function sendPaymentLink(OrderInterface $order): void
    {
        $payment = $order->getLastPayment(PaymentInterface::STATE_NEW);
        if (null === $payment) {
            return;
        }

        $paymentDetails = $payment->getDetails();
        if (!isset($paymentDetails['payment-link'])) {
            return;
        }

        assert($order->getCustomer() !== null);

        $this->sender
            ->send(
                'order_created_in_admin_panel',
                [$order->getCustomer()->getEmail()],
                [
                    'order'       => $order,
                    'paymentLink' => $paymentDetails['payment-link'],
                ]
            )
        ;
    }
}
