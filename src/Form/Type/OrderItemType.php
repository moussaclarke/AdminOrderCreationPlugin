<?php

declare (strict_types = 1);

namespace Sylius\AdminOrderCreationPlugin\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceAutocompleteChoiceType;

final class OrderItemType extends AbstractResourceType
{
    /** @var DataMapperInterface */
    private $dataMapper;

    public function __construct(
        string $dataClass,
        DataMapperInterface $dataMapper,
        array $validationGroups = []
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->dataMapper = $dataMapper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', IntegerType::class, [
                'attr'       => ['min' => 1],
                'label'      => 'sylius.ui.quantity',
                'empty_data' => 1,
            ])
            ->add('variant', ResourceAutocompleteChoiceType::class, [
                'label'        => 'sylius.ui.variant',
                'choice_name'  => 'descriptor',
                'choice_value' => 'code',
                'resource'     => 'sylius.product_variant',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                $event
                    ->getForm()
                    ->add('adjustments', CollectionType::class, [
                        'label'            => false,
                        'entry_type'       => AdjustmentType::class,
                        'entry_options'    => [
                            'label'    => 'sylius_admin_order_creation.ui.item_adjustment',
                            'currency' => $options['currency'],
                            'type'     => AdjustmentType::ORDER_ITEM_ADJUSTMENT,
                        ],
                        'allow_add'        => true,
                        'allow_delete'     => true,
                        'by_reference'     => false,
                        'button_add_label' => 'sylius_admin_order_creation.ui.add_adjustment',
                    ])
                ;
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $data = $event->getData();
                if (empty($data['quantity'])) {
                    $data['quantity'] = '1';
                }

                $event->setData($data);
            })
            ->setDataMapper($this->dataMapper)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setRequired('currency');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_admin_order_creation_new_order_order_item';
    }
}
