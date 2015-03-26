<?php

class MageProfis_MageSetupEmail_Model_Core_Translate
extends Mage_Core_Model_Translate
{
    const LOCALE_FOLDER = 'magesetupemail';

    // set caching ttl in seconds
    protected $_cache_ttl = 7200; // 7200 = 2 hours

    /**
     * Retrive translated template file
     * MP: replace the hole method and added cache mechanism, Files: magesetup
     *
     * @param string $file
     * @param string $type
     * @param string $localeCode
     * @return string file content
     */
    public function getTemplateFile($file, $type, $localeCode=null)
    {

        if (is_null($localeCode) || preg_match('/[^a-zA-Z_]/', $localeCode)) {
            $localeCode = $this->getLocale();
        }

        //cache Keys
        $key = $this->getCacheKey(array($localeCode, $file, $this->getConfig(self::CONFIG_KEY_STORE)));
        
        // use from cache
        $filePath = $this->_getTemplateCache($filePath);
        if($filePath && $this->_FileExists($filePath))
        {
            return $this->_readTemplateFile($filePath);
        }
        
        // use from app/locale/[localeCode]/template/custom
        $filePath = Mage::getBaseDir('locale') . DS
                . $localeCode
                . DS . 'template' . DS . 'custom' . DS . $type . DS . $file;

        if($this->_FileExists($filePath))
        {
            return $this->_readTemplateFile($filePath, $key);
        }

        // use from app/locale/[localeCode]/template/magesetupemail
        $filePath = Mage::getBaseDir('locale') . DS
                . $localeCode
                . DS . 'template' . DS . self::LOCALE_FOLDER . DS . $type . DS . $file;
        
        if($this->_FileExists($filePath))
        {
            return $this->_readTemplateFile($filePath, $key);
        }

        $filePath = Mage::getBaseDir('locale') . DS
                . Mage::app()->getLocale()->getDefaultLocale()
                . DS . 'template' . DS . self::LOCALE_FOLDER . DS . $type . DS . $file;
        
        if(Mage::app()->getLocale()->getDefaultLocale() != $localeCode && $this->_FileExists($filePath))
        {
            return $this->_readTemplateFile($filePath, $key);
        }

        $filePath = Mage::getBaseDir('locale') . DS
                . Mage_Core_Model_Locale::DEFAULT_LOCALE
                . DS . 'template' . DS . self::LOCALE_FOLDER . DS . $type . DS . $file;

        if(Mage_Core_Model_Locale::DEFAULT_LOCALE != $localeCode && $this->_FileExists($filePath))
        {
            return $this->_readTemplateFile($filePath, $key);
        }

        // orig part
        $filePath = Mage::getBaseDir('locale')  . DS
                  . $localeCode . DS . 'template' . DS . $type . DS . $file;
        if($this->_FileExists($filePath))
        {
            return $this->_readTemplateFile($filePath, $key);
        }
        // If no template specified for this locale, use store default
        $filePath = Mage::getBaseDir('locale') . DS
                  . Mage::app()->getLocale()->getDefaultLocale()
                  . DS . 'template' . DS . $type . DS . $file;
        if(Mage::app()->getLocale()->getDefaultLocale() != $localeCode && $this->_FileExists($filePath))
        {
            return $this->_readTemplateFile($filePath, $key);
        }

        // If no template specified as  store default locale, use en_US/Mage_Core_Model_Locale::DEFAULT_LOCALE
        $filePath = Mage::getBaseDir('locale') . DS
                  . Mage_Core_Model_Locale::DEFAULT_LOCALE
                  . DS . 'template' . DS . $type . DS . $file;

        return $this->_readTemplateFile($filePath, $key);
    }

    /**
     * read file
     * 
     * @param string $file
     * @param bool $cachekey
     * @return string file content
     */
    protected function _readTemplateFile($file, $cachekey=false)
    {
        if($cachekey)
        {
            $this->_setTemplateCache($cachekey, $file);
        }
        return (string)file_get_contents($file);
    }

    /**
     * 
     * @param string $key
     * @return boolean|string
     */
    protected function _getTemplateCache($key)
    {
        if($this->canUseCache())
        {
            $content = $this->_getCache()->load($key);
            if(!empty($content))
            {
               return $content; 
            }
        }
        return false;
    }

    /**
     * 
     * @param type $key
     * @param type $value
     */
    protected function _setTemplateCache($key, $value)
    {
        $this->_getCache()->save($value, $key, $this->getCacheTags(), $this->_cache_ttl);
        return true;
    }
    
    /**
     * alias for file_exists
     * 
     * @param string $file
     * @return bool
     */
    protected function _FileExists($file)
    {
        return @file_exists($file);
    }

    /**
     * 
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data = array())
    {   
        return 'email-'.md5(implode('-', $data));
    }

    /**
     * 
     * @return Zend_Cache_Core
     */
    protected function _getCache()
    {
        return Mage::app()->getCache();
    }

    /**
     * get Cache tags for Caching Model
     * 
     * @return mixed
     */
    protected function getCacheTags()
    {
        return array(
            Mage_Core_Model_Translate::CACHE_TAG,
            MageProfis_MageSetupEmail_Helper_Data::CACHE_TAG
        );
    }

    /**
     * cache with the cache keys if the cache can be used
     * 
     * @return boolean
     */
    protected function canUseCache()
    {
        foreach($this->getCacheTags() as $_tag)
        {
            if(!Mage::app()->useCache($_tag))
            {
                return false;
            }
        }
        return true;
    }
}
