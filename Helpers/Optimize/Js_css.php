<?php
	namespace Plg\Pro_critical\Optimize;
	use JFactory;
	use Exception;
    use Joomla\CMS\Application\CMSApplication;
    use Throwable;
	
	use JURI;
	use Joomla\CMS\Filesystem\File as JFile;
	// use JModelLegacy;
	use \Joomla\CMS\MVC\Model\BaseDatabaseModel as JModelLegacy ;

    /**
     * Класс Отимизации CSS && JAVASCRIPT
     * @package     Plg\Pro_critical\Optimize
     *
     * @since 3.9
     */
	class Js_css
	{
        /**
         * @var CMSApplication|null
         * @since version
         */
		private $app  ;
		private $Errors ;
        /**
         * @var string Адрес шлюза для CSS оптимизации
         * @since version
         */
		private static $cssUrl = 'https://cssminifier.com/raw' ;
        /**
         * @var string Адрес шлюза для JAVASCRIPT оптимизации
         * @since version
         */
		private static $javascriptUrl = 'https://javascript-minifier.com/raw' ;
		public $file ;
		public $newFile ;
		/**
		 * Имя компонента для вызова модели
		 * @var string
		 * @since 3.9
		 */
		private static $component = 'pro_critical';
        /**
         * @var string Префикс для модели
         * @since version
         */
		private static $prefix = 'pro_critical' . 'Model';
		
		/**
		 * Js_css constructor.
		 * @since 3.9
		 */
		public function __construct ()
		{
			$this->app = JFactory::getApplication() ;
			$this->Errors = false ;
			JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_' . self::$component . DS . 'models', self::$prefix );
		}
		
		/**
		 * Изменить расшерение файла - добавить ".min"
		 * @param           $filename
		 * @param   string  $new_extension
		 *
		 * @see http://qaru.site/questions/315103/how-can-i-change-a-files-extension-using-php/1522619#1522619
		 * @return string
		 *
		 * @since version
		 */
		function replace_extension( $filename , $new_extension = '') {
			$info = pathinfo($filename);
			$new_extension =  'min.'.$info['extension'];
			return $info['dirname'].DIRECTORY_SEPARATOR.$info['filename'] . '.' . $new_extension;
		}
		
		/**
		 * Вход для минимизации файлов
		 * @return array
		 *
		 * @throws Throwable
		 * @since version
		 */
		public function minify(){
			
			$form = $this->app->input->get('data' , false , 'RAW');
			
			if( !$form  )
			{
				$arr = $this->app->input->get('data' , [] , 'ARRAY');
				echo'<pre>';print_r( $arr );echo'</pre>'.__FILE__.' '.__LINE__;
				die(__FILE__ .' '. __LINE__ );
			}#END IF
			parse_str( $form, $output );
			
			# Если передается не форма а строка с ссылкой на файл
			if( !isset($output['jform']) )
			{
				$file = $form ;
				$Ext = JFile::getExt( $form );
				if ($Ext == 'css'){
					$urlApi =  self::$cssUrl ;
				}else if($Ext == 'js'){
					$urlApi = self::$javascriptUrl ;
				}else{
					$mes = 'Не удалось определить тип обрабатываемых данных.' ;
					throw new Exception( $mes , 500 );
				}#END IF
				
				#Подготовить имена файлов и отправить на сжатие.
				$data = $this->getDataProcess( $file , $urlApi   );
				
				$mes = 'Сжатие файла выполнено!';
				$this->app->enqueueMessage( $mes  );
				
				return $data;
				
			}#END IF
			
			$model =  ( explode('.' , $output['task'] ) )[0] ;
			$jform = $output['jform'] ;


            $urlApi = false ;
            switch($model){
                case 'css_file':
                    $urlApi =  self::$cssUrl ;

                    break ;
                case 'js_style':
                case 'js_file':
                    $urlApi = self::$javascriptUrl ;

                    break ;
                default :
                    $mes = 'Не удалось определить тип обрабатываемых данных.' ;
                    throw new Exception( $mes , 500 );
            }

			if( $model == 'css_file' || $model == 'js_file'     )
			{
                $file = $jform['file'];
                if( $jform['override'] && !empty($jform['override_file']) )
                {
                    $file = $jform['override_file'];
                }#END IF
                #Подготовить имена файлов и отправить на сжатие.
                $data = $this->getDataProcess($file, $urlApi);
                $jform['minify_file'] = $this->newFile ;

			}else if($model == 'js_style'){

                $GNZ11_Js_css = new \GNZ11\Api\Optimize\Js_css();
                $data         = $GNZ11_Js_css->Minified( $urlApi , $jform['content'] );
                $jform['content_min'] = $data['content'] ;
                $jform['modified'] = 1 ;

//                echo'<pre>';print_r( $jform );echo'</pre>'.__FILE__.' '.__LINE__;
//                echo'<pre>';print_r( $data );echo'</pre>'.__FILE__.' '.__LINE__;
//                die(__FILE__ .' '. __LINE__ );


			}#END IF

            $Model = JModelLegacy::getInstance( $model , self::$prefix );


			
			

			
			

			if( !$Model->save($jform) )
			{
				$mes = 'Сохранение параметров не удалось!';
				$this->app->enqueueMessage( $mes , 'warning' );
				return $data;
			}#END IF
			
			$mes = 'Параметры сохранены!';
			$this->app->enqueueMessage( $mes  );
			return $data;
			#TODO Создать обработчик ошибок !!!
			
		}

        /**
         * Ajax Вход для Удаления сжатых файлов
         * @return bool|null
         * @throws Exception
         * @since 3.9
         * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
         * @date 25.08.2020 02:10
         *
         */
		public function remove_minify(){
			# Только для администратора
			if( !$this->app->isClient( 'administrator' ) ) return null ;
            parse_str( $this->app->input->get('data' , false , 'RAW') , $output );

            $model =  ( explode('.' , $output['task'] ) )[0] ;
            $_Model = JModelLegacy::getInstance( $model , self::$prefix );

            $jform = $output['jform'] ;






			if(  in_array( $model , ['css_file' , 'js_file'] ) )
			{
				$path = $jform['minify_file'];
				$jform['minify_file'] = false ;
			    if( !$_Model->save( $jform ) )
				{
					$this->app->enqueueMessage('Не удалось обновать параметры этих данных' , 'warning');
				}else{
					$this->app->enqueueMessage('Параметры сохранены!' );
				}#END IF
                if( empty($path) )
                {
                    $mes = 'ERROR : Имя файла не передано!';
                    $this->app->enqueueMessage( $mes , 'warning' );
                    return true ;
                }#END IF

                if( $this->removeMinFile($path) )
                {
                    return true ;
                }#END IF

			}else if (in_array( $model , ['js_style' ,  ] ) ){
                $jform['minify'] = false ;
                $jform['content_min'] = false ;
                if( !$_Model->save( $jform ) )
                {
                    $this->app->enqueueMessage('Не удалось обновать параметры этих данных' , 'warning');
                }else{
                    $this->app->enqueueMessage('Параметры сохранены!' );
                }#END IF
                return true ;
            }#END IF



			return false ;
			
		}
		
		/**
		 * Удаление файла по переданной ссылке
		 * Если ссылка не передана ищем ссылку в paramsQuery $_POST || $_GET
		 *
		 * @param $path - string  url ссылка на файл Абсолютная или относительная
		 *
		 * @return bool в случае учаеспеха возвращает TRUE
		 *
		 * @throws Exception в случае если удаление не удалось
		 *
		 * @since 3.9
		 */
		public function removeMinFile ( $path = false ){
			
			# Только для администратора
			if( !$this->app->isClient( 'administrator' ) ) return null ;
			
			if( !$path )
			{
				$path = $this->app->input->get('data' ,  false , 'PATH' ) ;
			}#END IF
			
			# Убрать домен - Если ссылка абсолютная
			$path = JPATH_ROOT . str_replace( JURI::root() , '/' , $path );
			
			if( !JFile::exists($path) )
			{
				$mes = 'Сжатая версия файла не не найдена.' . '<br><hr>';
				$mes .= '<p title="'.$path.'" style="overflow-wrap:break-word;">'.$path .'</p>';
				throw new Exception( $mes , 500 );
			}#END IF
			
			if( !JFile::delete($path) )
			{
				$mes = 'Удаление не удалось.' . '';
				throw new Exception( $mes , 500 );
			}#END IF
			
			$mes = 'Cжатая версия файла удалена!' . '<br>';
			$this->app->enqueueMessage( $mes );
			
			return true ;
		}

		/**
		 * @param $arr
		 * @param $url
		 *
		 * @return array
		 * @throws Exception
		 *
		 * @since     3.8
		 * @copyright 23.12.18
		 * @author    Gartes
		 *
		 */
		public function Procrss_minify($arr, $url)
		{
			
			foreach( $arr as $originalFilePath => $minFilePath )
			{
				$handler = @fopen( $minFilePath , 'w' );
				if( !$handler )
				{
					fclose( $handler );
					$mes = 'Не возможно открыть файл для записи.' . '<br>';
					$mes .= 'Проверьте права доступа к файлу и директории.';
					throw new Exception( $mes , 500 );
				}#END IF
				
				if( !JFile::exists($originalFilePath) )
				{
					fclose( $handler );
					$mes = 'Не возможно открыть оригинальный файл с данными для чтения.' . '<br>';
					$mes .= 'Проверьте правильность указания путей(ссылок) на файл.';
					throw new Exception( $mes , 500 );
				}#END IF
				
				$contents = file_get_contents( $originalFilePath );
				
				try
				{
					$GNZ11_Js_css = new \GNZ11\Api\Optimize\Js_css();
					$data         = $GNZ11_Js_css->Minified( $url , $contents );
				}
				catch( Exception $e )
				{
					fclose( $handler );
					throw new Exception( $e->getMessage() , 500 );
					// Executed only in PHP 5, will not be reached in PHP 7
				}
				
				$data[ 'files' ] = [
					'file' => $this->file ,
					'minify_file' => $this->newFile ,
					];
				
				fwrite( $handler , $data[ 'content' ] );
				fclose( $handler );
				
				$mes = 'Файл создан!' . '<br>';
				$mes .= 'Размер не сжатого файла: ' . $data[ 'sizes' ][ 'in' ] . ' кБ.<br>';
				$mes .= 'Размер сжатого файла: ' . $data[ 'sizes' ][ 'out' ] . ' кБ.<br>';
				$mes .= 'Процент сжатия: ' . $data[ 'sizes' ][ 'zip_percent' ] . '%';
				$this->app->enqueueMessage( $mes );
				return $data;
			}
			return [];
		}#END FN
		
		/**
		 * Подготовить имена файлов и отправить на сжатие.
		 * @param $file
		 * @param $urlApi
		 *
		 * @return array
		 *
		 * @throws Exception
		 * @since version
		 */
        protected function getDataProcess($file, $urlApi)
        {
            $newArr = [];
            # Изменить расшерение файла - добавить ".min"
            $newfile = $this->replace_extension($file);

            $this->file = str_replace(JURI::root(), '/', $file);
            $this->newFile = str_replace(JURI::root(), '/', $newfile);

            $newArr[JPATH_ROOT . $this->file] = JPATH_ROOT . $this->newFile;
            return $this->Procrss_minify($newArr, $urlApi);

        }
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	