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
 * @date 09.10.2020 12:33
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 **********************************************************************************************************************/

namespace Plg\Pro_critical;
defined('_JEXEC') or die; // No direct access to this file

use DOMElement;
use DOMNode;
use DOMNodeList;
use Exception;
use JDatabaseDriver;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;
use stdClass;

/**
 * Class Html
 * @package Plg\Pro_critical\Helpers\Assets
 * @since 3.9
 * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
 * @date 09.10.2020 12:33
 *
 */
class Html
{
    /**
     * @var \GNZ11\Document\Dom #document
     * @since 3.9
     */
    private static $dom;

    /**
     * @var array Хранение JS данных о задачах для Front
     * @since 3.9
     */
    public static $addJsTask = [] ;
    /**
     * @var array|object Настройки компонента
     * @since 3.9
     */
    /**
     * @var array Хранение элементов <template /> до того пока скрипты не будут перенесены вниз страницы для того что бы
     * из этих тегов не извлекались скрипты
     * @since 3.9
     */
    public static $TemplateNodeCollection = [] ;

    protected static $params;

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
     * @var Html
     * @since  1.6
     */
    public static $instance;

    /**
     * Html constructor.
     * @param $params array|object
     * @throws Exception
     * @since 3.9
     */
    public function __construct($params)
    {
        self::$params = $params ;

        $this->app = Factory::getApplication();
        $this->db = Factory::getDbo();
        return $this;
    }
    /**
     * @param array $options
     *
     * @return Html
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

    public function Run(){
        self::$dom = \Plg\Pro_critical\Assets::$dom ;

        # Получить Html Задания
        $tasks = $this->getTaskHtml();

        # Массив с Id выполненых задач
        $useIdTask = [] ;

        foreach ( $tasks as $task )
        {
            if (isset( $task->task_data )) {
                $task->task_data = json_decode( $task->task_data );
            }#END IF

            $_t = 'task'.ucfirst ( $task->html_processing );

            try
            {

                // Code that may throw an Exception or Error.
                  $useIdTask[] = $this->{$_t}( $task );
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
     * Удаление элементов страницы
     *
     * @param stdClass $paramsTask - параметры задачи
     *
     * @since  3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date   29.01.2021 05:04
     */
    protected function taskElement_removeElement(stdClass $paramsTask){
        # Находим узлы по селеутору
        $Nodes = self::getNodes( $paramsTask->selector );
        foreach ($Nodes as $node)
        {
            $node->parentNode->removeChild($node);
        }

    }

    /**
     * Выполнение задачи для - Скрытие элемента от рейдинга <template />
     * элементы будут отобраны - вложенны в тег <template /> и будут вставлкны на страницу после переноса JS скриптов
     * Это позволяет снизить нагрузку при Script Evaluation
     *
     * @param $paramsTask
     *
     * @throws Exception
     * @since  3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date   10.10.2020 23:13
     *         https://habr.com/ru/post/231845/ - Import Html
     *         https://x-tag.readme.io/docs/getting-started
     */
    protected function taskElement_temlating($paramsTask){

        if( $paramsTask->task_data->event_show == 'removeElement' )
        {
            $this->taskElement_removeElement($paramsTask);
            return ;
        }#END IF


        if( $paramsTask->id == 5 )
        {
//            echo'<pre>';print_r( $paramsTask->selector );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
//            echo'<pre>';print_r( $Nodes );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
//            echo'<pre>';print_r( $paramsTask );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
//            die(__FILE__ .' '. __LINE__ );
        }#END IF


        # Находим узлы по селектору
        $Nodes = self::getNodes( $paramsTask->selector );




        # Создать тег <template />
        $_template = self::$dom->createElement('template');

        # Создать тег-метку для возвращения содержимого тега <template />
        $_markNode = $this->getMarkNodeElement($paramsTask);

        # Если в задачи есть рессурсы для которых нужен прелоадер
        $this->addLinkPreloader(  $paramsTask ) ;

        $task_id = '__template-' . $paramsTask->task_id ;




        foreach ($Nodes as $node)
        {
            $markNode_clone = $_markNode->cloneNode();
            $markNode_clone->setAttribute( 'data-temlate_id' , $task_id );

            $template_clone = $_template->cloneNode();
            $template_clone->setAttribute( 'id' , $task_id );

            $node->parentNode->replaceChild( $markNode_clone , $node );

            $template_clone->appendChild( $node );
            # Сохраняем в коллекцию
            self::$TemplateNodeCollection[] = $template_clone ;

        }#END FOREACH

        $element_for_event = self::getSelectorShow( $paramsTask );
        $jsData = [
            # Id - тега <template />
            'template_id' => $task_id ,
            # тип события триггер для отображения
            'event_type' => $paramsTask->task_data->event_show ,
            # елемент на на котором слушать событие
            'element_event' => $element_for_event ,
        ];
        self::$addJsTask['temlating'][] = $jsData ;
    }

