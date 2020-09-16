<?php
	
	namespace Plg\Pro_critical;
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
	 * @since       3.9
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 * @package     Plg\Pro_critical
	 */
	class Helper
	{
		public static $instance;
		
		private $app;
		
		private $params;
		private $paramsComponent;
		private $GNZ11_js;
        /**
         * Имя компонента для вызова модели
         * @since 3.9
         * @var string
         */
        public static $component = 'pro_critical';
        public static $prefix = 'pro_critical' . 'Model';

		
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

			if( !$this->params->get('is_none_component' , false ) )
            {
                $Component = ComponentHelper::getComponent('com_pro_critical', $strict = true);
                if(!$Component->id ){
                    $mes = 'Для правильной работы <b>плагина Pro Critical</b> - должен быть установлен и включен <b>компонент Pro Critical</b>' ;
                    if( $this->app->input->get('format' , 'html' , 'STRING') == 'json' ) {
                        $mes ='';
                    } #END IF

                    //				$this->app->enqueueMessage($mes , 'warning');
                    throw new Exception( $mes , 1000 );
                }
                $this->paramsComponent = ComponentHelper::getParams( 'com_pro_critical' );
                $this->paramsComponent->set('plugin_param' , $this->params ) ;



                JLoader::registerNamespace( 'Com_pro_critical\Helpers' ,
                    JPATH_ADMINISTRATOR . '/components/com_pro_critical/com_pro_critical/helpers' ,
                    $reset = false , $prepend = false , $type = 'psr4' );
            }#END IF
            JLoader::registerNamespace('Plg\Pro_critical\Helpers\Assets',JPATH_PLUGINS.'/system/pro_critical/Helpers/Assets',$reset=false,$prepend=false,$type='psr4');
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
         * К ссылке этого файла будет добавлен атрибут async
		 * Перед созданием HEAD
		 *
		 * @throws Exception
		 * @since version
		 */
		public function BeforeCompileHead()
        {
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
            //			$doc->addScriptOptions('csrf.token'  , JSession::getFormToken()  ) ;


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
		 * После рендеринга страницы
		 *
		 * @throws Exception
		 * @since version
		 */
		public function AfterRender(){

            # Если Админ Панель
            if( $this->app->isClient( 'administrator' ) ) return true; #END IF
            # Если нет настроек компонента
            if ( !$this->paramsComponent ) return true; #END IF



            $HelpersAssets = \Plg\Pro_critical\Assets::instance( $this->paramsComponent );

            # Извлечение всех ресурсов JS && CSS Со траницы
            $HelpersAssets->getAllAccessList();

            # Установить найденные ресурсы в тело страницы
            $HelpersAssets->setAssetsToPage();

            # Перенос скриптов в низ тела страницы
            if( $this->paramsComponent->get('moving_scripts_to_bottom' , false) )
            {
                $Optimises = \GNZ11\Api\Optimize\Optimises::instance( $this->params ) ;
                $Optimises->setParams([
                    # Имя Оптимизатора
                    'my_name' => 'HtmlOptimizer' ,
                    # Переносить скрипты вниз страницы : Bool
                    'downScript' => true ,
                    'preload'=>[],
                    'not_load'=>[],
                    # обварачивать элементы в тег <template /> : Array
                    'to_templates'=>[],
                    'to_html_file'=>[],
                ]);
//                $Optimises->Start();

            }#END IF


//            $HelpersCss = Helpers\Assets\Css::instance();

//            $HelpersCss

            # Найти и извлечь все ссылки на CSS файлы и теги стили
//			$HelpersCss->getFileList();

			# Установить в HTML ссылки на Css файлы и стили
//			$HelpersCss->insertStylesIntoDocument();



            ### Сохранить тело страницы
            $HelpersAssets->saveBody();

			
			return true ;
		}
		
		/**
		 * Точка входа Ajax
		 *
		 * @since version
		 */
		public function onAjax(){






			# Проверить Token
			if(!JSession::checkToken('get')) exit;
			
			$dataModel = $this->app->input->get('model' , false , 'RAW' );
			
			if( !$dataModel )
			{
				echo new JResponseJson( false , JText::_('MODEL ERROR'), true);
				$this->app->close();
			}#END IF
			
			$inputTask = $this->app->input->get('task' , false , 'STRING' );
			
			$model = '\Plg\Pro_critical'.$dataModel ;
			$obj = new $model();
			
			$res = $obj->{$inputTask}();
			
			if(  !$res )
			{
				echo new JResponseJson( false , JText::sprintf('METHOD %s:%s ERROR' , $model , $inputTask ), true);
				$this->app->close();
			}#END IF
			echo new JResponseJson( $res );
			$this->app->close();
			
			
			
			
		}
		
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	