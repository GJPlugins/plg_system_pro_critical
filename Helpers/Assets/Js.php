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
    private $app;
    /**
     * @var \JDatabaseDriver|null
     * @since 3.9
     */
    private $db;
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
    public function setScript(){

        /**
         * Типы JS скриптов которые не обязательны
         */
        $this->excludedTypes = ['text/javascript',];

        foreach (self::$AssetssColection['script'] as &$script )
        {
            $attr = $this->getAttr( $script ) ;
            $attr['src'] = $this->getFile( $script );
            self::$dom::writeDownTag ( self::$dom , 'script' , null , $attr );
        }#END FOREACH

        # TODO Добавить Выбор типа контента для скрипта - ( Original | Minified | Overridden )
        foreach ( self::$AssetssColection['scriptDeclaration']  as &$scriptDeclaration )
        {
            $attr = $this->getAttr( $scriptDeclaration ) ;
            # Если TYPE рессурса не из исключенных добавляем его к атрибутам
            !in_array( $scriptDeclaration->type,$this->excludedTypes )?$attr['type']=$scriptDeclaration->type:null;

            self::$dom::writeDownTag ( self::$dom , 'script' , $scriptDeclaration->content , $attr );
        }

    }







}






