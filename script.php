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
 * @date 23.08.2020 17:58
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 **********************************************************************************************************************/

use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Class pro_criticalInstallerScript
 * @since 3.9
 */
class pro_criticalInstallerScript
{
    /**
     * @var string Url архива библиотеки GNZ11
     * @since 3.9
     */
    const Gnz11InstallUrl = 'https://github.com/gartes/GNZ11/archive/master.zip';
    /**
     * Минимальная требуемая версия библиотеки GNZ11
     * @var string
     * @since 3.9
     */
    protected $minimum_version_gnz11;
    /**
     * @var \Joomla\CMS\Application\CMSApplication
     * @since 3.9
     */
    private $app;
    /**
     * @var string текущая версия Gnz11
     * @since 3.9
     */
    protected $VersionGnz11;

    /**
     * pro_criticalInstallerScript constructor.
     * @throws Exception
     * @since 3.9
     */
    public function __construct()
    {
        $this->app = Factory::getApplication();
    }

    /**
     * Метод установки расширения
     * $parent - это класс, вызывающий этот метод
     *
     * Method to install the extension
     * $parent is the class calling this method
     *
     * @return void
     * @since 3.9
     */
    function install($parent)
    {

        echo '<p>The module has been installed.</p>';
    }

    /**
     * Метод удаления расширения
     * $parent - это класс, вызывающий этот метод
     *
     * Method to uninstall the extension
     * $parent is the class calling this method
     *
     * @return void
     * @since 3.9
     */
    function uninstall($parent)
    {
        echo '<p>' . Text::_('UNINSTALL_TEXT') . '</p>';
    }

    /**
     * Метод обновления расширения
     * $parent - это класс, вызывающий этот метод
     *
     * Method to update the extension
     * $parent is the class calling this method
     *
     * @return void
     * @since 3.9
     */
    function update($parent)
    {
        echo '<p> ' . Text::_('UPDATE_TEXT') . $parent->get('manifest')->version . '.</p>';
    }

    /**
     * Метод, запускаемый перед методом install/update/uninstall method
     * $parent - это класс, вызывающий этот метод
     * $type - это тип изменения (установка, обновление или обнаружение_инсталляции)
     *
     * Method to run before an install/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     *
     * @return true|false
     * @throws Exception
     * @since 3.9
     */
    function preflight($typeExt, $parent)
    {
        // manifest file version
        $this->release = (string)$parent->get('manifest')->version;

        # Проверить версию Gnz11
        if( !$this->checkVersionGnz11($parent, true) )
        {
            $result = $this->InstallGnz11($parent);
            if( !$result )
            {
                return false;
            } #END IF
        } #END IF


        JLoader::registerNamespace('GNZ11', JPATH_LIBRARIES . '/GNZ11', $reset = false, $prepend = false, $type = 'psr4');
        \GNZ11\Extensions\ScriptFile::updateProcedure($typeExt, $parent);
        return true;
    }

    /**
     * Метод, запускаемый после метода install / update / uninstall
     * $parent - это класс, вызывающий этот метод
     * $type - это тип изменения (установка, обновление или discover_install)
     *
     * Method to run after an install/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     *
     * @return void
     * @since 3.9
     */
    function postflight($type, $parent)
    {
        //        echo '<p>Anything here happens after the installation/update/uninstallation of the module.</p>';
    }

    /**
     * Проверить версию библиотеки GNZ11
     * @param $parent
     * @param bool $mute - Скрывать сообщения
     * @return bool
     * @since 3.9
     */
    protected function checkVersionGnz11($parent, $mute = false)
    {
        $this->minimum_version_gnz11 = (string)$parent->get('manifest')->version_gnz11;
        $this->VersionGnz11 = $this->getVersionGnz11();

        if( version_compare($this->VersionGnz11, $this->minimum_version_gnz11, '<') )
        {
            if( !$mute )
            {
                $ErrorMsg = 'Необходимая минимальная версия библиотеи GNZ11 <b>' . $this->minimum_version_gnz11 . '</b>' . PHP_EOL;
                $ErrorMsg .= 'Установленная версия <b>' . $this->VersionGnz11 . '</b>';
            }#END IF


            # Выдать сообщение об ошибке и вернуть false
            # Throw some error message and return false
            $this->app->enqueueMessage($ErrorMsg, 'error');
            return false;
        }
        if( !$mute )
        {
            $mes = 'Версия библиотеки GNZ11 (' . $this->VersionGnz11 . ') <b style="color: #ff7600;">В актуальном состояни</b></b>' . PHP_EOL;
            $this->app->enqueueMessage($mes);
        }
        return true;
    }

    /**
     * Установить библиотеку GNZ11
     * @param $parent
     * @return bool     true в случае если установка удалась !
     * @throws Exception
     * @since 3.9
     */
    protected function InstallGnz11($parent)
    {
        # Скачать и установить расширение
        $result = $this->installDownload('Gnz11', self::Gnz11InstallUrl);
        if( $result )
        {
            $checkRes = $this->checkVersionGnz11($parent, true);
            $mes = 'Библиотека GNZ11 обновлена до актуальной версии <b>(' . $this->VersionGnz11 . ')</b> ';
            $this->app->enqueueMessage($mes);
            return $checkRes;
        }#END IF
        $this->app->enqueueMessage('Не удалось скачать и установить библиотеку GNZ11', 'error');
        return false;
    }

