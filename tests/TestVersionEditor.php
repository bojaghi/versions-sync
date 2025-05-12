<?php

namespace Bojaghi\VersionSync\Tests;

use Bojaghi\VersionSync\MainFileVersionDetector;
use Bojaghi\VersionSync\VersionEditor;
use PHPUnit\Framework\TestCase;

class TestVersionEditor extends TestCase
{
    public function test_PHPHeader(): void
    {
        $detector = new MainFileVersionDetector(__DIR__ . '/fixtures/test-plugin-1');
        $detector->detect();

        $path    = $detector->getPath();
        $version = $detector->getVersion();
        $this->assertEquals('1.0.0', $version[0]);

        $editor = new VersionEditor();
        $editor->load($path);
        $editor->editPhpHeader('1.1.1', $version);

        // Header string
        $content = $editor->getContent();
        $this->assertStringNotContainsString('Version: 1.0.0', $content);
        $this->assertStringContainsString('Version: 1.1.1', $content);
    }

    public function test_PhpConstant_Plugin(): void
    {
        $detector = new MainFileVersionDetector(__DIR__ . '/fixtures/test-plugin-1');
        $detector->detect();

        $path    = $detector->getPath();
        $version = $detector->getVersion();
        $this->assertEquals('1.0.0', $version[0]);

        $editor = new VersionEditor();
        $editor->load($path);
        $editor->editPHPConstant('1.1.1', 'TEST_PLUGIN_1_CONST_VER');
        $editor->editPHPConstant('1.1.1', 'TEST_PLUGIN_1_DEFINE_VER');

        $content = $editor->getContent();
        $this->assertStringNotContainsString("const TEST_PLUGIN_1_CONST_VER = '1.0.0';", $content);
        $this->assertStringContainsString("const TEST_PLUGIN_1_CONST_VER = '1.1.1';", $content);

        $this->assertStringNotContainsString("define( 'TEST_PLUGIN_1_DEFINE_VER', '1.0.0' );", $content);
        $this->assertStringContainsString("define( 'TEST_PLUGIN_1_DEFINE_VER', '1.1.1' );", $content);
    }
}
