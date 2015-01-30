<?php
namespace Rshop\Synchronization\Superfaktura;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Superfaktura Client
 *
 * @author Tomas Saghy <segy@riesenia.com>
 */
class Client extends ApiObject
{
    /**
     * Configure options for options resolver
     *
     * @param Symfony\Component\OptionsResolver\OptionsResolver
     */
    protected function _configureOptions(OptionsResolver $resolver)
    {
        // available options
        $resolver->setDefined(array('address', 'bank_account', 'city', 'country_id', 'country', 'delivery_address', 'delivery_city', 'delivery_country', 'delivery_country_id', 'delivery_name', 'delivery_zip', 'dic', 'email', 'fax', 'ic_dph', 'ico', 'name', 'phone', 'zip'));
    }
}
