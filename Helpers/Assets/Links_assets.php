<?php
	/**
	 * @package     Plg\Pro_critical\Helpers\Assets
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace Plg\Pro_critical\Helpers\Assets;
	
	use Exception;
    use Joomla\CMS\Uri\Uri;
	
	
	/**
	 * Обработка ссылок на ресурсы
	 * @since       version
	 * @package     Plg\Pro_critical\Helpers\Assets
	 *
	 */
	class Links_assets
	{
		
		public static $instance;
		/**
		 * Медиа-версия для файлов по умолчанию
		 * @since 3.9
		 * @var string
		 */
		private $MediaVersion ;
		
		/**
		 * Links_assets constructor.
		 * @throws Exception
		 * @since 3.9
		 */
		private function __construct ( $options = [] )
		{
			$doc = \Joomla\CMS\Factory::getDocument();
			$this->MediaVersion = $doc->getMediaVersion() ; 
			return $this;
		}#END FN
		
		/**
		 * @param   array  $options
		 *
		 * @return Links_assets
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
		
		protected function setPreload($Link){
		
		}
		
		
		/**
		 * Подготовить ссылку к загрузи
		 * @param $Link
		 *
		 * @return mixed
		 *
		 * @since version
		 */
		public function prepareLinkData ( $Link )
        {
            $Link->href = $Link->file;
            # Переопределение
            if( isset($Link->override) && !empty($Link->override_file) && $Link->override )
                $Link->href = $Link->override_file; #END IF

            # режим разработки отключен
            if( !$Link->file_debug )
            {
                # Мин версия
                if( isset($Link->minify) && $Link->minify && !empty($Link->minify_file) )
                    $Link->href = $Link->minify_file; #END IF
            }#END IF

            # Если с прелоадером
            if( $Link->preload )
                $this->setPreload($Link);

            # id Revision
            if( isset($Link->ver_type) && $Link->ver_type && !empty($Link->revision_id) )
            {
                $Link->href .= '?i=' . $Link->revision_id;
            }
            else
            {
                $Link->href .= '?i=' . $this->MediaVersion;
            }#END IF

            # TODO ????
            if( isset($Link->params_query) && $Link->params_query )
            {
                $i = null;
                $queryStr = null;
                $params_query = json_decode($Link->params_query);
                foreach ($params_query as $query)
                {
                    if( isset($query->published) && !$query->published )
                        continue;
                    $queryStr .= !$i ? '' : '&';
                    $queryStr .= $query->name . (!empty($query->value) ? '=' . $query->value : '');
                    $i++;
                }#END FOREACH

                $Link->href .= (!empty($queryStr) ? '&' . $queryStr : null);

            }

            $dataLink = $Link;
            return $dataLink;
        }

        /**
         * Проверка хоста ссылки локалный или внешний.
         * Работает точнее для Joomla чем метод \GNZ11\Core\Filesystem\File::isInternal()
         * @param string $href ссылка
         * @return bool - если локальный TRUE иначе FALSE
         * @since 3.9
         */
		protected function checkLocalHost ( string $href )
		{
            $protocol = parse_url( $href );
            if(  !isset($protocol[ 'host' ]) )
            {
                return true ;
            }#END IF
            # для ссылок вида //joomla-upd.ga/test_css/test_home.css
			# если домен действительно содержит точку
			# и он не root домен сайта (не имеет вхождений в Uri::root() )
			if( stristr( $protocol[ 'host' ] , '.' ) && !stristr( Uri::root() , $protocol[ 'host' ] ) )
			{
				return false ;
			}
			return true ;
		}

		public static function cleanLocalLink($link){
            # убираем домен
            # Оставляем только путь к файлу от корня сайта так как - сайт может находится в директории домена
            # и путь к файлу без ведущего слеша
            $link = str_replace( \Joomla\CMS\Uri\Uri::root() , '' , $link )  ;
            $link = str_replace( \Joomla\CMS\Uri\Uri::root(true) , '' , $link )  ;
            # Убрать слеши по краям
            $link  =  trim($link, '/');
            return $link ;
        }

		/**
		 * Разбор ссылки - поиск ошибок - исправление ссылки - определение локальная ссылка или нет
		 * @param $href
		 *
		 * @return array - []
		 *
		 * @since 3.9
		 */
		public function linkAnalysis ( $href   )
		{
			
			$config = \Joomla\CMS\Factory::getConfig();
			$force_ssl = $config->get('force_ssl');
			$log = [
				'file' => null ,
				'no_external' => false ,
				'err' => [] ,
				'protocol' => [] ,
				
				'absolute_path' => false ,
				'err_href' => null ,
				'is_error' => false ,
			];
			
			$copyOrigHref = $href ;
			
			$href = trim( $href );
			
			if( preg_match( '/\s/' , $href ) )
			{
				$log[ 'err' ][] = 'В ссылке присутствую пробелы это може привести к ошибкам';
			}#END IF
			
            # проверка на русские буквы
			if( preg_match( "/[а-яё]+/iu" , $href ) )
			{
				$log[ 'err' ][] = 'В ссылке присутствую русские буквы.';
			}

			# Проверка хоста ссылки локалный или внешний.
			$isLocalHost          = $this->checkLocalHost( $href );


            $protocol             = parse_url( $href );

			# Проверка протокола
			if( !isset( $protocol[ 'scheme' ] ) )
			{
				if( !$isLocalHost )
				{
					$log[ 'err' ][] = 'Отсутствует протокол (scheme) в адресе';
				}#END IF
			}
			else if( stristr( $protocol[ 'scheme' ] , 'http' ) )
			{
				# Если не ssl и внешний
				if( $protocol[ 'scheme' ] == 'http' )
				{
					if( !$isLocalHost )
					{
						# Если на сайте включено SSL
						if( $force_ssl == 2 )
						{
							$log[ 'err' ][] = 'Протокол ссылки без SSL! но этот сайт с SSL';
						}#END IF
						
					}
					else
					{
						# Если на сайте включено SSL
						if( $force_ssl == 2 )
						{
							$log[ 'err' ][] = 'Протокол ссылки без SSL! Протокол сайта с SSL. Это приведет к ошибкам при загрузки данного ресурса.';
						}#END IF
					}#END IF
				}
			}
			else
			{
				$log[ 'err' ][] = 'Тип протокола не определен';
			}#END IF
			
			# Проверить домен
			if( $isLocalHost  && isset( $protocol[ 'host' ] )   )
			{
				$protocolSite             = parse_url( Uri::root() );
				if( $protocolSite['host'] == $protocol['host'] )
				{
					$log[ 'err' ][] = 'Для локальной ссылки указан абсолютный путь';
					$log['absolute_path'] = true ;
				}#END IF
			}#END IF
		
			# Проверка path
			if( stristr( $protocol[ 'path' ] , '//' ) )
			{
				$log[ 'err' ][] = 'Путь содержит два слеша после домена';
				$copyPath       = preg_replace( '/^\/\//' , '/' , $protocol[ 'path' ] );
				$href           = str_replace( '/' . $copyPath , $copyPath , $href );
				
			}
			else
			{
				# если host - не содержит точку
				if( isset( $protocol[ 'host' ] ) &&  !stristr( $protocol[ 'host' ] , '.' ) && $isLocalHost && preg_match( '/^\/\//' , $href ) )
				{
					$log[ 'err' ][] = 'Ошибка в адресе локального файла. Два слеша в начале относительного пути';
					$href           = '/' . $protocol[ 'host' ] . $protocol[ 'path' ];
				}#END IF
			}

			$log [ 'file' ]     = $href;
			$log [ 'no_external' ] = $isLocalHost;
			$log [ 'protocol' ] = $protocol;

			if( count( $log[ 'err' ] ) )
			{
				$log [ 'err_href' ] = $copyOrigHref;
				$log [ 'is_error' ] = true;
				$log [ 'err_path_log' ] = implode("\n" , $log[ 'err' ] );
			}#END IF

			return $log ;
		}

        /**
         * Разобрать параметры запроса
         *
         * @param   array  $hrefArr
         * @param   array  $link
         * @param          $href
         *
         * @return string
         *
         * @since version
         */
        public function parseRequestParameters (array $hrefArr , array $link , $href )
        {
            $paramHrefArr = explode( '&' , $hrefArr[ 1 ] );
            $i            = 0;
            foreach( $paramHrefArr as $item )
            {
                $paramArr                                                          = explode( '=' , $item );
                $nam                                                               = $paramArr[ 0 ];
                $val                                                               = (isset( $paramArr[ 1 ] ) ? $paramArr[ 1 ] : null );
                $link[ $href ][ 'params_query' ][ 'params_query' . $i ][ 'name' ]  = $nam;
                $link[ $href ][ 'params_query' ][ 'params_query' . $i ][ 'value' ] = $val;
                $i++;
            }#END FOREACH
            return json_encode( $link[ $href ][ 'params_query' ] );
        }
		
		
	}
	
	
	
	
	
	
	
	