<?php

namespace Bojaghi\VersionSync\Tests;

use Bojaghi\VersionSync\VersionSync;
use PHPUnit\Framework\TestCase;

class TestVersionSync extends TestCase
{
    /**
     * @throws \Exception
     */
    public function test_plugin_1(): void
    {
        // Create fake root.
        $root         = __DIR__ . '/fixtures/test-plugin-1';
        $fakeRoot     = __DIR__ . '/fixtures/test-plugin-test';
        $fakeMain     = $fakeRoot . '/test-plugin-1.php';
        $fakeComposer = $fakeRoot . '/composer.json';
        $fakePackage  = $fakeRoot . '/package.json';
        if (!file_exists($fakeRoot)) {
            mkdir($fakeRoot);
        }
        copy($root . '/test-plugin-1.php', $fakeMain);
        copy($root . '/composer.json', $fakeComposer);
        copy($root . '/package.json', $fakePackage);

        $content = file_get_contents($fakeMain);
        $content = str_replace('Version: 1.0.0', 'Version: 1.1.1', $content);
        file_put_contents($fakeMain, $content);

        $sync = new VersionSync($fakeRoot);
        $sync->run();

        // Check version strings are changed.
        $content = file_get_contents($fakeMain);
        $this->assertStringContainsString("const TEST_PLUGIN_1_CONST_VER = '1.1.1';", $content);
        $this->assertStringContainsString("define( 'TEST_PLUGIN_1_DEFINE_VER', '1.1.1' );", $content);

        // composer.json
        $content = file_get_contents($fakeComposer);
        $this->assertStringContainsString('"version": "1.1.1"', $content);

        // package.json
        $content = file_get_contents($fakePackage);
        $this->assertStringContainsString('"version": "1.1.1"', $content);

        @unlink($fakeMain);
        @unlink($fakeComposer);
        @unlink($fakePackage);
        rmdir($fakeRoot);
    }
}
