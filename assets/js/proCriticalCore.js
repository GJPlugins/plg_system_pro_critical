/***********************************************************************************************************************
 * ╔═══╗ ╔══╗ ╔═══╗ ╔════╗ ╔═══╗ ╔══╗  ╔╗╔╗╔╗ ╔═══╗ ╔══╗   ╔══╗  ╔═══╗ ╔╗╔╗ ╔═══╗ ╔╗   ╔══╗ ╔═══╗ ╔╗  ╔╗ ╔═══╗ ╔╗ ╔╗ ╔════╗
 * ║╔══╝ ║╔╗║ ║╔═╗║ ╚═╗╔═╝ ║╔══╝ ║╔═╝  ║║║║║║ ║╔══╝ ║╔╗║   ║╔╗╚╗ ║╔══╝ ║║║║ ║╔══╝ ║║   ║╔╗║ ║╔═╗║ ║║  ║║ ║╔══╝ ║╚═╝║ ╚═╗╔═╝
 * ║║╔═╗ ║╚╝║ ║╚═╝║   ║║   ║╚══╗ ║╚═╗  ║║║║║║ ║╚══╗ ║╚╝╚╗  ║║╚╗║ ║╚══╗ ║║║║ ║╚══╗ ║║   ║║║║ ║╚═╝║ ║╚╗╔╝║ ║╚══╗ ║╔╗ ║   ║║
 * ║║╚╗║ ║╔╗║ ║╔╗╔╝   ║║   ║╔══╝ ╚═╗║  ║║║║║║ ║╔══╝ ║╔═╗║  ║║─║║ ║╔══╝ ║╚╝║ ║╔══╝ ║║   ║║║║ ║╔══╝ ║╔╗╔╗║ ║╔══╝ ║║╚╗║   ║║
 * ║╚═╝║ ║║║║ ║║║║    ║║   ║╚══╗ ╔═╝║  ║╚╝╚╝║ ║╚══╗ ║╚═╝║  ║╚═╝║ ║╚══╗ ╚╗╔╝ ║╚══╗ ║╚═╗ ║╚╝║ ║║    ║║╚╝║║ ║╚══╗ ║║ ║║   ║║
 * ╚═══╝ ╚╝╚╝ ╚╝╚╝    ╚╝   ╚═══╝ ╚══╝  ╚═╝╚═╝ ╚═══╝ ╚═══╝  ╚═══╝ ╚═══╝  ╚╝  ╚═══╝ ╚══╝ ╚══╝ ╚╝    ╚╝  ╚╝ ╚═══╝ ╚╝ ╚╝   ╚╝
 *----------------------------------------------------------------------------------------------------------------------
 * @author Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 09.10.2020 10:08
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 **********************************************************************************************************************//* global jQuery , Joomla   */
window.proCriticalCore = function () {
    var $ = jQuery;
    var self = this;
    // Домен сайта
    var host = Joomla.getOptions('GNZ11').Ajax.siteUrl;
    this.__type = false;
    this.__plugin = false;
    this.__name = false;
    this._params = {};
    // Параметры Ajax по умолчвнию
    this.AjaxDefaultData = {
        group: this.__type,
        plugin: this.__plugin,
        option: 'com_ajax',
        format: 'json',
        task: null,
    };
    this.Tasks = {};
    this.Init = function () {

        this._params = Joomla.getOptions('pro_critical', {});
        this.Tasks = JSON.parse( $('#pro_critical-script-Task').text() ) ;

        this.setAjaxDefaultData();

        // Установка обработки событий
        this._addEventListener();

        // проверка зоны видимости на экране
        self.onCheckPosition();
        // Дозагрузка CSS
        this.loadLaterCss();
        // Выполнить Html задания
        this.performTaskHtml()

        console.log(this._params)
        console.log(this.AjaxDefaultData);
    }
    /**
     * Установка обработки событий
     * @private
     */
    this._addEventListener = function (){
        window.addEventListener('scroll' , self.onCheckPosition ,  { passive: true   }  );
    };
    /**
     * Выполнить Html задания
     */
    this.performTaskHtml = function (){
        console.log( this.Tasks )
        $.each(this.Tasks.html.temlating , function ( i , a ){

            // Для  Событие отображения. - по умолчанию ! Показываем сразу
            if ( +a.event_type === 0 ){
                console.log( a.template_id )
                prox(a)
                return ;
            }

            switch ( a.event_type ){
                case 'hover':
                    console.log( a.element_event )
                    $( a.element_event ).on('mouseover',function (){
                        $( a.element_event ).off('mouseover');
                        console.log(a)
                        prox(a)
                    });
                    /*$( a.element_event ).hover(
                        function(){
                            prox(a)
                        },
                        function(){ });*/
                    break ;
                case 'mouse_move':
                    $('body').on('mousemove.reTemplate' , function (){
                        $('body').off('mousemove.reTemplate');
                        prox(a)
                    })
                    /*$('body').mousemove(function(){
                        prox(a)
                    });*/
                    break ;
                default :
                    console.log( a.element_event )
                    console.log( a.event_type )
                    console.log( a  )
                    $( a.element_event ).on( a.event_type , { __setting : a , passive: true } , self.reTemlatingElement );
            }


        });
        function prox(a){
            var p = {
                data : {
                    __setting : {
                        template_id : a.template_id ,
                    }
                }
            };
            setTimeout(function (){
                self.reTemlatingElement( p );
            },500);
        }
    };
    /**
     * Достать из тега <temlate /> и установить на страницу
     * @param event
     */
    this.reTemlatingElement = function ( event ){
        var params = event.data.__setting ;
        var template_id = params.template_id
        var $template = $( 'template#'+template_id );
        if (!$template[0]){
            console.warn('Уточните Параметры запроса  для HTML Задачи "'+template_id+'"');
            return ;
        }
        var htmlTemplate = $template.html().trim();
        var TemplateClone = $(htmlTemplate);
        var $templateMark = $('.__template-mark[data-temlate_id="'+params.template_id+'"]');
        var load_before = $templateMark.data('load_before')

        if (typeof self.Tasks.html.loadAssets === 'undefined' || typeof self.Tasks.html.loadAssets[template_id] === 'undefined' ){
            $templateMark.replaceWith($(TemplateClone));
        }else{
            //   Загрузка дополнительных ресурсов
            var Assets = self.Tasks.html.loadAssets[template_id];
            var arrLoad = [] ;
            var Ext ;
            $.each(Assets , function (i , Asset ){
                Ext = self.FILE_SYSTEM.getExtensionInPath(Asset) ;
                arrLoad.push( self.load[Ext](  Asset ) );
            });
            // console.log( arrLoad )
            Promise.all(arrLoad).then(function (a){
                $templateMark.replaceWith($(TemplateClone));
            },function (err){console.log(err)});
        }

    }

    /**
     * Триггеры от позиции на экране
     */
    this.onCheckPosition = function (){
        var $checkPositionElements =  $('.checkPosition')

        if ( !$checkPositionElements.length ) {
            $(document).off('scroll.proCriticalCore' );
            return  ;
        }



        $.each($checkPositionElements , function (i,a ){
            var $El = $(a);

            if ( self.Optimizing.checkPosition( $El ) ){

                if ($El.hasClass('off-load')) return ;
                var positionTrigger = $El.data('position');

                switch ( positionTrigger ) {
                    // Отложенная загрузка изображений
                    case 'img-deferred' :
                        var src = $El.data('src') ;
                        $El.attr('src' , src)  ;
                        $El.removeAttr('data-src')
                            .removeAttr('data-position').removeClass('checkPosition')
                            .addClass('off-load')
                        break ;
                    // Когда элемент попадает в видимость загружаем необходимые ресурсы ( JS or CSS )
                    case 'asset-deferred' :
                        var asset = $El.data('assets-load');
                        var parseResult = self.parseURL(asset  );
                        var tag = parseResult.extension ;
                        self.load[tag](host+asset).then(function (){
                            $El.removeAttr('data-assets-load')
                                .removeAttr('data-position')
                                .removeClass('checkPosition')
                        },function (err){
                            console.log('asset-deferred' , err)
                        });
                        // console.log( host )
                        // console.log( asset )
                        // console.log( parseResult )
                        // console.log( $El )
                        break ;
                    // Обработка теневых томов <template />
                    case 're-template':
                        var temlateSelector = $El.data('temlate_id');
                        var p = {
                            data : {
                                __setting : {
                                    template_id : $El.data('temlate_id') ,
                                }
                            }
                        };

                        setTimeout(function (){ self.reTemlatingElement( p ); },500);
                        break ;
                }
            }
        });
    }

    /**
     * Дозагрузка CSS
     */
    this.loadLaterCss = function (){
        // var LaterCss = JSON.parse( $('#pro_critical-script-LoadLaterCss').text() ) ;
         console.log(this.Tasks.Css.loadLaterCss.link )
         console.log(this.Tasks.Css.loadLaterCss.stile )
        var LaterCssLink = this.Tasks.Css.loadLaterCss.link ;



        $.each(LaterCssLink , function (i,a){
            // make a stylesheet link
            var link = document.createElement( "link" );
            link.rel = "stylesheet";
            link.href = a.href ;
            // insert it at the end of the head in a legacy-friendly manner
            document.head.insertBefore( link, document.head.childNodes[ document.head.childNodes.length - 1 ].nextSibling );

        });

        $.each(this.Tasks.Css.loadLaterCss.stile , function (i,a){
            var css = a.content ,
                head = document.head || document.getElementsByTagName('head')[0],
                style = document.createElement('style');

            head.appendChild(style);

            style.type = 'text/css';
            if (style.styleSheet){
                // This is required for IE8 and below.
                style.styleSheet.cssText = css;
            } else {
                style.appendChild(document.createTextNode(css));
            }
        });

    }
    /**
     * Отправить запрос
     * @param Data - отправляемые данные
     * Должен содержать Data.task = 'taskName';
     * @returns {Promise}
     * @constructor
     */
    this.AjaxPost = function (Data) {
        var data = $.extend(true, this.AjaxDefaultData, Data);
        return new Promise(function (resolve, reject) {
            self.getModul("Ajax").then(function (Ajax) {
                // Не обрабатывать сообщения
                Ajax.ReturnRespond = true;
                // Отправить запрос
                Ajax.send(data, self._params.__name).then(function (r) {
                    resolve(r);
                }, function (err) {
                    console.error(err);
                    reject(err);
                })
            });
        });
    };
    /**
     * Астановка Параметры Ajax по умолчвнию
     */
    this.setAjaxDefaultData = function () {
        this.AjaxDefaultData.group = this._params.__type
        this.AjaxDefaultData.plugin = this._params.__name
    }

    this.Init();
};

window.proCriticalCore.prototype = new GNZ11();
new window.proCriticalCore();