    /**
     * Выполнение задачи для - Скрытие элемента от рейдинга <template /> и заменить
     * @param $paramsTask - параметры задачи
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 11.10.2020 11:01
     *
     */
    protected function taskElement_temlating_and_replace( $paramsTask ){

        # Находим узлы по селеутору
        $Nodes = self::getNodes( $paramsTask->selector );

        # Создать тег <template />
        $_template = self::$dom->createElement('template');

        # Создать тег-метку для возвращения содержимого тега <template />
        $_markNode = $this->getMarkNodeElement($paramsTask);

        # Если в задачи есть рессурсы для которых нужен прелоадер
        $this->addLinkPreloader(  $paramsTask ) ;

        $task_id = '__template-' . $paramsTask->task_id ;

        # Создание маникена
        $HTML = '';
        # Количество повторение шаблона маникена
        $paramsTask->repeat_replacement_pattern = 1 ;
        for ($i = 1; $i <= $paramsTask->repeat_replacement_pattern; $i++) {
            $HTML .= LayoutHelper::render( $paramsTask->task_data->file_replace , [] );
        }



        foreach ($Nodes as $node)
        {

            $markNode_clone = $_markNode->cloneNode();
            $markNode_clone->setAttribute( 'data-temlate_id' , $task_id );
            $this->appendHTML ( $markNode_clone, $HTML );

            $template_clone = $_template->cloneNode();
            $template_clone->setAttribute( 'id' , $task_id );

            $node->parentNode->replaceChild( $markNode_clone , $node );

            $template_clone->appendChild( $node );
            # Сохраняем в коллекцию
            self::$TemplateNodeCollection[] = $template_clone ;
        }#END FOREACH

        $element_for_event = self::getSelectorShow( $paramsTask );
        $jsData = [
            # Id - тега <template />
            'template_id' => $task_id ,
            # тип события триггер для отображения
            'event_type' => $paramsTask->task_data->event_show  ,
            # елемент на на котором слушать событие
            'element_event' => $element_for_event ,
        ];
        self::$addJsTask['temlating'][] = $jsData ;



    }

    /**
     * Создать тег-метку для возвращения содержимого тега <template />
     * @param object $paramsTask параметры HTML задания
     * @return DOMElement
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 29.11.2020 02:01
     */
    public function getMarkNodeElement( $paramsTask ){
        $_markNode = self::$dom->createElement('div');
        $markClass = '__template-mark' ;
        # Если событие для re template попадание в зону видемости -
        # добавить class слижение за позицией
        if ($paramsTask->event_show == 'scroll')
        {
            $_markNode->setAttribute( 'data-position' , 're-template' );
            $markClass .= ' checkPosition' ;
        }#END IF
        $_markNode->setAttribute( 'class' , $markClass );
        # Добавить к маркеру для <template /> - файлы для загрузки перед выполнением задания
        $this->addDataLoadBefore( $_markNode , $paramsTask );
        return $_markNode ;
    }

    /**
     * Найти узлы Dom по селектору
     * @param $selector - jQuery || XPath селектор для поиска узла
     * @return DOMNodeList|false
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 29.11.2020 01:05
     *
     */
    public static function getNodes( $selector ){
        if (strpos( $selector , '/') !== false) {
            $xpathQuery = $selector ;
        }else{
            /**
             * Конвертируем jQuery Селектор в  XPath
             */
            $Translator = new \GNZ11\Document\Dom\Translator( $selector );
            $xpathQuery = $Translator->asXPath();
        }

        $xpath = new \DOMXPath(self::$dom);
        # Найденые узлы
        return $xpath->query( $xpathQuery );
    }

