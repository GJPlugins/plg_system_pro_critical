<?php
	/**
	 * @package     Css_file
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace Plg\Pro_critical\Helpers\Assets;
	
	
	use GNZ11\Document\Dom;
    use JFactory;
	use JLoader;
	use JModelLegacy;
	use Exception;
	use JDate;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Uri\Uri;
    use Joomla\Registry\Registry;
    use Throwable;
	
	/**
	 * @since       version
	 * @package     Plg\Pro_critical\HelpersCss
	 *
	 */
	class Css extends \Plg\Pro_critical\Assets
	{

		public static $instance;

        /**
         * @var array Хранение JS данных о задачах для Front
         * @since 3.9
         */
        public static $addJsTask = [] ;
		
		public $BASE_LINK;
		
		
		/**
		 * Имя компонента для вызова модели
		 * @since 3.9
		 * @var string
		 */
		private static $component = 'pro_critical';
		private static $prefix = 'pro_critical' . 'Model';
		
		
		private $Css_file_list;
        /**
         * @var array Новые теги STYLE которые найдены во время загрузки страницы и которых нет в справочнике
         * @since version
         */
		protected $newStyleTag;
        /**
         * @var array
         * @since 3.9
         */
        private $excludedTypes = [] ;


        /**
		 * helper constructor.
		 * @throws Exception
		 * @since 3.9
		 */
		public function __construct ($options = [] )
		{
            self::$dom = parent::$dom ;
/*
            JLoader::register( 'Pro_criticalHelper' , JPATH_ADMINISTRATOR . '/components/com_pro_critical/helpers/pro_critical.php' );

            JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_' . self::$component . DS . 'models' , self::$prefix );
			$this->Css_file_list = JModelLegacy::getInstance( 'Css_file_list' , self::$prefix );
            $this->cssStileListModel = JModelLegacy::getInstance( 'Css_style_list' , self::$prefix );

			# Установить поля в статистику
			$this->statistics = [ 'New_fiels' => [] , 'Load_fiels' => [] , 'minifyCount' => 0 ];*/
			
			return $this;
		}#END FN
		
		/**
		 * @param   array  $options
		 *
		 * @return Css
		 * @throws Exception
		 * @since 3.9
		 */
		public static function instance ( $options = [] )
		{
			if( self::$instance === null )
			{
				self::$instance = new self( $options );
			}
			
			return self::$instance;
		}#END FN

        /**
         * Установить отобранные CSS в экземпляр DOM
         * @since 3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date 26.08.2020 08:29
         *
         */
        public function setCss(){

            # Если коллекция css ссылок пустая
            if (!isset( self::$AssetssCollection['link'] )) return ; #END IF

            # Проверить Если не созданы CCSS
            $checkCCSS = \Plg\Pro_critical\Helpers\Assets\Css_critical::checkCriticalCssData() ;

            # Если созданы CCSS - Добавляем <style>CCSS</style> перед тегом </head>
            if ( $checkCCSS )
            {
                $DataCCSS = \Plg\Pro_critical\Helpers\Assets\Css_critical::$CriticalCssData ;
                $DataCCSS->critical_css_code .= $DataCCSS->add_css_code ;

                $attr = null ;
                self::$dom::_setBottomHeadTag ( self::$dom , 'style' , $DataCCSS->critical_css_code , $attr );
            }#END IF




            $loadLaterCss = [];
            # Справочник view=css_file
            foreach ( self::$AssetssCollection['link'] as $item)
            {
                # Если в настройках ресурса установлено не загружать
                if ( !$item->load ) continue ; #END IF

                # Если созданы CCSS и не Загружать с Critical Css
                if ( $checkCCSS && !$item->load_if_criticalis_set  ) continue; #END IF

                $attr = $this->getAttr( $item ) ;
                $attr['rel']  ="stylesheet";

                $file = $this->getFile( $item );

                # для создания ссылки к файлу убираем домен
                $file = str_replace( Uri::root() , '' , $file)  ;
                $file = str_replace( Uri::root(true) , '' , $file )  ;
                # Если путь начатается с '/'
                if (strpos( $file , '/') === 0) {
                    $file = ltrim( $file, '/' ) ;
                }

                $attr['href'] = Uri::root().$file ;


                # Если CCSS не созданы - добавляем  как есть
                if (!$checkCCSS)
                {
                    self::$dom::_setBottomHeadTag( self::$dom , 'link',  '',  $attr   );
                    continue ;
                }#END IF

                
                unset( $attr['rel'] ) ;
                $loadLaterCss[] = $attr ;
            }#END FOREACH

            # Справочник view=css_style
            $loadLaterCssStyle = [] ;
            foreach (self::$AssetssCollection['style'] as $item)
            {
                $attr = $this->getAttr( $item ) ;

                # Если TYPE ресурса не из исключенных добавляем его к атрибутам
                !in_array( $item->type,$this->excludedTypes )?$attr['type']=$item->type:null;

                if (!is_array( $item ))
                {
                    $Registry = new Registry($item);
                    $item = $Registry->toArray();
                }#END IF
                // Если нет содержимого тега <style />
                if ( empty( trim( $item['content'] ) )) continue ; #END IF

                // Если CCSS не созданы
                if ( !$checkCCSS )
                {
                    self::$dom::_setBottomHeadTag ( self::$dom , 'style' , $item['content'] , $attr );
                    continue ;
                }#END IF


                $loadLaterCssStyle[] = [ 'content' => $item['content'] , 'attr' => $attr ]  ;
            }#END FOREACH

            
            # Если в настройках компонента установлено создавать прелоадер
            if ( self::$params->get('ccss_add_preloader_ccs_link' , true ) )
            {
                $RevLoadLaterCss = array_reverse( $loadLaterCss );
                foreach ( $RevLoadLaterCss as $attr)
                {
                    # Создать предварительную загрузку ключевых запросов
                    $attr['rel'] = 'preload' ;
                    $attr['as'] = 'style' ;
//                    self::$dom::_setTopHeadTag( self::$dom , 'link',  '',  $attr   );
                    self::$dom::_setBottomHeadTag( self::$dom , 'link',  '',  $attr   );
                }#END FOREACH

            }#END IF





            if( count( $loadLaterCss  ) )
            {
                # Загрузить файл для запуска триггера __loadLaterCss
                \GNZ11\Core\Js::addJproLoad( Uri::root().'plugins/system/pro_critical/assets/js/__loadLaterCss.js' , false , false);
                foreach ( $loadLaterCss as $linkCss)
                {
                    # Добавить файл в загрузку JPro
                    \GNZ11\Core\Js::addJproLoad( $linkCss['href'] , /*'Callback'*/ false , $Trigger = '__loadLaterCss' );
                }#END FOREACH
            }#END IF

            # TODO - Дозагрузка файлов стилей при созданных CCSS - перенесено в JPro
//            self::$addJsTask['loadLaterCss']['link'] = $loadLaterCss ;

            # Данные для дозагрузки стилей css
            # обрабатывается /plg_system_pro_critical/assets/js/proCriticalCore.js :: loadLaterCss()
            self::$addJsTask['loadLaterCss']['stile'] = $loadLaterCssStyle ;


        }

		

		

		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	