<?php

namespace MauticPlugin\GautitClearCacheBundle\Integration;


use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Integration\AbstractIntegration;

/**
 * Class GautitClearCacheIntegration.php
 */
class GautitClearCacheIntegration extends AbstractIntegration
{
	const INTEGRATION_NAME = 'GautitClearCache';


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::INTEGRATION_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return self::INTEGRATION_NAME;
    }

   /**
     * Return's authentication method such as oauth2, oauth1a, key, etc.
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        // Just use none for now and I'll build in "basic" later
        return 'none';
    }

}


