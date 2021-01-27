<?php

/***********************************************************************************************************************
 * ╔═══╗ ╔══╗ ╔═══╗ ╔════╗ ╔═══╗ ╔══╗  ╔╗╔╗╔╗ ╔═══╗ ╔══╗   ╔══╗  ╔═══╗ ╔╗╔╗ ╔═══╗ ╔╗   ╔══╗ ╔═══╗ ╔╗  ╔╗ ╔═══╗ ╔╗ ╔╗ ╔════╗
 * ║╔══╝ ║╔╗║ ║╔═╗║ ╚═╗╔═╝ ║╔══╝ ║╔═╝  ║║║║║║ ║╔══╝ ║╔╗║   ║╔╗╚╗ ║╔══╝ ║║║║ ║╔══╝ ║║   ║╔╗║ ║╔═╗║ ║║  ║║ ║╔══╝ ║╚═╝║ ╚═╗╔═╝
 * ║║╔═╗ ║╚╝║ ║╚═╝║   ║║   ║╚══╗ ║╚═╗  ║║║║║║ ║╚══╗ ║╚╝╚╗  ║║╚╗║ ║╚══╗ ║║║║ ║╚══╗ ║║   ║║║║ ║╚═╝║ ║╚╗╔╝║ ║╚══╗ ║╔╗ ║   ║║
 * ║║╚╗║ ║╔╗║ ║╔╗╔╝   ║║   ║╔══╝ ╚═╗║  ║║║║║║ ║╔══╝ ║╔═╗║  ║║─║║ ║╔══╝ ║╚╝║ ║╔══╝ ║║   ║║║║ ║╔══╝ ║╔╗╔╗║ ║╔══╝ ║║╚╗║   ║║
 * ║╚═╝║ ║║║║ ║║║║    ║║   ║╚══╗ ╔═╝║  ║╚╝╚╝║ ║╚══╗ ║╚═╝║  ║╚═╝║ ║╚══╗ ╚╗╔╝ ║╚══╗ ║╚═╗ ║╚╝║ ║║    ║║╚╝║║ ║╚══╗ ║║ ║║   ║║
 * ╚═══╝ ╚╝╚╝ ╚╝╚╝    ╚╝   ╚═══╝ ╚══╝  ╚═╝╚═╝ ╚═══╝ ╚═══╝  ╚═══╝ ╚═══╝  ╚╝  ╚═══╝ ╚══╝ ╚══╝ ╚╝    ╚╝  ╚╝ ╚═══╝ ╚╝ ╚╝   ╚╝
 *----------------------------------------------------------------------------------------------------------------------
 * @author Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 07.10.2020 16:30
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 **********************************************************************************************************************/

namespace Plg\Pro_critical\Helpers\Assets;
defined('_JEXEC') or die; // No direct access to this file
require JPATH_PLUGINS.'/system/pro_critical/matthiasmullie/vendor/autoload.php';


use Exception;
use JDatabaseDriver;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use \MatthiasMullie\Minify;
/**
 * Class Css_critical
 * @package Plg\Pro_critical\Helpers\Assets
 * @since 3.9
 * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 07.10.2020 16:30
 *
 */
class Css_critical
{
    /**
     * @var array|object Настройки компонента
     * @since 3.9
     */
    private static $params;
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
     * Array to hold the object instances
     *
     * @var Css_critical
     * @since  1.6
     */
    public static $instance;
    /**
     * @var bool
     * @since 3.9
     */
    public static $CriticalCssData = false ;
    /**
     * Путь для сохранения файла AllCSS
     * @since 3.9
     */
    const ALL_CSS_PATCH = JPATH_BASE . DIRECTORY_SEPARATOR .'cache'. DIRECTORY_SEPARATOR .'_allCss'. DIRECTORY_SEPARATOR ;
    const ALL_CSS_URL_PATCH  = 'cache'. DIRECTORY_SEPARATOR .'_allCss'. DIRECTORY_SEPARATOR ;
    /**
     * Css_critical constructor.
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

        # Если Админ Панель
        if( $this->app->isClient( 'administrator' ) ) return $this; #END IF
        # Если создание CCSS отключено
        if ( !self::$params->get('css_critical_on')) return $this;  #END IF



        # Найти CCSS в DB
        self::$CriticalCssData = $this->getCriticalCss();




        # Если CCSS не созданы создать задачу для FRONT
        if ( !self::$CriticalCssData )
        {
            $plugin_param = self::$params->get('plugin_param');
            $__v = $plugin_param->get('__v');
            \GNZ11\Core\Js::addJproLoad(Uri::root().'plugins/system/pro_critical/assets/js/css_critical.ccss.js?v=' . $__v );

            # Получить ключ текущей страницы
            $key = \Plg\Pro_critical\Helper::$PageKey ;
            # URL - Страницы
            $urlPage = self::getAddressURI() ;

//            echo'<pre>';print_r( $this->app->input );echo'</pre>'.__FILE__.' '.__LINE__;
//            die(__FILE__ .' '. __LINE__ );



            ####--------------------------------------------------------------------------------------------------------
            ## - Добавить в URL дополнительные GET параметры которые указаны в настройках компонента->CCSS
            $addParamArr = [];
            $additionalRequestParametersCcss = self::$params->get('additional_request_parameters_ccss', []);
            foreach ( $additionalRequestParametersCcss as $additionalRequest)
            {
                $val = $this->app->input->get($additionalRequest->query , null ) ;
                if ( !$val ) continue ; #END IF
                $addParamArr[] = $additionalRequest->query.'='.$val ;
            }#END FOREACH
            if ( count( $addParamArr ) )
            {
                $addParam = implode('&' , $addParamArr ) ;
                if (strpos($urlPage, '?') !== false) {
                    $urlPage .= '&'.$addParam;
                }else{
                    $urlPage .= '?'.$addParam;
                }
            }#END IF
            ####--------------------------------------------------------------------------------------------------------


            $data = [
                '__v' => $__v ,
                'Css_critical'=> [
                    'AllCssKey' => $key , // ключ allCss file
                    'urlPage' => $urlPage , // ссылка на страницу
                ],

            ];
            \Joomla\CMS\Factory::getDocument()->addScriptOptions('pro_critical' , $data , true ) ;


        }#END IF

        return $this;
    }
    /**
     * @param array $options
     *
     * @return Css_critical
     * @throws Exception
     * @since 3.9
     */
    public static function instance($options = array())
    {
        if (self::$instance === null)
        {
            self::$instance = new self($options);
        }
        return self::$instance;
    }

