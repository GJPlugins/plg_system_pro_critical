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
    public static $AssetssColection = [] ;
    /**
     * @var \GNZ11\Document\Dom объект с телом страницы
     * @since 3.9
     */
    protected static $dom;
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
     * @var CMSApplication|null
     * @since 3.9
     */
    private $app;
    /**
     * @var \JDatabaseDriver|null
     * @since 3.9
     */
    private $db;
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

        BaseDatabaseModel::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_' . Helper::$component . DS . 'models' , Helper::$prefix );
        $this->CssFileListModel = BaseDatabaseModel::getInstance( 'Css_file_list' , Helper::$prefix );
        $this->cssStyleListModel = BaseDatabaseModel::getInstance( 'Css_style_list' , Helper::$prefix );

        # Модель справочника JS Файлов
        $this->JsFileListModel = BaseDatabaseModel::getInstance( 'js_file_list' , Helper::$prefix );
        $this->JsScriptListModel = BaseDatabaseModel::getInstance( 'js_style_list' , Helper::$prefix );

        # Установить лимит 0 для того чтобы выбрать все данные из справичников
        $this->app->input->set('limit', 0);

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

        $xpathQuery = [] ;
        $xpathQuery[] = ( self::$params->get('JS_On' , false ) ? '//script' : null ) ;
        $xpathQuery[] = ( self::$params->get('CSS_On' , false ) ? '//link[@rel="stylesheet"]|//style' : null ) ;



        self::$dom = new \GNZ11\Document\Dom();
        $body = $this->app->getBody();
        self::$dom->loadHTML($body);
        $xpath = new \DOMXPath(self::$dom);
        $Nodes = $xpath->query( implode('|' , $xpathQuery) );



        foreach ($Nodes as $node)
        {
            switch ($node->tagName){
                case 'link':
                    $type = 'text/css' ;
                    $attr = self::$dom::getAttrElement($node, ['rel']);
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

                    # Записываем статистику
                    self::$statistics['errors'] += (count($log['err']));

                    # Записываем в справочник ресурсов
                    self::$AssetssColection['link'][$hash] = $link ;

                    # Записываем hash
                    self::$HashArray['link'][] = $hash ;

                    # Удаление найденого узла ##########################
                    $node->parentNode->removeChild($node);

                    break ;
                case 'style' :
                    $hash = md5($node->nodeValue);
                    # Записываем в справочник ресурсов
                    self::$AssetssColection['style'][$hash] = [
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

                    # SJ Скрипты
                default :
                    #Получить атрибуты
                    $excludeAttr=[];
                    $attr = self::$dom::getAttrElement( $node , $excludeAttr ) ;

                    # JS Файлы
                    if( isset($attr['src']) )
                    {

                        # Для ссылок на JS файлы
                        $hash = md5($attr['src']);
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


                        # Добавляем найденные арибуты
                        $excludeAttr = ['type' , 'src', 'async', 'defer'] ;
                        # Фильтрация элементов с определенными именами ключей
                        $attr   = \GNZ11\Document\Arrays::filterElemByKey($attr , $excludeAttr ) ;
                        $link['attrs'] = (!empty( $attr ) ? json_encode( $attr )  : null ) ;



                        $link = array_merge($link, $attr);
                        # Если есть параметры в ссылке
                        if( isset($hrefArr[1]) )
                        {
                            # Разобрать параметры ссылки
                            $link['params_query'] = $Links_assets->parseRequestParameters($hrefArr, $link, $href);
                        }#END IF

                        # Записываем статистику
                        self::$statistics['errors'] += (count($log['err']));
                        # Записываем в справочник ресурсов
                        self::$AssetssColection['script'][$hash] = $link;
                        # Записываем hash
                        self::$HashArray['script'][] = $hash ;

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
                        self::$AssetssColection['scriptDeclaration'][$hash] = [
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
        }

        # Получить данные из справочника для <link Css />
        $linkDbList = $this->getItemTableData('link') ;
        # Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
        $this->getNewAssets( 'link' , $linkDbList );

        # Получить данные из справочника для <Style Css />
        $styleDbList = $this->getItemTableData('style') ;
        # Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
        $this->getNewAssets( 'style' , $styleDbList );

        # Получить данные из справочника для <script />
        $scriptDbList = $this->getItemTableData('script') ;
        # Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
        $this->getNewAssets( 'script' , $scriptDbList );

        # Получить данные из справочника для <script Declaration/>
        $scriptDeclarationDbList = $this->getItemTableData('scriptDeclaration') ;
        # Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
        $this->getNewAssets( 'scriptDeclaration' , $scriptDeclarationDbList );
    }

    public function setAssetsToPage(){
        \Plg\Pro_critical\Helpers\Assets\Js::instance()->setScript() ;
        \Plg\Pro_critical\Helpers\Assets\Css::instance()->setCss() ;
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
    }







    /**
     * Найти новые рессурсы которых нет в справочнике и добавить их  в справочник
     * @param $view string Псевдоним вида
     * @throws Exception
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 24.08.2020 21:40
     *
     */
    protected function getNewAssets( $view , $DbList ){

        $Query = $this->db->getQuery(true) ;
        $tbl = self::getTbl($view) ;
        $excludeFields=[ 'err','protocol','absolute_path', 'href'  ];
        $firstElement = reset(self::$AssetssColection[$view] );


        foreach( $firstElement as $key => $itemFile )
        {
            if(  in_array( $key , $excludeFields ) ) continue ; #END IF
            $columns[]= $key ;
        }#END FOREACH

        

        # Индикатор добавления в DB
        $addDB = false ;
        foreach ( self::$AssetssColection[$view] as $hash => &$data ){

            
            # Если есть параметры для найденного рессурса замещаем - параметрами
            if( key_exists($hash, $DbList) )
            {
                $data = $DbList[$hash];
                # Если установлено не загружать для этого рессурса
                if( !$data->load )
                {
                    unset( self::$AssetssColection[$view][$hash] );
                }#END IF
            }
            #Если данные в справочнике не найдены - подготовка добавления в DB
            else
            {

                $excludeLinesArr = self::$params->get('exclude_dynamic_lines_'.$view , [] ) ;
                $Registry = new Registry($excludeLinesArr) ;
                $Arr = $Registry->toArray() ;
                $mapArr = array_map(function($key, $value) {return $value['text'];}, array_keys($Arr), $Arr);


                switch ($view){
                    case 'scriptDeclaration':
                        $testStr = $data['content'] ;
                        break ;
                    default: $testStr = false ;
                }

                # Проверяем на исключение записи в DB ( Component Config )
                if( $testStr )
                {
                    # Найти подстроку из массива в заданной строке
                    $r = \GNZ11\Document\Arrays::strpos_array( $testStr , $mapArr) ;
                    # Если подстрака исключения найдена пропускаем добавление в справочник
                    if(  $r ) {
                        #Переводим в обьект новые найденные ресурсы
                        \GNZ11\Document\Arrays::arrToObj( $data ) ;
                        continue ;
                    } #END IF
                }#END IF

                $addDB = true ;
                # Фильтрация элементов с определенными именами ключей
                $valuesArr   = \GNZ11\Document\Arrays::filterElemByKey($data , $excludeFields ) ;

                $obsoleteQuoted = array_map(array( $this->db , 'quote'),  $valuesArr );
                $Query->values( implode( "," , $obsoleteQuoted ) . PHP_EOL );
            }#END IF

            #Переводим в обьект новые найденные ресурсы
            \GNZ11\Document\Arrays::arrToObj( $data ) ;


        }#END FOREACH







        if( !$addDB ) return ; #END IF
        

        
        
        $Query->insert( $this->db->quoteName( $tbl ) )->columns( $this->db->quoteName( $columns ) );
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
     * Получить назыание таблицы по Псевдониму вида
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
     * @param $view
     * @return array - Ресурсов с настройками
     * @throws Exception
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 24.08.2020 21:49
     *
     */
    protected function getItemTableData($view){
        $tbl = self::getTbl($view) ;
        $obsoleteIDsQuoted = array_map( array( $this->db , 'quote' ), self::$HashArray[$view]);
        $Query = $this->db->getQuery(true);
        $Query->select('*')
            ->from( $this->db->quoteName($tbl) )
            ->where( $this->db->quoteName('hash') . 'IN ('.implode(',' , $obsoleteIDsQuoted  ).')' )
            ->where( $this->db->quoteName('published') . '= 1 ' );
        $this->db->setQuery($Query);
        $res = $this->db->loadObjectList('hash') ;

        return $res ;
    }

    public function saveBody(){
        $body = self::$dom->saveHTML();
        $this->app->setBody($body);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}