    /**
     * Устанавливаем отобранные теги <template />
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 29.11.2020 00:57
     *
     */
    public function setTemplateCollection(){
        $xpath = new \DOMXPath(self::$dom);
        $parentBody = $xpath->query( '//body');

        foreach ( self::$TemplateNodeCollection as  $template_clone )
        {
            $parentBody->item(0)->appendChild( $template_clone );
        }#END FOREACH

    }

    /**
     * Определение селектора- триггера  для отображения элемента  ( selector_show )
     * @param $paramsTask
     * @return null
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 26.11.2020 12:09
     *
     */
    private static function getSelectorShow( $paramsTask ){
        switch ($paramsTask->task_data->event_show){
            case 'click' :
            case 'hover' :
                $element_for_event = $paramsTask->task_data->selector_show ;
                break ;
            case 'mouse_move' :
                $element_for_event = 'body' ;
                break ;
            case 'scroll' :
                $element_for_event = null ;
                break ;
            default:
                throw new Exception('Code Exception '.__FILE__.':'.__LINE__) ;
                echo'<pre>';print_r( $paramsTask );echo'</pre>'.__FILE__.' '.__LINE__;
                die(__FILE__ .' '. __LINE__ );
        }
        return $element_for_event ;
    }

    /**
     * Если в задачи есть рессурсы для которых нужен прелоадер - установить
     * TODO - добавить разные типы файлов
     * @param $paramsTask Object stdClass - параметры задачи
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 16.10.2020 06:51
     *
     */
    protected function addLinkPreloader( $paramsTask ){
        if ( empty( $paramsTask->additional_task_settings_add_preloader ) ) return ; #END IF
        $Assets = $this->getLinksAssets( $paramsTask->additional_task_settings_add_preloader  ) ;

        foreach ( $Assets as $asset )
        {
            # Создать предварительную загрузку ключевых запросов
            $attr['rel'] = 'preload' ;
            $attr['as'] = 'style' ;
            $attr['href'] = $asset ;
            self::$dom::_setTopHeadTag( self::$dom , 'link',  '',  $attr   );
        }#END FOREACH
    }

    /**
     * Добавить к маркеру для <template /> - файлы для загрузки перед выполнением задания
     * @param $_markNode
     * @param $paramsTask
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 12.10.2020 18:22
     *
     */
    protected function addDataLoadBefore( &$_markNode , $paramsTask ){
        # Если в задании нет файлов для предварительной загрузки
        if ( empty( $paramsTask->asset_load_before_task )) return ; #END IF

        $task_id = '__template-' . $paramsTask->task_id ;
        $asset_load = $this->getLinksAssets( $paramsTask->asset_load_before_task  ) ;
        self::$addJsTask['loadAssets'][$task_id] = $asset_load ;

    }

    private function getLinksAssets( $stingAssets ){
        $asset_load = explode(',' , $stingAssets );
        $asset_load = array_map(array( $this , '_mapLink' ), $asset_load);
        return $asset_load ;
    }

    /**
     * перебрать массив со ссылками для правильного составления url
     * @param $val
     * @return string
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 14.10.2020 13:25
     *
     */
    private function _mapLink( $val ){
        $val = trim($val) ;
        $val = trim($val, "/");
        return \Joomla\CMS\Uri\Uri::root(). trim($val);
    }

