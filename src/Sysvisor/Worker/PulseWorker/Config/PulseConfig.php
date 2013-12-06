<?php

namespace Sysvisor\Worker\PulseWorker\Config;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sysvisor\Config\Config as BaseConfig;

class PulseConfig extends BaseConfig
{
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configure(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'username',
            'password',
            'url'
        ));

        $resolver->setOptional(['namespace']);

        $resolver->setDefaults(array(
            'namespace' => ''
        ));
    }
}