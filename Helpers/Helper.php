<?php
	
	namespace Plg\Pro_critical;
	use Joomla\CMS\Factory;
    use Joomla\Registry\Registry;
	use JFactory;
	use JLoader;
	
	use JUri;
	use JSession;
	use JText;
	use JResponseJson;
	use Exception;
	use Joomla\CMS\Component\ComponentHelper;
	
	// No direct access to this file
	defined( '_JEXEC' ) or die( 'Restricted access' );

    /**
     *
     * @since       3.9
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 * @package     Plg\Pro_critical
	 */
	class Helper
	{
        /**
         * @var Assets Class обработка рессурсов
         * @since 3.9
         */
	    protected $HelpersAssets ;

		public static $instance;

        /**
         * Параметры компонента COM_PRO_CRITICAL
         * @var Registry
         * @since 3.9
         */
        protected $paramsComponent;

        /**
         * @var string - Ключ страницы
         * @since 3.9
         */
		public static $PageKey ;


		private $app;
		
		private $params;

		private $GNZ11_js;
        /**
         * Имя компонента для вызова модели
         * @since 3.9
         * @var string
         */
        public static $component = 'pro_critical';
        public static $prefix = 'pro_critical' . 'Model';
        /**
         * @var object класс работы с Html заданиями
         * @since 3.9
         */
        protected $HelpersHtml;
        private $db;
        /**
         * @var HelperCache
         * @since 3.9
         */
        public $HelperCache;

        /**
		 * helper constructor.
		 *
		 * @param $params
		 *
		 * @throws Exception
		 * @since 3.9
		 */
		private function __construct ( $params  )
		{
            $this->app = JFactory::getApplication();
			$this->params = $params ;
            $this->db = Factory::getDbo();


            # Если работать по настройкам компонента
			if( !$this->params->get('is_none_component' , false ) )
            {
                $Component = ComponentHelper::getComponent('com_pro_critical', $strict = true);
                # Если компонент не найден
                if(!$Component->id ){
                    $mes = 'Для правильной работы <b>плагина Pro Critical</b> - должен быть установлен и включен <b>компонент Pro Critical</b>' ;
                    if( $this->app->input->get('format' , 'html' , 'STRING') == 'json' ) {
                        $mes ='';
                    } #END IF
                    throw new Exception( $mes , 1000 );
                }

                $this->paramsComponent = ComponentHelper::getParams( 'com_pro_critical' );
                // Добавить в настройки компонента параметры плагина
                $this->paramsComponent->set('plugin_param' , $this->params ) ;

                \JLoader::registerNamespace( 'Com_pro_critical\Helpers' ,JPATH_ADMINISTRATOR . '/components/com_pro_critical/com_pro_critical/helpers' , $reset = false , $prepend = false , $type = 'psr4' );
            }#END IF

            JLoader::registerNamespace('Plg\Pro_critical\Helpers\Assets', JPATH_PLUGINS.'/system/pro_critical/Helpers/Assets',$reset=false,$prepend=false,$type='psr4');
            $this->HelperCache = HelperCache::instance( $this->paramsComponent );
            $this->getPageKey();

            
            
            $this->HelpersAssets = \Plg\Pro_critical\Assets::instance( $this->paramsComponent );
            $this->HelpersHtml = \Plg\Pro_critical\Html::instance( $this->paramsComponent );


			return $this;
		}#END FN
	
		/**
		 * @param   Registry  $params
		 *
		 * @return helper
		 * @throws Exception
		 * @since 3.9
		 */
		public static function instance ( $params = null )
		{
			if( !$params ) { $params = new \Joomla\Registry\Registry; }#END IF
			if( self::$instance === null )
			{
				self::$instance = new self(  $params  );
			}
			return self::$instance;
		}#END FN

        /**
         * Создать ключ CCSS текущей страницы
         * @since 3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date 08.10.2020 05:37
         * TODO Добавить в настройки компонента добавление параметров для создания ключей
         */
        public function getPageKey()
        {
            

            # TODO Реренести в плаги группы   pagecache
            # $session =  Factory::getSession() ;
            # $list_style = $session->get( 'list_style' , 'tmp_table' );

            self::$PageKey = $this->HelperCache->getCacheKey();

            if ( !self::$PageKey )
            {
                if (!$this->paramsComponent)
                {
                    $Component = ComponentHelper::getComponent('com_pro_critical', $strict = true);
                    $this->paramsComponent = ComponentHelper::getParams( 'com_pro_critical' );
                }#END IF


                $client = new \Joomla\Application\Web\WebClient();

                $arrInput = [
                    'option' => 'STRING',
                    'controller' => 'STRING',
                    'task' => 'STRING',
                    'view' => 'STRING',
                ];

                $parts = $this->app->input->getArray($arrInput);
                
                $parts['mobile'] = $client->__get('mobile') ;
            

                foreach ( $this->paramsComponent->get('additional_request_parameters_ccss' , []) as $item)
                {
                    $p_query = $this->app->input->get( $item->query , $item );
                    $parts['additional_request_parameters'][$item->query] = $p_query ;
                }#END FOREACH

//                echo'<pre>';print_r( $this->paramsComponent->get('additional_request_parameters_ccss' , []) );echo'</pre>'.__FILE__.' '.__LINE__;
//                echo'<pre>';print_r( $parts );echo'</pre>'.__FILE__.' '.__LINE__;
//                die(__FILE__ .' '. __LINE__ );



                self::$PageKey = md5(serialize($parts));


            }#END IF

            


            
            return self::$PageKey ;
        }

        public function AfterInitialise(){ }

		/**
         * Перед созданием HEAD
		 *
		 * @throws Exception
		 * @since version
		 */
		public function BeforeCompileHead()
        {
             if ($this->app->isClient('administrator') )  return ; #END IF

            $doc = JFactory::getDocument();
            $DefaultLanguage = \Plg\Pro_critical\Helper_site::getDefaultLanguage();
            $languages = \JLanguageHelper::getLanguages('lang_code');
            $doc->addScriptOptions('langSef', $languages[$DefaultLanguage]->sef);

            $menu = \JFactory::getApplication()->getMenu();
            $active = $menu->getActive();
            $doc->addScriptOptions('itemId', (!empty($active) ? $active->id : false));

            if ( $this->params->get('virtuemart_enable' , 0 ) )
            {
                $Component_virtuemart = ComponentHelper::getComponent('com_virtuemart', $strict = true);
                if( !$Component_virtuemart->id )
                {
                    if( !class_exists('VmConfig') )
                        require(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
                    \VmConfig::loadConfig();
                }
            }#END IF

            # instance GNZ11
            $this->GNZ11_js = \GNZ11\Core\Js::instance($this->paramsComponent);
            # Утановить настройки библионтеки GNZ11
            $doc->addScriptOptions('siteUrl', JUri::root());
            $doc->addScriptOptions('isClient', $this->app->isClient('administrator'));
            $doc->addScriptOptions('csrf.token', JSession::getFormToken());

            $__v = $this->params->get('__v');
            \GNZ11\Core\Js::addJproLoad(\Joomla\CMS\Uri\Uri::root().'plugins/system/pro_critical/assets/js/proCriticalCore.js?v=' . $__v );

            $this->loadDummyStyle();

            ############################################################################################################
            # Только для администратора
            if( !$this->app->isClient('administrator') )
                return;

            if( !$this->params->get('is_none_component', false) )
            {
                # установка ресурсов для админ панели
                \Com_pro_critical\Helpers\helper::settingsAdminViews();
            }#END IF

            /*if( $this->app->input->get('option') == 'com_pro_critical' )
            {

            }#END IF*/

        }

        /**
         * Загрузить стили для манекенов если установлены задания - Заменять элементы манекенами
         * @since  3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date   28.01.2021 01:41
         *
         */
        public function loadDummyStyle(){
            $__v = $this->params->get('__v');
            $doc = Factory::getDocument();
            $Query = $this->db->getQuery(true);
            $Query->select('*')->from( $this->db->quoteName('#__pro_critical_html_task' , 't' ) );
            $where = [
                $this->db->quoteName( 't.html_processing') . '='. $this->db->quote( 'element_temlating_and_replace' ) ,
                $this->db->quoteName( 't.published') . '='. $this->db->quote( 1 ) ,
            ];
            $Query->where($where);
            $this->db->setQuery( $Query ) ;
            $tasks = $this->db->loadObjectList();
            if( count( $tasks ) )
            {
                $doc->addStyleSheet(\Joomla\CMS\Uri\Uri::root().'plugins/system/pro_critical/assets/css/dummy-style.css?v=' . $__v);
            }#END IF



        }


		
		/**
		 * После рендеринга страницы Собираем информауию о скриптах JS и CSS
		 *
		 * @throws Exception
		 * @since version
		 */
		public function AfterRender(){

		    # Если нет настроек компонента
            if ( !$this->paramsComponent ) return true; #END IF

            # Загрузка тела страницы в DOMDocument
            $this->HelpersAssets->InitDOM();

            # Выполнение Html заданий
            $this->HelpersHtml->Run();

            # Извлечение всех ресурсов JS && CSS Со страницы
            $this->HelpersAssets->getAllAccessList();

            # Установить найденные ресурсы JS && CSS в тело страницы
            $this->HelpersAssets->setAssetsToPage();

            # Устанавливаем отобранные теги <template />
            $this->HelpersHtml->setTemplateCollection();

            # Установить найденные ресурсы JS && CSS в тело страницы
            $this->HelpersAssets->setOverAssetsToPage();

            try
            {
                // Code that may throw an Exception or Error.
                ### Сохранить тело страницы
                $this->HelpersAssets->saveBody();
                // throw new Exception('Code Exception '.__FILE__.':'.__LINE__) ;
            }
            catch (Exception $e)
            {
                // Executed only in PHP 5, will not be reached in PHP 7
                echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__;
                die(__FILE__ .' '. __LINE__ );
            }

            # Создаем кеш если нужно
            $this->HelperCache->_onAfterRender();

			return true ;
		}
		
		/**
		 * Точка входа Ajax
		 *
		 * @since version
		 */
        public function onAjax()
        {


            # Проверить Token
            # TODO - доделать ддля работы с влюченным кешем
//            if (!JSession::checkToken('get')) exit('Err - check Token');




            $task = $this->app->input->get('task', false, 'RAW');
            $model= $this->app->input->get('model', false, 'RAW');

            if (!$model)
            {
                echo new JResponseJson(false, JText::_('MODEL ERROR'), true);
                $this->app->close();
            }#END IF




            $model = '\Plg\Pro_critical' . $model;
            $obj = $model::instance( $this->paramsComponent ) ;
            $res = $obj->{$task}();

            if (!$res)
            {
                echo new JResponseJson(false, \Joomla\CMS\Language\Text::sprintf('METHOD %s:%s ERROR', $model, $task), true);
                $this->app->close();
            }#END IF
            echo new JResponseJson($res);
            $this->app->close();


        }
		
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	