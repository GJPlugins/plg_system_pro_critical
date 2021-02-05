<?php

/***********************************************************************************************************************
 * ╔═══╗ ╔══╗ ╔═══╗ ╔════╗ ╔═══╗ ╔══╗  ╔╗╔╗╔╗ ╔═══╗ ╔══╗   ╔══╗  ╔═══╗ ╔╗╔╗ ╔═══╗ ╔╗   ╔══╗ ╔═══╗ ╔╗  ╔╗ ╔═══╗ ╔╗ ╔╗ ╔════╗
 * ║╔══╝ ║╔╗║ ║╔═╗║ ╚═╗╔═╝ ║╔══╝ ║╔═╝  ║║║║║║ ║╔══╝ ║╔╗║   ║╔╗╚╗ ║╔══╝ ║║║║ ║╔══╝ ║║   ║╔╗║ ║╔═╗║ ║║  ║║ ║╔══╝ ║╚═╝║ ╚═╗╔═╝
 * ║║╔═╗ ║╚╝║ ║╚═╝║   ║║   ║╚══╗ ║╚═╗  ║║║║║║ ║╚══╗ ║╚╝╚╗  ║║╚╗║ ║╚══╗ ║║║║ ║╚══╗ ║║   ║║║║ ║╚═╝║ ║╚╗╔╝║ ║╚══╗ ║╔╗ ║   ║║
 * ║║╚╗║ ║╔╗║ ║╔╗╔╝   ║║   ║╔══╝ ╚═╗║  ║║║║║║ ║╔══╝ ║╔═╗║  ║║─║║ ║╔══╝ ║╚╝║ ║╔══╝ ║║   ║║║║ ║╔══╝ ║╔╗╔╗║ ║╔══╝ ║║╚╗║   ║║
 * ║╚═╝║ ║║║║ ║║║║    ║║   ║╚══╗ ╔═╝║  ║╚╝╚╝║ ║╚══╗ ║╚═╝║  ║╚═╝║ ║╚══╗ ╚╗╔╝ ║╚══╗ ║╚═╗ ║╚╝║ ║║    ║║╚╝║║ ║╚══╗ ║║ ║║   ║║
 * ╚═══╝ ╚╝╚╝ ╚╝╚╝    ╚╝   ╚═══╝ ╚══╝  ╚═╝╚═╝ ╚═══╝ ╚═══╝  ╚═══╝ ╚═══╝  ╚╝  ╚═══╝ ╚══╝ ╚══╝ ╚╝    ╚╝  ╚╝ ╚═══╝ ╚╝ ╚╝   ╚╝
 *----------------------------------------------------------------------------------------------------------------------
 * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 24.08.2020 15:24
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 **********************************************************************************************************************/

namespace Plg\Pro_critical;
defined('_JEXEC') or die; // No direct access to this file

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Exception;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;

/**
 * Class Assets
 * @package Plg\Pro_critical
 * @since 3.9
 * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 24.08.2020 15:24
 *
 */
class Assets
{
    /**
     * @var array Коллекция JS CSS Ресурсов
     * @since 3.9
     */
    public static $AssetssCollection = [] ;
    /**
     * @var \GNZ11\Document\Dom объект с телом страницы
     * @since 3.9
     */
    public static $dom;
    /**
     * @var array[] массив хеш найденных ресурсов
     * @since 3.9
     */
    private static $HashArray = [
        'link' => [] ,
        'style' => [] ,
        'script' => [] ,
        'scriptDeclaration' => [] ,
        ];
    /**
     * @var array хранилище путей к файлам от корня сайта - для поиска файлов в справочнике
     * @since 3.9
     */
    private static $HashFileArray = [
        'link' => [] ,
        'script' => [] ,
        ];
    /**
     * @var CMSApplication|null
     * @since 3.9
     */
    protected $app;
    /**
     * @var \JDatabaseDriver|null
     * @since 3.9
     */
    protected  $db;
    /**
     * Array to hold the object instances
     *
     * @var Assets
     * @since  1.6
     */
    public static $instance;
    /**
     * @var Registry Параметры компонента
     * @since 3.9
     */
    protected static $params ;
    /**
     * @var array Хранение статистики об обработанных ресурсах
     * @since 3.9
     */
    public static $statistics = [
        'errors' => 0 ,
        'New_fiels' => [] ,
        'Load_fiels' => [] ,
        'minifyCount' => 0 ,
    ];

