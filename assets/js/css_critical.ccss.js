/***********************************************************************************************************************
 * ╔═══╗ ╔══╗ ╔═══╗ ╔════╗ ╔═══╗ ╔══╗  ╔╗╔╗╔╗ ╔═══╗ ╔══╗   ╔══╗  ╔═══╗ ╔╗╔╗ ╔═══╗ ╔╗   ╔══╗ ╔═══╗ ╔╗  ╔╗ ╔═══╗ ╔╗ ╔╗ ╔════╗
 * ║╔══╝ ║╔╗║ ║╔═╗║ ╚═╗╔═╝ ║╔══╝ ║╔═╝  ║║║║║║ ║╔══╝ ║╔╗║   ║╔╗╚╗ ║╔══╝ ║║║║ ║╔══╝ ║║   ║╔╗║ ║╔═╗║ ║║  ║║ ║╔══╝ ║╚═╝║ ╚═╗╔═╝
 * ║║╔═╗ ║╚╝║ ║╚═╝║   ║║   ║╚══╗ ║╚═╗  ║║║║║║ ║╚══╗ ║╚╝╚╗  ║║╚╗║ ║╚══╗ ║║║║ ║╚══╗ ║║   ║║║║ ║╚═╝║ ║╚╗╔╝║ ║╚══╗ ║╔╗ ║   ║║
 * ║║╚╗║ ║╔╗║ ║╔╗╔╝   ║║   ║╔══╝ ╚═╗║  ║║║║║║ ║╔══╝ ║╔═╗║  ║║─║║ ║╔══╝ ║╚╝║ ║╔══╝ ║║   ║║║║ ║╔══╝ ║╔╗╔╗║ ║╔══╝ ║║╚╗║   ║║
 * ║╚═╝║ ║║║║ ║║║║    ║║   ║╚══╗ ╔═╝║  ║╚╝╚╝║ ║╚══╗ ║╚═╝║  ║╚═╝║ ║╚══╗ ╚╗╔╝ ║╚══╗ ║╚═╗ ║╚╝║ ║║    ║║╚╝║║ ║╚══╗ ║║ ║║   ║║
 * ╚═══╝ ╚╝╚╝ ╚╝╚╝    ╚╝   ╚═══╝ ╚══╝  ╚═╝╚═╝ ╚═══╝ ╚═══╝  ╚═══╝ ╚═══╝  ╚╝  ╚═══╝ ╚══╝ ╚══╝ ╚╝    ╚╝  ╚╝ ╚═══╝ ╚╝ ╚╝   ╚╝
 *----------------------------------------------------------------------------------------------------------------------
 * @author Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 07.10.2020 18:10
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 **********************************************************************************************************************/
/**
 * Создание CCSS ====================
 */
/* global jQuery , Joomla   */
window.CssCriticalCore = function (){
    var $ = jQuery ;
    var self = this ;
    this.__type = false ;
    /**
     * Todo Убрать - использовать this.__name
     * @type {boolean}
     * @private
     */
    this.__plugin = false ;
    this.__name = false ;
    this._params = {};
    // Параметры Ajax по умолчвнию
    this.AjaxDefaultData = {
        group : this.__type,
        plugin : this.__plugin ,
        option : 'com_ajax' ,
        format : 'json' ,
        task : null ,
    };
    this.Init = function (){
        this._params = Joomla.getOptions( 'pro_critical' , {} );



        this.setAjaxDefaultData();
        var Data = {
            task : 'CreateCCSS',
            model : '\\Helpers\\Assets\\Css_critical' ,
            AllCssKey : this._params.Css_critical.AllCssKey ,
            url : (typeof this._params.Css_critical.urlPage === 'undefined') ? window.location.href : this._params.Css_critical.urlPage ,
        };

        this.AjaxPost(Data).then(function (response){
            console.log( response )
        })
        console.log(this._params )
        console.log( this.AjaxDefaultData  ) ;
    }

    /**
     * Отправить запрос
     * @param Data - отправляемые данные
     * Должен содержать Data.task = 'taskName';
     * @returns {Promise}
     * @constructor
     */
    this.AjaxPost = function (Data){
        var data = $.extend( true , this.AjaxDefaultData , Data );
        return new Promise( function ( resolve , reject )
        {
            self.getModul( "Ajax" ).then( function ( Ajax )
            {
                // Не обрабатывать сообщения
                Ajax.ReturnRespond = true;
                // Отправить запрос
                Ajax.send( data , self._params.__name ).then( function ( r )
                {
                    resolve( r );
                } , function ( err )
                {
                    console.error( err );
                    reject(err);
                })
            });
        });
    };
    /**
     * Астановка Параметры Ajax по умолчвнию
     */
    this.setAjaxDefaultData = function (){
        this.AjaxDefaultData.group = this._params.__type
        this.AjaxDefaultData.plugin = this._params.__name
    }
    this.Init();
};

window.CssCriticalCore.prototype = new GNZ11();
new window.CssCriticalCore();