    /**
     * Получить URL - запрашиваемой страницы
     * @return string
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 13.10.2020 05:48
     *
     */
    public static function getAddressURI() {
        $protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        return $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    /**
     * Проверить Если не созданы CCSS -
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 07.10.2020 21:20
     *
     */
    public static function checkCriticalCssData(){
        if ( !self::$CriticalCssData )
        {
            # Создать файл AllCss
            self::$instance->getAllCss();
            return false ;
        }#END IF
        return true ;
    }

    /**
     * Создать файл AllCss Вытащить все стили из найденных рессурсов
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 07.10.2020 21:25
     *
     */
    protected function getAllCss(){

        # Получить ключ текущей страницы
        $key = \Plg\Pro_critical\Helper::$PageKey;
        # Имя файла AllCss
        $fileName = self::ALL_CSS_PATCH . $key .  '.css' ;

        # Если файл AllCss - существует
        if ( \GNZ11\Core\Filesystem\File::exists( $fileName ) ) return ; #END IF

        $Registry = new Registry( \Plg\Pro_critical\Helpers\Assets\Css::$AssetssCollection['link'] ) ;
        $CollectionLink = $Registry->toObject() ;

        $fileArr = [] ;

        $minifier = new \MatthiasMullie\Minify\CSS(  );
        $minifier->setMaxImportSize(100);

        foreach ($CollectionLink as $fileData ){

            # Если файл внутренний
            if (\GNZ11\Core\Filesystem\File::isInternal($fileData->file))
            {

                # Если в настройках файлов установлено не использовать при создании CCSS
                # Эта настройка предназначена для CSS файлов которые используются для печати
                # в некоторых для ссылок псевдо элемент :after содержит текс аребута href
                # ect. a:link:after{ content: ' (' attr(href) ')'; }
                if (!$fileData->use_critical) continue ;   #END IF

                # для создания пути к файлу убираем домен
                $fileData->file = str_replace( \Joomla\CMS\Uri\Uri::root() , '' , $fileData->file)  ;
                $fileData->file = str_replace( \Joomla\CMS\Uri\Uri::root(true) , '' , $fileData->file)  ;
                if (strpos($fileData->file, '/') !== 0) {
                    $fileData->file = '/'.$fileData->file ;
                }


                $fileData->file = JPATH_ROOT .$fileData->file ;
            }else{


                die(__FILE__ .' '. __LINE__ );


            }#END IF

            $fileArr[] = $fileData->file ;
            $minifier->add( $fileData->file ) ;
        }

        $style = \Plg\Pro_critical\Helpers\Assets\Css::$AssetssCollection['style'] ;


        foreach ( $style as $item)
        {
            $content = trim( $item->content ) ;
            if ( empty($content) ) continue ; #END IF
            $minifier->add( $content ) ;
        }#END FOREACH

        $allCss = $minifier->minify() ;

//        echo'<pre>';print_r( $allCss );echo'</pre>'.__FILE__.' '.__LINE__;
//        die(__FILE__ .' '. __LINE__ );


        # Записываем в файл
        \GNZ11\Core\Filesystem\File::write( $fileName ,  $allCss ) ;
    }
    /**
     * Найти CCSS по ключу $PageKey
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 07.10.2020 17:38
     *
     */
    protected function getCriticalCss(){
        # Получить ключ текущей страницы
        $key = \Plg\Pro_critical\Helper::$PageKey ;
        $Query = $this->db->getQuery(true);
        $Query->select( [$this->db->quoteName('critical_css_code') , $this->db->quoteName('add_css_code')] )
            ->from($this->db->quoteName('#__pro_critical_css'));
        $where = [
            $this->db->quoteName('page_key') .'='. $this->db->quote( $key ) ,
            $this->db->quoteName('published') .'='. $this->db->quote( 1 ) ,
        ];
        $Query->where( $where );
        $this->db->setQuery($Query);





        return $this->db->loadObject();
    }

    /**
     * Создание CCSS
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 08.10.2020 20:29
     *
     */
    public function CreateCCSS(){
        $AllCssKey = $this->app->input->get('AllCssKey' , false, 'STRING');
        $url = $this->app->input->get('url' , false, 'STRING');
        # Ссылка на файла AllCss
        $fileUrl =  \Joomla\CMS\Uri\Uri::root() . self::ALL_CSS_URL_PATCH . $AllCssKey . '.css' ;
        $filePathAllCss =  self::ALL_CSS_PATCH . $AllCssKey . '.css' ;

        $client = new \Joomla\Application\Web\WebClient();
        $platform = $client->__get('platform');
        $mobile = $client->__get('mobile') ;
        $screen_sizes = self::$params->get('screen_sizes') ;
        $dataSizes = $screen_sizes->{'screen_sizes' . (!$mobile?0:1) };

        $Arrdata = [
            // Задача
            'task' => 'getCtiticalCss' ,
            // ссылка на файл AllCss
            'cssUrl'  =>   $fileUrl,
            // Ссылка на страницу для создания CCSS
            'urlSite' =>  $url ,
            // Юзер-Агент браузера
            'userAgent' => $dataSizes->ua ,
            // Ширина экрана
            'width' => $dataSizes->width ,
            // Высота экрана
            'height' => $dataSizes->height ,
        ];

        $res = $this->generateCCSS( $Arrdata );

        if ( !$res )
        {
            echo new \JResponseJson( null , 'Данные не получены' , true );
            die();
        }#END IF





        # Если ответ получен удачный - Удалить файл AllCss
        \GNZ11\Core\Filesystem\File::delete( $filePathAllCss ); #END IF
        


        $addRes = $this->saveCCSS( $res , $AllCssKey );
        $result = [
            # Id - Критических стилей в DB
            'CCSS_ID' => $addRes ,
            # переданные данные
            'SentData' => $Arrdata ,
        ];
        echo new \JResponseJson($result);
        die();
    }

    /**
     * Сохранене CCSS в справочнике
     * @param $res
     * @param $page_key
     * @return mixed
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 09.10.2020 05:30
     *
     */
    public function saveCCSS($res, $page_key)
    {
        $url = $this->app->input->get('url' , false, 'STRING');
        $url_page_id = $this->findUrl($url);
        if ( !$url_page_id )
        {
            $url_page_id = $this->saveUrl($url);
        }#END IF

        $CCSS = $res->data[0]->criticalCss;
        # Добавить css из настроек компонента
        # Эти стили будут добавленны ковсем страницам в момент генирации критических стилей
        $CCSS .= self::$params->get('add_to_after_ccss' , null ) ;

        
        
        $Query = $this->db->getQuery(true);
        $table = $this->db->quoteName('#__pro_critical_css');
        $columns = array('critical_css_code', 'page_key', 'pro_critical_url_id');
        $values =
            $this->db->quote($CCSS) . ","
            . $this->db->quote($page_key). ","
            . $this->db->quote($url_page_id);

        $Query->values($values);
        $Query->insert($table)->columns($this->db->quoteName($columns));
        $this->db->setQuery($Query);
        //echo $query->dump();
        $this->db->execute();

        # Id - Вствленной стороки
        return $this->db->insertid();
    }

    /**
     * Отправить запрос для создания CCSS
     * @param $ArrData
     * @return mixed
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 09.10.2020 01:58
     *
     */
    public function generateCCSS($ArrData){
        $URL = self::$params->get('css_critical_url_api');
        $key = self::$params->get('css_critical_key_api');
        $Curl = curl_init();
        curl_setopt_array( $Curl, [
            CURLOPT_URL            => $URL ,
            CURLOPT_TIMEOUT        => 400,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($ArrData),
        ]);
        $response = curl_exec( $Curl );
        curl_close( $Curl );
        return json_decode( $response  );
    }

    /**
     * Найти Url в справочнике
     * @param $url
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 09.10.2020 05:32
     *
     */
    public function findUrl( $url ){
        $Query = $this->db->getQuery(true);
        $Query->select( $this->db->quoteName('id'))
            ->from( $this->db->quoteName('#__pro_critical_url'));
        $where = [
            $this->db->quoteName('url_page') .'='.$this->db->quote($url)
        ];
        $Query->where($where);
        $this->db->setQuery($Query);
        return $this->db->loadResult();
    }

    /**
     * Сохранить Url в справочнике
     * @param $url
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 09.10.2020 05:38
     *
     */
    public function saveUrl( $url ){
        $Query = $this->db->getQuery(true);
        $table = $this->db->quoteName('#__pro_critical_url');
        $columns = array('url_page' );
        $values =
            $this->db->quote($url)  ;

        $Query->values($values);
        $Query->insert($table)->columns($this->db->quoteName($columns));
        $this->db->setQuery($Query);
        //echo $query->dump();
        $this->db->execute();
        return $this->db->insertid();
    }

}