    /**
     * Assets constructor.
     * @param $params array|object
     * @throws Exception
     * @since 3.9
     */
    public function __construct($params)
    {
        # Устанавливаем параметраметры копонента
        self::$params = $params ;
        $this->app = Factory::getApplication();
        $this->db = Factory::getDbo();

        BaseDatabaseModel::addIncludePath( JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_' . Helper::$component . DIRECTORY_SEPARATOR . 'models' , Helper::$prefix );
        $this->CssFileListModel = BaseDatabaseModel::getInstance( 'Css_file_list' , Helper::$prefix );
        $this->cssStyleListModel = BaseDatabaseModel::getInstance( 'Css_style_list' , Helper::$prefix );

        # Модель справочника JS Файлов
        $this->JsFileListModel = BaseDatabaseModel::getInstance( 'js_file_list' , Helper::$prefix );
        $this->JsScriptListModel = BaseDatabaseModel::getInstance( 'js_style_list' , Helper::$prefix );

        # Установить лимит 0 для того чтобы выбрать все данные из справичников
        $this->app->input->set('limit', 0);

        \Plg\Pro_critical\Helpers\Assets\Css_critical::instance( self::$params );


    }
    /**
     * @param array $options
     *
     * @return Assets
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
    }#END FN

    /**
     * Загрузка тела страницы в DOMDocument
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 29.11.2020 00:02
     *
     */
    public  function InitDOM(){
        self::$dom = new \GNZ11\Document\Dom();
        $body = $this->app->getBody();
        self::$dom->loadHTML($body);
    }

    /**
     * Извлечение всех ресурсов JS && CSS Со траницы
     * @throws Exception
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 25.08.2020 19:37
     *
     */
    public function getAllAccessList(){

        # Если отключено в настройках компонента оработка JS и CSS
        if( !self::$params->get('JS_On' , false ) && !self::$params->get('CSS_On' , false ) ) return  ; #END IF

        $Date = new Date() ;
        $userId  = Factory::getUser()->id;

        try
        {
            $Links_assets = \Plg\Pro_critical\Helpers\Assets\Links_assets::instance();
        } catch (Exception $e)
        {
        }


        $xpathQuery = false ;
        if ( self::$params->get('JS_On' , false ) )
        {
            $xpathQuery[] = '//script[not(@data-not-interact)]' ;
        }#END IF
        if (self::$params->get('CSS_On' , false ) )
        {
            $xpathQuery[] = '//link[@rel="stylesheet" and not(@data-not-interact)]|//style[not(@data-not-interact)]'  ;
        }#END IF

//        self::$dom = new \GNZ11\Document\Dom();
//        $body = $this->app->getBody();
//        self::$dom->loadHTML($body);

        $xpath = new \DOMXPath(self::$dom);
        $Nodes = $xpath->query( implode('|' , $xpathQuery) );

        foreach ($Nodes as $node)
        {
            switch ($node->tagName){
                case 'link':
                    $type = 'text/css' ;
                    $attr = self::$dom::getAttrElement($node, []);

                    if ( !isset( $attr['href'] ) ) continue 2 ; #END IF

                    $hash =  md5( $attr['href'] ) ;


                    $hrefArr = explode('?', $attr['href']);

                    $href = $hrefArr[0];

                    # Разбор ссылки - поиск ошибок - исправление ссылки - определение локальная ссылка или нет
                    $log = $Links_assets->linkAnalysis($href);
                    $href = $log['file'];
                    $link = [];
                    $link['load'] = 1;
                    $link['hash'] = $hash ;
                    $link['minify'] = ( substr_count($attr['href']  , '.min.' ) ? true : false );
                    $link['minify_file'] = ( substr_count( $attr['href']  , '.min.' ) ? $href : false );
                    $link['err_path_log'] = null ;
                    $link['params_query'] = null ;
                    $link['type'] = $type ;
                    $link['created_by'] = $userId ;
                    $link['created'] = $Date->toSql()  ;
                    $link = array_merge($link, $log);

                    # Добавляем найденные арибуты

                    # Фильтрация элементов с определенными именами ключей
                    $attr   = \GNZ11\Document\Arrays::filterElemByKey($attr , ['type' , 'href'] ) ;
                    $link['attrs'] = (!empty( $attr ) ? json_encode( $attr )  : null ) ;

                    # Если есть GET параметры в ссылке
                    if( isset($hrefArr[1]) )
                    {
                        # Разобрать параметры ссылки
                        $link['params_query'] = $Links_assets->parseRequestParameters($hrefArr, $link, $href);
                    }#END IF

                    # Очистить ссылку : Оставляем только путь к файлу от корня сайта
                    $link['file'] = $Links_assets::cleanLocalLink( $link['file'] );
                    $link['minify_file'] = $Links_assets::cleanLocalLink( $link['minify_file'] );

                    # Записываем статистику
                    self::$statistics['errors'] += (count($log['err']));


                    # Записываем в справочник ресурсов
//                    self::$AssetssCollection['link'][$hash] = $link ;
                    # Записываем в справочник ресурсов
                    $key = $link['file'] ;
                    self::$AssetssCollection['link'][ $key ] = $link;


                    # Записываем hash link
                    self::$HashArray['link'][] = $hash ;
                    self::$HashFileArray['link'][] = $link['file'] ;

                    # Удаление найденого узла ##########################
                    $node->parentNode->removeChild($node);

                    break ;
                case 'style' :
                    $hash = md5($node->nodeValue);
                    # Записываем в справочник ресурсов
                    self::$AssetssCollection['style'][$hash] = [
                        'load' => 1,
                        'content' => $node->nodeValue,
                        'hash' => $hash,

                        'type' => 'text/css' ,
                        'media'=> 'all' ,

                        'created_by' => $userId ,
                        'created' => $Date->toSql()  ,
                    ];

                    # Записываем hash
                    self::$HashArray['style'][] = $hash ;

                    # Удаление найденого узла ##########################
                    $node->parentNode->removeChild($node);
                    break ;
                    # JS Скрипты
                default :
                    #Получить атрибуты
                    $excludeAttr=[];
                    $attr = self::$dom::getAttrElement( $node , $excludeAttr ) ;

                    # JS Файлы
                    if( isset($attr['src']) )
                    {
                        # Для ссылок на JS файлы

                        # Создаем hash от полной ссылкм в мести с медиа запросом
                        # так как вслучае если файл обновили то должен измениться и hash
                        $hash = md5( $attr['src'] );

                        # Отделить ссылку от GET запроса
                        $hrefArr = explode('?', $attr['src']);
                        # Разбор ссылки - поиск ошибок - исправление ссылки - определение локальная ссылка или нет
                        $log = $Links_assets->linkAnalysis($hrefArr[0]);

                        $href = $log['file'];

                        $link = [];
                        $link['load'] = 1;
                        $link['hash'] = $hash;
                        $link['type'] = 'text/javascript';
                        $link['async'] = isset($attr['async']);
                        $link['defer'] = isset($attr['defer']);
                        $link['minify'] = (substr_count($attr['src'], '.min.') ? true : false);
                        $link['minify_file'] = (substr_count($attr['src'], '.min.') ? $href : false);
                        $link['err_path_log'] = null ;
                        $link['params_query'] = null ;
                        $link['override'] = null ;
                        $link = array_merge($link, $log);

                        # Добавляем найденные арибуты кроме существенных которые перечислены а $excludeAttr
                        $excludeAttr = ['type' , 'src', 'async', 'defer'] ;

                        # Фильтрация элементов с определенными именами ключей
                        $attr   = \GNZ11\Document\Arrays::filterElemByKey($attr , $excludeAttr ) ;
                        $link['attrs'] = (!empty( $attr ) ? json_encode( $attr )  : null ) ;


                        $link = array_merge($link, $attr);

                        # Если есть параметры в ссылке то что находится после (?)
                        if( isset($hrefArr[1]) )
                        {
                            # Разобрать параметры ссылки
                            $link['params_query'] = $Links_assets->parseRequestParameters($hrefArr, $link, $href);
                        }#END IF

                        # Очистить ссылку : Оставляем только путь к файлу от корня сайта
                        $link['file'] = $Links_assets::cleanLocalLink( $link['file'] );
                        $link['minify_file'] = $Links_assets::cleanLocalLink( $link['minify_file'] );





                        # Записываем в справочник ресурсов
                        $key = $link['file'] ;
                        self::$AssetssCollection['script'][ $key ] = $link;

                        # Записываем статистику
                        self::$statistics['errors'] += ( count( $log['err'] ) );

                        # Записываем hash в хранилище - для поиска файлов в справочнике
                        self::$HashArray['script'][] = $hash ;
                        self::$HashFileArray['script'][] = $key ;
                    }
                    # для JS скриптов
                    else
                    {
                        $hash = md5($node->nodeValue);
                        $attr['type'] = (isset($attr['type']) ? $attr['type'] : 'text/javascript' );
                        $type = $attr['type'];
                        # Фильтрация элементов с определенными именами ключей
                        $attr   = \GNZ11\Document\Arrays::filterElemByKey($attr , ['type'] ) ;
                        # Добавляем найденные арибуты
                        self::$AssetssCollection['scriptDeclaration'][$hash] = [
                            'load' => 1,
                            'hash' => $hash ,
                            'content' => $node->nodeValue,
                            'attrs' =>  (!empty( $attr ) ? json_encode( $attr )  : null ) ,
                            'type' => $type,
                        ];
                        # Записываем hash
                        self::$HashArray['scriptDeclaration'][] = $hash ;
                    }#END IF

                    # Если переносить скрипты вниз страницы
                    if( self::$params->get('moving_scripts_to_bottom' , false ) )
                    {
                        # Удаление найденого узла ##########################
                        $node->parentNode->removeChild($node);
                    }#END IF
            }#END SWITCH
        }#END FOREACH

        # Получить данные из справочника для <link Css />
        $linkDbList = $this->getItemTableData('link') ;

        # Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
        $this->getNewAssets( 'link' , (array)$linkDbList);




        # Получить данные из справочника для <Style Css />
        $styleDbList = $this->getItemTableData('style') ;
        # Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
        $this->getNewAssets( 'style' , (array)$styleDbList);

        # Получить данные из справочника для <script />
        $scriptDbList = $this->getItemTableData('script') ;

        # Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
        $this->getNewAssets( 'script' , (array)$scriptDbList);

        # Получить данные из справочника для <script Declaration/>
        $scriptDeclarationDbList = $this->getItemTableData('scriptDeclaration') ;
        # Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
        $this->getNewAssets( 'scriptDeclaration' , (array)$scriptDeclarationDbList);
    }

    /**
     * Установить ссылки на JS Файлы   установка Css Стилей !
     * @throws Exception
     * @since  3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date   29.01.2021 18:41
     *
     */
    public function setAssetsToPage(){
        \Plg\Pro_critical\Helpers\Assets\Js::instance()->setScriptLink() ;
        \Plg\Pro_critical\Helpers\Assets\Css::instance()->setCss() ;
    }

    /**
     * Установка Последних JS скриптов перед возвращением BODY в документ
     * @since  3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date   29.01.2021 18:44
     *
     */
    public function setOverAssetsToPage(){
        \Plg\Pro_critical\Helpers\Assets\Js::instance()->setScriptTags() ;
    }

    /**
     * Получить Атребуты для ресура
     * @param $Object
     * @return array
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 26.08.2020 01:42
     *
     */
    protected function getAttr($Object){


        if(  empty( $Object->attrs ) ) return [] ; #END IF
         

        
        
        $Registry = new Registry( $Object->attrs );
        return $Registry->toArray() ;
    }

    /**
     * Выбрать подходящию сборку файла ( file | override | minify_file )
     * @param $Object object данные ресурса JS
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 25.08.2020 19:53
     *  Todo - Добавить состаяние отладки файла
     */
    protected function getFile( $Object ){
        $file =  $Object->file ;
        # Если включено переопределение
        $file = ( $Object->override && $Object->override_file ? $Object->override_file :  $file  );
        $file = ( $Object->minify   && $Object->minify_file ? $Object->minify_file :  $file  );
        $this->addParamsQuery( $file , $Object );
        return $file ;
    }

    /**
     * Добавить параметры GET Запроса к ссылке на файл
     * @param $file
     * @param $Object
     * @return string
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 25.08.2020 20:55
     *
     */
    protected function addParamsQuery( &$file , $Object){
        if( isset($Object->params_query) && $Object->params_query )
        {
            $i = null;
            $queryStr = null;
            $params_query = json_decode($Object->params_query);
            foreach ($params_query as $query)
            {
                if( isset($query->published) && !$query->published )
                    continue;
                $queryStr .= !$i ? '?' : '&';
                $queryStr .= $query->name . (!empty($query->value) ? '=' . $query->value : '');
                $i++;
            }#END FOREACH

            $file .= $queryStr ;

            return $file ;
        }
        return $file ;
    }



    /**
     * Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
     * @param string    $view   тип ресурса   link | script
     * @param array     $DbList массив данных из справичника этого типа
     * @throws Exception
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 24.08.2020 21:40
     */
    protected function getNewAssets(string $view, array $DbList){

        if (!isset( self::$AssetssCollection[$view] )) return ;  #END IF





        $Query = $this->db->getQuery(true) ;
        $tbl = self::getTbl($view) ;

        $excludeFields=[ 'err','protocol','absolute_path', 'href'  ];
        $firstElement = reset(self::$AssetssCollection[$view] );
        # Создать список полей из ключей первого элемента для добавления в корзину
        foreach( $firstElement as $key => $itemFile )
        {
            if(  in_array( $key , $excludeFields ) ) continue ; #END IF
            $columns[]= $key ;
        }#END FOREACH




        # Индикатор добавления в DB
        $addDB = false ;








        # Перебрать рессурсы
        foreach (self::$AssetssCollection[$view] as $hash => &$data ){





            # TODO - Добавить проверку по хешу - в случае изменения медиа версии после редактирования
            # Если этот ресурс уже был сохранен в справочнике и у него установлен параметр не загружать
            # то удаляем его из кандидатов на запись в справочник
            # Если данные в справочнике не найдены - подготовка добавления в DB
            if( key_exists($hash, $DbList) )
            {
                if( !$DbList[$hash]->load )
                {
                    unset( self::$AssetssCollection[$view][$hash] );
                    continue ;
                }#END IF

                self::$AssetssCollection[$view][$hash] = $DbList[$hash] ;

            }
            else
            {

                $excludeLinesArr = self::$params->get('exclude_dynamic_lines_'.$view , [] ) ;
                $Registry = new Registry($excludeLinesArr) ;
                $Arr = $Registry->toArray() ;
                $mapArr = array_map(
                    function($key, $value) {
                        return $value['text'];
                    },
                    array_keys($Arr),
                    $Arr
                );




                switch ($view){
                    case 'scriptDeclaration':
                        $testStr = $data['content'] ;
                        break ;
                    default: $testStr = false ;
                }

                # Проверяем на исключение записи в DB ( Component Config ) для scriptDeclaration
                if( $testStr )
                {
                    # Найти подстроку из массива в заданной строке
                    $r = \GNZ11\Document\Arrays::strpos_array( $testStr , $mapArr) ;
                    # Если подстарка исключения найдена пропускаем добавление в справочник
                    if(  $r ) {
                        #Переводим в объект новые найденные ресурсы
                        \GNZ11\Document\Arrays::arrToObj( $data ) ;
                        continue ;
                    } #END IF
                }#END IF


                $addDB = true ;
                # Фильтрация элементов с определенными именами ключей
                $valuesArr   = \GNZ11\Document\Arrays::filterElemByKey($data , $excludeFields ) ;

                $obsoleteQuoted = array_map( array( $this->db , 'quote'),  $valuesArr );
                $Query->values( implode( "," , $obsoleteQuoted ) . PHP_EOL );
            }#END IF

            #Переводим в обьект новые найденные ресурсы
            \GNZ11\Document\Arrays::arrToObj( $data ) ;

        }#END FOREACH




        # Если ни чего не найдено для записи
        if( !$addDB ) return ; #END IF

        # если JS Script и в настройках не сохранять в DB
        if( $view == 'scriptDeclaration' && !self::$params->get('save_books_js_script' , 1 )  ) return;  #END IF












        $Query->insert( $this->db->quoteName( $tbl ) )->columns( $this->db->quoteName( $columns ) );

        if ($view == 'link') {
//            echo'<pre>';print_r( $Query->dump() );echo'</pre>'.__FILE__.' '.__LINE__;
//            echo'<pre>';print_r( $tbl );echo'</pre>'.__FILE__.' '.__LINE__;
//            echo'<pre>';print_r( $addDB );echo'</pre>'.__FILE__.' '.__LINE__;
//            echo'<pre>';print_r( $view );echo'</pre>'.__FILE__.' '.__LINE__;
//            die(__FILE__ .' '. __LINE__ );
        }#END IF

        $this->db->setQuery( $Query );
        try
        {
            // Code that may throw an Exception or Error.
            $this->db->execute();
        }
        catch( Exception $e )
        {
            // Executed only in PHP 5, will not be reached in PHP 7
            echo 'Выброшено исключение: ' , $e->getMessage() , "\n";
            echo'<pre>';print_r(  $e );echo'</pre>'.__FILE__.' '.__LINE__;
            die(__FILE__ .' '. __LINE__ );
        }

    }

    /**
     * Получить название таблицы по Псевдониму вида
     * @param $view string Псевдоним вида
     * @return string
     * @throws Exception
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 24.08.2020 21:44
     *
     */
    protected static function getTbl($view){
        switch ( $view ){
            case 'link':
                $tbl = '#__pro_critical_css_file' ;
                break ;
            case 'style':
                $tbl = '#__pro_critical_css_style' ;
                break ;
            case 'script':
                $tbl = '#__pro_critical_js_file' ;
                break ;
            case 'scriptDeclaration':
                $tbl = '#__pro_critical_js_style' ;
                break ;
            default:
                throw new Exception('Не известный тип рессурса ' . $view ) ;
        }
        return $tbl;
    }

    /**
     * Получить настройкм из DB для отбранных рессурсов
     * @param $typeAsset - Тип ресурса
     * @return void|Object - Ресурсов с настройками
     * @throws Exception
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 24.08.2020 21:49
     *
     */
    protected function getItemTableData($typeAsset){
        $res = new \stdClass();
        if ( empty(self::$HashArray[$typeAsset] ) ) return  $res ; #END IF



        $tbl = self::getTbl($typeAsset) ;

        $Query = $this->db->getQuery(true);
        $Query->select('*')
            ->from( $this->db->quoteName($tbl) )
            ->where( $this->db->quoteName('published') . '= 1 ' );


        if ( !empty(self::$HashFileArray[$typeAsset] ) ) {
            # Поиск через Путь к файлу  -
            $key = 'file';
            # применяем метод quote к каждому елементу массива
            $obsoleteIDsQuoted = array_map( array( $this->db , 'quote' ), self::$HashFileArray[$typeAsset] );
        }else{
            # Поиск по хешу -
            $key = 'hash';
            # применяем метод quote к каждому елементу массива
            $obsoleteIDsQuoted = array_map( array( $this->db , 'quote' ), self::$HashArray[$typeAsset]);
        }#END IF

        $Query->where( $this->db->quoteName($key) . 'IN ('.implode(',' , $obsoleteIDsQuoted  ).')' );





        $this->db->setQuery($Query);
        $res = $this->db->loadObjectList($key) ;

        if ($typeAsset == 'link') {
//            echo'<pre>';print_r( $res );echo'</pre>'.__FILE__.' '.__LINE__;
//            die(__FILE__ .' '. __LINE__ );

        }#END IF


        return $res ;
    }

    public function saveBody(){
        if (!self::$dom ) return ;  #END IF

        $scriptTask = [
            'html' => \Plg\Pro_critical\Html::$addJsTask ,
            'Css' => \Plg\Pro_critical\Helpers\Assets\Css::$addJsTask ,
        ] ;




        $attr = [
            'type'=> 'application/json' ,
            'id'=> 'pro_critical-script-Task' ,
        ];
        $content =  json_encode( $scriptTask ) ;
        self::$dom::writeDownTag ( self::$dom , 'script' , $content , $attr );


        $body = self::$dom->saveHTML(); 
        $this->app->setBody($body);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}