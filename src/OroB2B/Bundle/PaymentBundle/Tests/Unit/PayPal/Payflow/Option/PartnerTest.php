<?php

namespace OroB2B\Bundle\PaymentBundle\Tests\Unit\PayPal\Payflow\Option;

use OroB2B\Bundle\PaymentBundle\PayPal\Payflow\Option\Partner;
use OroB2B\Bundle\PaymentBundle\PayPal\Payflow\Processor\PayPal;

class PartnerTest extends AbstractOptionTest
{
    /** {@inheritdoc} */
    protected function getOption()
    {
        return new Partner();
    }

    /** {@inheritdoc} */
    public function configureOptionDataProvider()
    {
        return [
            'empty' => [
                [],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\MissingOptionsException',
                    'The required option "PARTNER" is missing.',
                ],
            ],
            'invalid type' => [
                ['PARTNER' => 123],
                [],
                [
                    'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException',
                    'The option "PARTNER" with value 123 is invalid. Accepted values are: "PayPal", "AMEX", "MESP",' .
                    ' "NOVA", "NASH", "NORT", "SOUT", "MAPP", "NDCE", "HTLD", "LITL", "MONE", "PAYT", "TMPA", "PPAY",' .
                    ' "SNET", "VITA", "TELN", "FIFT", "WPAY".',
                ],
            ],
            'valid' => [['PARTNER' => 'PayPal'], ['PARTNER' => 'PayPal']],
        ];
    }

    public function testList()
    {
        $this->assertInternalType('array', Partner::$partners);
        $this->assertNotEmpty('array', Partner::$partners);
        $this->assertArrayHasKey(PayPal::CODE, Partner::$partners);
    }
}
