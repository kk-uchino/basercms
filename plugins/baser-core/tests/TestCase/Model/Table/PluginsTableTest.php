<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) baserCMS User Community <https://basercms.net/community/>
 *
 * @copyright     Copyright (c) baserCMS User Community
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.0
 * @license       http://basercms.net/license/index.html MIT License
 */

namespace BaserCore\Test\TestCase\Model\Table;

use BaserCore\Model\Table\PluginsTable;
use BaserCore\TestSuite\BcTestCase;
use Cake\Core\App;
use Cake\Filesystem\Folder;

/**
 * Class PluginsTableTest
 * @package BaserCore\Test\TestCase\Model\Table
 * @property PluginsTable $Plugins
 */
class PluginsTableTest extends BcTestCase
{

    /**
     * @var PluginsTable
     */
    public $Plugins;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.BaserCore.Plugins',
    ];

    /**
     * Set Up
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Plugins')? [] : ['className' => 'BaserCore\Model\Table\PluginsTable'];
        $this->Plugins = $this->getTableLocator()->get('Plugins', $config);
    }

    /**
     * Tear Down
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Plugins);
        parent::tearDown();
    }

    /**
     * testGetAvailable
     */
    public function testGetAvailable()
    {
        $plugins = $this->Plugins->getAvailable();
        $this->assertEquals(4, count($plugins));

        $pluginPath = App::path('plugins')[0] . DS . 'BcTest';
        $folder = new Folder($pluginPath);
        $folder->create($pluginPath, 0777);

        $plugins = $this->Plugins->getAvailable();
        $this->assertEquals(5, count($plugins));

        $folder->delete($pluginPath);
    }

    /**
     * testGetPluginConfig
     */
    public function testGetPluginConfig()
    {
        $plugin = $this->Plugins->getPluginConfig('BaserCore');
        $this->assertEquals('BaserCore', $plugin->name);
    }

    /**
     * testIsInstallable
     */
    public function testIsInstallable()
    {
        $this->expectExceptionMessage('既にインストール済のプラグインです。');
        $this->Plugins->isInstallable('BcBlog');
        $this->expectExceptionMessage('インストールしようとしているプラグインのフォルダが存在しません。');
        $this->Plugins->isInstallable('BcTest');
        $pluginPath = App::path('plugins')[0] . DS . 'BcTest';
        $folder = new Folder($pluginPath);
        $folder->create($pluginPath, 0777);
        $this->assertEquals(true, $this->Plugins->isInstallable('BcTest'));
        $folder->delete($pluginPath);
    }

    /**
     * testInstall
     */
    public function testInstall()
    {
        $this->Plugins->install('BcTest');
        $plugin = $this->Plugins->find()->where(['name' => 'BcTest'])->first();
        $this->assertEquals(2, $plugin->priority);
    }

}
