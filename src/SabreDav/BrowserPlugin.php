<?php

namespace Secotrust\Bundle\SabreDavBundle\SabreDav;

use Sabre\DAV\Browser\Plugin;

/**
 * Browser Plugin.
 * 
 * This file extends the sabredav-browserplugin. 
 * It's possible to manipulate the (original) SabreDAV html-output with this class.
 * 
 * @author lduer
 */
class BrowserPlugin extends Plugin
{
    /**
     * @var array
     */
    private $config = array();

    /**
     * set configuration.
     * 
     * @param array $config
     */
    public function setBrowserConfig(array $config)
    {
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getBrowserConfig($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return;
    }

    /**
     * This method returns a local pathname to an asset.
     * 
     * The logo and favicon can be overwritten
     *
     * @param string $assetName
     *
     * @return string
     */
    protected function getLocalAssetPath($assetName)
    {
        // load path to logo from parameters
        if ($assetName === 'sabredav.png' && $this->getBrowserConfig('browser_logo')) {
            return $this->getBrowserConfig('browser_logo');
        } elseif ($assetName === 'favicon.ico' && $this->getBrowserConfig('favicon')) {
            return $this->getBrowserConfig('favicon');
        }

        return parent::getLocalAssetPath($assetName);
    }
}
