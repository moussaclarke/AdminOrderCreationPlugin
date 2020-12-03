<?php

declare (strict_types = 1);

namespace Sylius\AdminOrderCreationPlugin\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;

final class AdjustmentType extends AbstractResourceType
{
    public const ORDER_ADJUSTMENT = 'order_adjustment';

    public const ORDER_ITEM_ADJUSTMENT = 'order_item_adjustment';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('amount', MoneyType::class, [
            'label'    => $options['label'],
            'currency' => $options['currency'],
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options): void {
            $adjustment = $event->getData();

            if ($adjustment === null) {
                return;
            }

            $adjustment->setLabel('sylius_admin_order_creation.ui.order_adjustment');
            $adjustment->setAmount($adjustment->getAmount());
            $adjustment->setType($options['type']);

            $event->setData($adjustment);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('label');
        $resolver->setRequired('currency');
        $resolver->setRequired('type');
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_admin_order_creation_new_order_adjustment';
    }
}
