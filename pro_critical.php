<?php
	/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
					Gartes
	/-------------------------------------------------------------------------------------------------------/
	
		@version		1.x.x
		@build			30th октября, 2019
		@created		5th мая, 2019
		@package		proCritical
		@subpackage		pro_critical.php
		@author			Nikolaychuk Oleg <https://nobd.ml>
		@copyright		Copyright (C) 2019. All Rights Reserved
		@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____
	 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
	.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(
	\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__)
	
	/------------------------------------------------------------------------------------------------------*/
	
	// No direct access to this file
	defined( '_JEXEC' ) or die( 'Restricted access' );
	
	
	use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Plugin\CMSPlugin;
    use Plg\Pro_critical\HelperCache;

    /**
	 * System - Pro_critical plugin.
	 *
	 * @since     1.0.4
	 * @package   Pro_critical
	 */
	class PlgSystemPro_critical extends CMSPlugin
	{
		/**
		 * @since 3.7
		 * @var CMSApplication
		 */
		private $app ;
		/**
		 * Экземпляр основного хелпера
		 * @since 3.7
		 * @var Plg\Pro_critical\Helper
		 */
		public $Helper;
		/**
		 * Принудительное отключение в случае ошибки
		 * @since version
		 * @var bool
		 */
		private $SLEEP = false ;

		protected $patchGnz11 = JPATH_LIBRARIES . '/GNZ11' ;
        /**
         * @var HelperCache
         * @since 3.9
         */
        private $HelperCache;

        /**
		 * Constructor.
		 *
		 * @param   object  &$subject  The object to observe.
		 * @param   array    $config   An optional associative array of configuration settings.
		 *
		 * @throws Exception
		 * @since   3.7
		 */
		public function __construct ( &$subject , $config )
		{
//		    die(__FILE__ .' '. __LINE__ );

			parent::__construct( $subject , $config );

			// Get the application if not done by JPlugin.
            if (!isset($this->app))
            {
                $this->app = JFactory::getApplication();
            }

//            echo'<pre>';print_r( $this->patchGnz11 );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
//            die(__FILE__ .' '. __LINE__ );


            # TODO - Добавить исключение - на случай если не установлена библиотека GNZ11 (try )
            JLoader::registerNamespace( 'GNZ11' , $this->patchGnz11 , $reset = false , $prepend = false , $type = 'psr4' );
            JLoader::registerNamespace( 'Plg\Pro_critical' , JPATH_PLUGINS . '/system/pro_critical/Helpers' , $reset = false , $prepend = false , $type = 'psr4' );
            try
            {
                // Code that may throw an Exception or Error.
                $this->Helper = \Plg\Pro_critical\Helper::instance( $this->params );
                // throw new Exception('Code Exception '.__FILE__.':'.__LINE__) ;
            }
            catch (Exception $e)
            {
                $this->app->enqueueMessage($e , 'message');
                // Executed only in PHP 5, will not be reached in PHP 7
//                echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
//                echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__;
//                die(__FILE__ .' '. __LINE__ );
            }



            $this->HelperCache = HelperCache::instance( $this->params );
            $this->HelperCache = $this->Helper->HelperCache ;

        }
		
		/**
		 * Initialise the application.
		 * Trigger the onAfterInitialise event.
		 * @return  void
		 * @throws Exception
		 * @since   3.2
		 */
		public function onAfterInitialise ()
		{

            JDEBUG ? JProfiler::getInstance('Application')->mark('PLG (pro_critical) BeforeLoad => onAfterInitialise') : null;

            if ( \Joomla\CMS\Factory::getDocument()->getType() !== 'html' )
            {
                $this->SLEEP = true;
                return;
            }#END IF

            /**
             * Инит Кеш
             */
            $this->Helper->HelperCache->_onAfterInitialise();





            JDEBUG ? JProfiler::getInstance('Application')->mark('PLG (pro_critical) AfterLoad => onAfterInitialise') : null;

        }
		
		/**
		 * Route the application.
		 * Trigger the onAfterRoute event.
		 * @return bool
		 * @since   3.2
		 */
		public function onAfterRoute ()
		{
			if( $this->SLEEP ) return false ; #END IF

            $data = [
                '__name' => $this->_name ,
                '__type' => $this->_type ,
                '__v' => $this->params->get('__v') ,
            ] ;

            \Joomla\CMS\Factory::getDocument()->addScriptOptions('pro_critical' , $data , true ) ;
            $this->params->set('is_none_component' , false ) ;
            $this->Helper = \Plg\Pro_critical\Helper::instance( $this->params );
            return true;
		}
		
		/**
		 * Перед созданием HEAD
		 * @return bool
		 * @throws Exception
		 * @throws Throwable
		 * @since     3.8
		 * @copyright 06.12.18
		 * @author    Gartes
		 */
		public function onBeforeCompileHead ()
		{

            if( $this->SLEEP ) return false ; #END IF
            try
            {
                $this->Helper->BeforeCompileHead();
            }
            catch (Exception $e)
            {
                // Executed only in PHP 5, will not be reached in PHP 7
                echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__;
                die(__FILE__ .' '. __LINE__ );
            }
            return true;
		}

		public function onContentPrepare( $context, $article, $params){ }

		/**
		 * Trigger the onBeforeRender event.
		 * Рендеринг - это процесс вставки буферов документов в шаблон.
		 * заполнители, извлекающие данные из документа и помещающие их в
		 * буфер ответа приложения.
		 *
		 * @since 3.2
		 */
		public function onBeforeRender(){}
		
		/**
		 * Trigger the onAfterRender event.
		 *
		 * @return bool
		 * @throws Exception
		 * @since   3.2
		 */
		public function onAfterRender ()
		{


            if( $this->SLEEP ) return false ; #END IF

			# Если Админ Панель
			if( $this->app->isClient( 'administrator' ) ) return true; #END IF

            $this->Helper->AfterRender();



            return true;
		}
		

		
		/**
		 * Trigger the onAfterRespond event.
		 * onAfterRespond.
		 * После ответа приложения клиенту перед закрытием приложения
		 * @return bool
		 * @since   1.7.3
		 *
		 */
		public function onAfterRespond ()
		{
			if( $this->SLEEP ) return false ; #END IF

            $this->Helper->HelperCache->_onAfterRespond();
			return true;
		}

        /**
         * Trigger the onAfterCompress event.
         * Если в конфигурации включено сжатие gzip и сервер совместим.
         * @return bool
         * @since   3.2
         */
        public function onAfterCompress ()
        {
            if( $this->SLEEP ) return false ; #END IF

            return true;
        }



		/**
		 * Точка входа Ajax
		 *
		 * @since   3.2
		 */
		public function onAjaxPro_critical ()
		{
            $this->Helper = \Plg\Pro_critical\Helper::instance( $this->params );
		    $this->Helper->onAjax();
			
		}
		
		
	}
