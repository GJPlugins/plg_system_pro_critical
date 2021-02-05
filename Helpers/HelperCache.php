<?php

    /*******************************************************************************************************************
     *     ╔═══╗ ╔══╗ ╔═══╗ ╔════╗ ╔═══╗ ╔══╗        ╔══╗  ╔═══╗ ╔╗╔╗ ╔═══╗ ╔╗   ╔══╗ ╔═══╗ ╔╗  ╔╗ ╔═══╗ ╔╗ ╔╗ ╔════╗
     *     ║╔══╝ ║╔╗║ ║╔═╗║ ╚═╗╔═╝ ║╔══╝ ║╔═╝        ║╔╗╚╗ ║╔══╝ ║║║║ ║╔══╝ ║║   ║╔╗║ ║╔═╗║ ║║  ║║ ║╔══╝ ║╚═╝║ ╚═╗╔═╝
     *     ║║╔═╗ ║╚╝║ ║╚═╝║   ║║   ║╚══╗ ║╚═╗        ║║╚╗║ ║╚══╗ ║║║║ ║╚══╗ ║║   ║║║║ ║╚═╝║ ║╚╗╔╝║ ║╚══╗ ║╔╗ ║   ║║
     *     ║║╚╗║ ║╔╗║ ║╔╗╔╝   ║║   ║╔══╝ ╚═╗║        ║║─║║ ║╔══╝ ║╚╝║ ║╔══╝ ║║   ║║║║ ║╔══╝ ║╔╗╔╗║ ║╔══╝ ║║╚╗║   ║║
     *     ║╚═╝║ ║║║║ ║║║║    ║║   ║╚══╗ ╔═╝║        ║╚═╝║ ║╚══╗ ╚╗╔╝ ║╚══╗ ║╚═╗ ║╚╝║ ║║    ║║╚╝║║ ║╚══╗ ║║ ║║   ║║
     *     ╚═══╝ ╚╝╚╝ ╚╝╚╝    ╚╝   ╚═══╝ ╚══╝        ╚═══╝ ╚═══╝  ╚╝  ╚═══╝ ╚══╝ ╚══╝ ╚╝    ╚╝  ╚╝ ╚═══╝ ╚╝ ╚╝   ╚╝
     *------------------------------------------------------------------------------------------------------------------
     *
     * @author     Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date       29.01.2021 07:20
     * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
     * @license    GNU General Public License version 2 or later;
     ******************************************************************************************************************/

    namespace Plg\Pro_critical;
    defined('_JEXEC') or die; // No direct access to this file

    use Exception;
    use JDatabaseDriver;
    use Joomla\CMS\Application\CMSApplication;
    use Joomla\CMS\Cache\Cache;
    use Joomla\CMS\Component\ComponentHelper;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Plugin\PluginHelper;
    use Joomla\CMS\Profiler\Profiler;
    use Joomla\CMS\Uri\Uri;

    /**
     * Class HelperCache
     *
     * @package Plg\Pro_critical
     * @since   3.9
     * @auhtor  Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date    29.01.2021 07:20
     *
     */
    class HelperCache
    {

        /**
         * @var CMSApplication|null
         * @since 3.9
         */
        private $app;
        /**
         * @var JDatabaseDriver|null
         * @since 3.9
         */
        private $db;

        /**
         * Cache instance.
         *
         * @var    Cache
         * @since  1.5
         */
        public $_cache;

        /**
         * Cache key
         *
         * @var    string
         * @since  3.0
         */
        public $_cache_key;


        /**
         * Array to hold the object instances
         *
         * @var HelperCache
         * @since  1.6
         */
        public static $instance;
        private $params;

        /**
         * HelperCache constructor.
         *
         * @param $params array|object
         *
         * @throws Exception
         * @since 3.9
         */
        public function __construct($params)
        {
            $this->params = $params ;
            $this->paramsComponent = ComponentHelper::getParams( 'com_pro_critical' );




            $this->app = Factory::getApplication();
            $this->db = Factory::getDbo();
        
            // Set the cache options.
            $options = array(
                'defaultgroup' => 'page',
                'browsercache' => $this->paramsComponent->get('browsercache', 0),
                'caching'      => false,
            );

            // Instantiate cache with previous options and create the cache key identifier.
            $this->_cache     = Cache::getInstance('page', $options);
            $this->_cache_key = Uri::getInstance()->toString();






            return $this;
        }

        /**
         * @param array $options
         *
         * @return HelperCache
         * @throws Exception
         * @since 3.9
         */
        public static function instance($options = array())
        {
            if( self::$instance === null )
            {
                self::$instance = new self($options);
            }
            return self::$instance;
        }

        public function _onAfterInitialise(){



            if ($this->app->isClient('administrator') || $this->app->get('offline', '0') || $this->app->getMessageQueue())
            {
                return;
            }
            # Если кеширование отключено в настройках компонента
            if( !$this->paramsComponent->get('cache_on' , 0 ) ) return ; #END IF

            try
            {
              $HelperCssCritical =   \Plg\Pro_critical\Helpers\Assets\Css_critical::instance( $this->paramsComponent ) ;
              $checkCCSS = $HelperCssCritical->getCriticalCss() ;

            } catch (Exception $e)
            {
                echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
                die(__FILE__ .' '. __LINE__ );
            }


            // If any pagecache plugins return false for onPageCacheSetCaching, do not use the cache.
            PluginHelper::importPlugin('pagecache');

            $results = \JEventDispatcher::getInstance()->trigger('onPageCacheSetCaching');
            $caching = !in_array(false, $results, true);

            if ( $caching && Factory::getUser()->guest && $this->app->input->getMethod() === 'GET')
            {
                $this->_cache->setCaching(true);
            }

            $data = $this->_cache->get( $this->getCacheKey() );




            // If page exist in cache, show cached page.
            if ($data !== false)
            {
                // Set HTML page from cache.
                $this->app->setBody($data);

                // Dumps HTML page.
                echo $this->app->toString((bool) $this->app->get('gzip'));

                // Mark afterCache in debug and run debug onAfterRespond events.
                // e.g., show Joomla Debug Console if debug is active.
                if (JDEBUG)
                {
                    Profiler::getInstance('Application')->mark('afterCache');
                    \JEventDispatcher::getInstance()->trigger('onAfterRespond');
                }

                // Closes the application.
                $this->app->close();
            }
        }

        /**
         * После события рендеринга.
         * Убедитесь, что текущая страница не исключена из кеша.
         *
         * @return   void
         *
         * @since   3.9.12
         */
        public function _onAfterRender(){

            # Загружаем CriticalCss
            $HelperCssCritical =   \Plg\Pro_critical\Helpers\Assets\Css_critical::instance( $this->paramsComponent ) ;
            $CCSS = $HelperCssCritical->getCriticalCss() ;
            


            # Если кеширование отключено системой
            if ($this->_cache->getCaching() === false  ) return; # END IF

            # критические стили не созданы - не создаем  КЕШ для страницы
            if( !$CCSS )
            {
                $this->_cache->setCaching(false);
                return;
            }#END IF

            // Нам нужно проверить, является ли пользователь сейчас гостем,
            // потому что плагины для автоматического входа в систему не были запущены до проверки первой помощи.
            // Страница исключается, если исключена в настройках плагина.
            if (!Factory::getUser()->guest || $this->app->getMessageQueue() || $this->isExcluded() === true)
            {
                $this->_cache->setCaching(false);
                return;
            }

            // Отключите сжатие перед кешированием страницы.
            $this->app->set('gzip', false);
        }

        /**
         * After Respond Event.
         * Stores page in cache.
         *
         * @return   void
         *
         * @since   1.5
         */
        public function _onAfterRespond()
        {
            if ($this->_cache->getCaching() === false)
            {
                return;
            }

            // Сохраняет текущую страницу в кеше.
            $this->_cache->store($this->app->getBody(), $this->getCacheKey());
        }

        /**
         * Получите ключ кеша для текущей страницы на основе URL-адреса
         * и возможных других факторов.
         *
         * @return  string
         *
         * @since   3.7
         */
        public function getCacheKey(): string
        {
            static $key;

            if (!$key)
            {


                $WebClient = new \Joomla\Application\Web\WebClient();
                PluginHelper::importPlugin('pagecache');

                $parts = \JEventDispatcher::getInstance()->trigger('onPageCacheGetKey');
                $parts[] = Uri::getInstance()->toString();
                $parts[] = $WebClient->__get('mobile');
                






                $key = md5(serialize($parts));
            }

            return $key;
        }

        /**
         * Проверьте, исключена ли страница из кеша.
         *
         * @return   boolean  Истина, если страница исключена, иначе ложь
         *
         * @since    3.5
         */
        protected function isExcluded()
        {



            // Check if menu items have been excluded.
            if ($exclusions = $this->paramsComponent->get('cache_exclude_menu_items', array()))
            {
                // Get the current menu item.
                $active = $this->app->getMenu()->getActive();

                if ($active && $active->id && in_array((int) $active->id, (array) $exclusions))
                {
                    return true;
                }
            }

            // Check if regular expressions are being used.
            if ($exclusions = $this->paramsComponent->get('cache_exclude', ''))
            {
                // Normalize line endings.
                $exclusions = str_replace(array("\r\n", "\r"), "\n", $exclusions);

                // Split them.
                $exclusions = explode("\n", $exclusions);

                // Gets internal URI.
                $internal_uri	= '/index.php?' . Uri::getInstance()->buildQuery($this->app->getRouter()->getVars());

                // Loop through each pattern.
                if ($exclusions)
                {
                    foreach ($exclusions as $exclusion)
                    {
                        // Make sure the exclusion has some content
                        if ($exclusion !== '')
                        {
                            // Test both external and internal URI
                            if (preg_match('#' . $exclusion . '#i', $this->_cache_key . ' ' . $internal_uri, $match))
                            {
                                return true;
                            }
                        }
                    }
                }
            }

            // If any pagecache plugins return true for onPageCacheIsExcluded, exclude.
            PluginHelper::importPlugin('pagecache');

            $results = \JEventDispatcher::getInstance()->trigger('onPageCacheIsExcluded');

            return in_array(true, $results, true);
        }



    }