    /**
     * Вставить в DOM Node - html тег-метку для возвращения содержимого тега <template />
     * @param DOMNode $parent
     * @param $source - Html
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 11.10.2020 20:56
     *
     */
    public function appendHTML(DOMNode $parent, $source) {
        $tmpDoc = new \GNZ11\Document\Dom();
        $tmpDoc->loadHTML($source);
        foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $node = $parent->ownerDocument->importNode($node, true);
            $parent->appendChild($node);
        }
    }

    /**
     * Обработка задания для отложенной загрузки изоброжений
     * @param $paramsTask - параметры задачи
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 10.10.2020 05:41
     */
    protected function taskImg_deferred( $paramsTask ){

        # Заглушка для мзображений
        $print = \Joomla\CMS\Uri\Uri::root().'media/com_pro_critical/images/goods-stub.svg' ;

        $Translator = new \GNZ11\Document\Dom\Translator( $paramsTask->selector );
        $xpathQuery = $Translator->asXPath();
        $xpath = new \DOMXPath(self::$dom);
        $Nodes = $xpath->query( $xpathQuery );

        foreach ($Nodes as $node)
        {
            $attr = self::$dom::getAttrElement($node, []);

            if ( !key_exists('class' ,  $attr ) )
            {
                $attr['class'] = '';
            }#END IF
            $attr['class'].=' checkPosition' ;
            $attr['data-position'].='img-deferred' ;

            if( isset( $paramsTask->task_data->width ) )
            {
                $attr['width'] = $paramsTask->task_data->width ;
            }#END IF

            if( isset( $paramsTask->task_data->height ) )
            {
                $attr['height'] = $paramsTask->task_data->height ;
            }#END IF

//            if( $paramsTask->id == 10 )
//            {
//                echo'<pre>';print_r( $paramsTask->task_data->width );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
//                die(__FILE__ .' '. __LINE__ );
//            }#END IF

            # Установка атрибутов узла
            $node->setAttribute('src', $print );
            $node->setAttribute('data-src',$attr['src']);
            unset( $attr['src'] ) ;

            foreach ( $attr as $k => $item)
            {
                $node->setAttribute( $k ,$item );
            }#END FOREACH
            self::$dom->saveHTML($node);
        }#END FOREACH
    }




    /**
     * Плучить список заданий
     * @return array|mixed
     * @since 3.9
     * @auhtor Gartes | sad.net79@gmail.com | Skype : agroparknew | Telegram : @gartes
     * @date 10.10.2020 06:26
     *
     */
    protected function getTaskHtml(){
        $__view = 'view' ;

        $option = $this->app->input->get( 'option' , false , 'STRING' );

        if ($option == 'com_jshopping')  $__view = 'controller' ;#END IF
        $view = $this->app->input->get( $__view , false , 'STRING' );


        $WebClient = new \Joomla\Application\Web\WebClient();
        $mobile = $WebClient->__get('mobile');

//        echo'<pre>';print_r( $mobile );echo'</pre>'.__FILE__.' '.__LINE__ . PHP_EOL;
//        die(__FILE__ .' '. __LINE__ );


        $Query = $this->db->getQuery(true);
        $Query->select('*')->from( $this->db->quoteName( '#__pro_critical_directory_components' , 'c' ));
        $Query->leftJoin( $this->db->quoteName('#__pro_critical_html_task' , 't' ) . ' ON  (c.id = t.id_component OR 0 = t.id_component )' )  ;
        $where = [
           '('
                . $this->db->quoteName( 'c.value_option') . '='. $this->db->quote( $option )
                .'OR'
                . $this->db->quoteName( 't.id_component') . '='. $this->db->quote( 0 )
           .')'
            ,

            $this->db->quoteName( 't.published') . '='. $this->db->quote( 1 ) ,
        ];


        if( $mobile )
        {
            $where[] = $this->db->quoteName( 't.type_device_id') . 'IN (0,1)' ;
        }else{
            $where[] = $this->db->quoteName( 't.type_device_id') . 'IN (0,2)' ;
        }#END IF
        
        $Query->where($where);


//        echo $Query->dump() ;
        $this->db->setQuery( $Query ) ;
        $tasks = $this->db->loadObjectList();






        
        # Перебираем задачи
        foreach ( $tasks as $i => &$task )
        {
            $Registry = new Registry( $task->query_params ) ;
            $task->query_params = $Registry->toObject() ;
            foreach ( $task->query_params as $query_param )
            {
                # проверяем по параметрам запроса на соответствии задачи
                # Если нет соответствия - убиваем задачу
                $query = $this->app->input->get( $query_param->query ) ;
                if ( $query != $query_param->value )
                {
                    unset( $tasks[$i] ) ;
                }#END IF

            }#END FOREACH
        }#END FOREACH


        
        return $tasks ;
    }


}