<?php

/*
 * This file is part of the Composer NPM bridge package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Composer\NpmBridge;

use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Eloquent\Phony\Phpunit\Phony;
use PHPUnit_Framework_TestCase;

class NpmBridgePluginTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->bridgeFactory = Phony::mock('Eloquent\Composer\NpmBridge\NpmBridgeFactoryInterface');
        $this->plugin = new NpmBridgePlugin($this->bridgeFactory->mock());

        $this->bridge = Phony::mock('Eloquent\Composer\NpmBridge\NpmBridgeInterface');
        $this->composer = new Composer();
        $this->io = new NullIO();

        $this->bridgeFactory->create('*')->returns($this->bridge);
    }

    public function testConstructor()
    {
        $this->assertSame($this->bridgeFactory->mock(), $this->plugin->bridgeFactory());
    }

    public function testConstructorDefaults()
    {
        $this->plugin = new NpmBridgePlugin();

        $this->assertEquals(new NpmBridgeFactory(), $this->plugin->bridgeFactory());
    }

    public function testActivate()
    {
        $this->assertNull($this->plugin->activate($this->composer, $this->io));
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                ScriptEvents::POST_INSTALL_CMD => 'onPostInstallCmd',
                ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
            ),
            $this->plugin->getSubscribedEvents()
        );
    }

    public function testOnPostInstallCmd()
    {
        $this->plugin->onPostInstallCmd(new Event(ScriptEvents::POST_INSTALL_CMD, $this->composer, $this->io, true));

        Phony::inOrder(
            $this->bridgeFactory->create->calledWith($this->io),
            $this->bridge->install->calledWith($this->composer, true)
        );
    }

    public function testOnPostInstallCmdProductionMode()
    {
        $this->plugin->onPostInstallCmd(new Event(ScriptEvents::POST_INSTALL_CMD, $this->composer, $this->io, false));

        Phony::inOrder(
            $this->bridgeFactory->create->calledWith($this->io),
            $this->bridge->install->calledWith($this->composer, false)
        );
    }

    public function testOnPostUpdateCmd()
    {
        $this->plugin->onPostUpdateCmd(new Event(ScriptEvents::POST_UPDATE_CMD, $this->composer, $this->io));

        Phony::inOrder(
            $this->bridgeFactory->create->calledWith($this->io),
            $this->bridge->update->calledWith($this->composer)
        );
    }
}