    /**
     * Получить версию library Gnz11
     * @return mixed
     * @since 3.9
     */
    protected function getVersionGnz11()
    {
        $manifest = $this->getItemArray(Factory::getDbo()->quote('GNZ11'));
        return $manifest['version'];
    }

    /**
     * Получить manifest_cache для установленного расшерения
     * @param $identifier
     * @return mixed
     * @since 3.9
     */
    protected function getItemArray($identifier)
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)->select($db->qn('manifest_cache'))->from($db->qn('#__extensions'))->where($db->qn('element') . ' = ' . $identifier);
        $db->setQuery($query);

        // Load the single cell and json_decode data
        return json_decode($db->loadResult(), true);
    }

    /**
     * Определяет, загружено ли указанное расширение PHP.
     * @link https://php.net/manual/en/function.extension-loaded.php
     * @param array $extensions extensions
     *
     * @return integer
     *
     * @throws Exception
     * @since version
     */
    protected function checkExtensions($extensions)
    {
        $app = Factory::getApplication();

        $pass = 1;

        foreach ($extensions as $name)
        {
            if( !extension_loaded($name) )
            {
                $pass = 0;
                $app->enqueueMessage(sprintf("Required PHP extension '%s' is missing. Please install it into your system.", $name), 'notice');
            }
        }

        return $pass;
    }

    /**
     * проверить установлер копонент или нет
     * @param $componentName
     * @since 3.9
     */
    protected function checkComponentIsInstalled($componentName)
    {
        # Checks if the component is enabled
        if( !ComponentHelper::isEnabled($componentName) )
        {
            echo $componentName . ' is either not enabled or not installed';
            //How to stop installation process and output an error here?
            return;
        }
        else
        {
            echo $componentName . ' is installed and enabled';
        }
    }

    /**
     * Скачать и установить расширение
     * @param string $id - имя разширения
     * @param string $url - Url для скачивания
     * @return bool|string
     * @throws Exception
     * @since 3.9
     */
    private function installDownload(string $id, string $url)
    {
        if( !self::checkTmpDir() )
        {
            return false;
        }#END IF

        $tmp_path = Factory::getApplication()->get('tmp_path');

        if( !is_string($url) )
        {
            return Text::_('NNEM_ERROR_NO_VALID_URL');
        }
        $target = $tmp_path . '/' . uniqid($id) . '.zip';

        jimport('joomla.filesystem.file');
        Factory::getLanguage()->load('com_installer', JPATH_ADMINISTRATOR);

        // Download the package at the URL given.
        $p_file = InstallerHelper::downloadPackage($url);

        // Was the package downloaded?
        if( !$p_file )
        {
            Factory::getApplication()->enqueueMessage('Не удалось скачать пакет установки', 'error');
            return false;
        }
        // Распакуй скачанный файл пакета.
        $package = InstallerHelper::unpack($tmp_path . '/' . $p_file, true);
        // Get an installer instance.
        $installer = new Installer();
        /*
         * Проверьте наличие основного пакета Joomla.
         * Для этого нам нужно указать исходный путь для поиска манифеста (тот же первый шаг, что и JInstaller :: install ())
         *
         * Это необходимо сделать перед распакованной проверкой, потому что JInstallerHelper :: detectType () возвращает логическое значение false, поскольку манифест
         * не может быть найден в ожидаемом месте.
		 */
        if( is_array($package) && isset($package['dir']) && is_dir($package['dir']) )
        {
            $installer->setPath('source', $package['dir']);
            if( !$installer->findManifest() )
            {
                # Если манифест не найден в источнике, это может быть пакет Joomla; проверьте каталог пакета для манифеста Joomla
                # If a manifest isn't found at the source, this may be a Joomla package; check the package directory for the Joomla manifest
                $this->app->enqueueMessage('Ошибка! Не удалось найти файл ианифест', 'warning');
                return false;
            }
        }
        if( !$package || !$package['type'] )
        {
            InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
            $this->app->enqueueMessage('Не удалось найти пакет установки', 'error');
            return false;
        }

        // Install the package.
        if( !$installer->install($package['dir']) )
        {
            // There was an error installing the package.
            $msg = Text::sprintf('COM_INSTALLER_INSTALL_ERROR', Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
            $result = false;
            $msgType = 'error';
        }
        else
        {
            // Package installed successfully.
            $msg = Text::sprintf('COM_INSTALLER_INSTALL_SUCCESS', Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
            $result = true;
            $msgType = 'message';
        }
        Factory::getApplication()->enqueueMessage($msg, $msgType);

        // Cleanup the install files.
        if( !is_file($package['packagefile']) )
        {
            $package['packagefile'] = $tmp_path . '/' . $package['packagefile'];
        }
        # Очистить временные файлы устанавливаемова пакета
        InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

        return $result;
    }

    /**
     * Проверка директории TMP
     * Проверить что директоря находится в корне сайта
     * @return bool
     * @throws \Exception
     * @since 3.9
     */
    private static function checkTmpDir()
    {
        $tmp_path = Factory::getApplication()->get('tmp_path');
        $tmp_pathLogic = JPATH_ROOT . '/tmp';
        if( \Joomla\CMS\Filesystem\Folder::exists($tmp_pathLogic) && $tmp_path != $tmp_pathLogic )
        {
            $mes = 'В настройках Joomla путь к директории TMP ведет не к той директории которая в корне сайта.';
            Factory::getApplication()->enqueueMessage($mes, 'warning');
            return true;
        }#END IF
        return true;
    }


}