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
 * @date 25.08.2020 19:33
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 **********************************************************************************************************************/

namespace Plg\Pro_critical\Helpers\Assets;
defined('_JEXEC') or die; // No direct access to this file

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Exception;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Class Js
 * @package Plg\Pro_critical\Helpers\Assets
 * @since 3.9
 * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 25.08.2020 19:33
 *
 */
class Js extends \Plg\Pro_critical\Assets
{
    /**
     * @var CMSApplication|null
     * @since 3.9
     */
    protected $app;
    /**
     * @var \JDatabaseDriver|null
     * @since 3.9
     */
    protected $db;
    /**
     * Array to hold the object instances
     *
     * @var Js
     * @since  1.6
     */
    public static $instance;

    /**
     * Js constructor.
     * @param $params array|object
     * @throws Exception
     * @since 3.9
     */
    public function __construct($params)
    {
        $this->app = Factory::getApplication();
        $this->db = Factory::getDbo();
        self::$dom = parent::$dom ;
    }

    /**
     * @param array $options
     *
     * @return Js
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
     * Установить отобранные скрипты в экземляр DOM
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 25.08.2020 23:10
     *
     */
    public function setScriptLink(){

        if ( !isset(self::$AssetssCollection['script'] ) )  return ; #END IF
        if ( !self::$params->get('moving_scripts_to_bottom' , false ) ) return ; #END IF


        /**
         * Типы JS скриптов которые не обязательны
         */
        $this->excludedTypes = ['text/javascript',];

        # Перебираем коллекцию ссылок на JS файлы
        foreach (self::$AssetssCollection['script'] as &$script )
        {

            if( $script->delayed_loading )
            {
               \GNZ11\Core\Js::addJproLoad(Uri::root().$script->file  ,   false ,   false );
               continue ;
            }#END IF
            
            
            $attr = $this->getAttr( $script ) ;
            $attr['src'] = $this->getFile( $script );
            $attr['async']= $script->async ;
            $attr['defer']= $script->defer ;

            try
            {
                self::$dom::writeDownTag ( self::$dom , 'script' , null , $attr );
                // throw new Exception('Code Exception '.__FILE__.':'.__LINE__) ;
            }
            catch (Exception $e)
            {
                // Executed only in PHP 5, will not be reached in PHP 7
                echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__;
                die(__FILE__ .' '. __LINE__ );
            }



        }#END FOREACH










    }

    /**
     * Установка - тегов <script /> и Joomla Options
     * @since  3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date   29.01.2021 18:52
     *
     */
    public function setScriptTags(){

        if( !isset( self::$AssetssCollection['scriptDeclaration'] ) )
        {
            return ;
        }#END IF





        # перебираем JAVAScript теги
        # TODO Добавить Выбор типа контента для скрипта - ( Original | Minified | Overridden )
        foreach (self::$AssetssCollection['scriptDeclaration'] as &$scriptDeclaration )
        {

            $attr = $this->getAttr( $scriptDeclaration ) ;

            # Если TYPE рессурса не из исключенных добавляем его к атрибутам
            !in_array( $scriptDeclaration->type , $this->excludedTypes ) ? $attr['type'] = $scriptDeclaration->type : null;

            if (!is_array( $scriptDeclaration ))
            {
                $Registry = new Registry($scriptDeclaration);
                $scriptDeclaration = $Registry->toArray();
            }#END IF

            /**
             * Ловим Joomla Options
             */
            if( $scriptDeclaration['type'] == 'application/json' && ( isset($attr['class']) && $attr['class']=='joomla-script-options new' ) )
            {
                $doc = Factory::getDocument();
                $JoomlaOptions = $doc->getScriptOptions();

                self::$AssetssCollection['scriptDeclaration'] ;
                $scriptDeclaration['content'] = json_encode( $JoomlaOptions ) ;
                /* echo'<pre>';print_r( $JoomlaOpions );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;


                 echo'<pre>';print_r( self::$AssetssCollection['scriptDeclaration'] );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;

                 die(__FILE__ .' '. __LINE__ );*/
            }#END IF


            self::$dom::writeDownTag ( self::$dom , 'script' , $scriptDeclaration['content'] , $attr );
        }
    }





}






