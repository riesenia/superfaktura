<?php
namespace Rshop\Synchronization\Superfaktura;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Superfaktura InvoiceItem
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
class InvoiceItem extends ApiObject
{
    /**
     * Configure options for options resolver
     *
     * @param Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected function _configureOptions(OptionsResolver $resolver)
    {
        // available options
        $resolver->setDefined(array('name', 'description', 'quantity', 'unit', 'unit_price', 'tax', 'stock_item_id', 'sku'));

        $resolver->setNormalizer('unit_price', $resolver->floatNormalizer);
        $resolver->setNormalizer('tax', $resolver->floatNormalizer);
    }
}
