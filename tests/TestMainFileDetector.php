<?php

namespace Bojaghi\VersionSync\Tests;

use Bojaghi\VersionSync\MainFileVersionDetector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TestMainFileDetector extends TestCase
{
    #[DataProvider('detector_dataProvider')]
    public function test_detector(string $root, string $path, string $version)
    {
        $detector = new MainFileVersionDetector($root);
        $detector->detect();

        $this->assertEquals($path, $detector->getPath());
        $this->assertEquals($version, $detector->getVersion()[0]);
    }

    public static function detector_dataProvider(): array
    {
        $fixtures = __DIR__ . '/fixtures';

        return [
            [
                'root'    => "$fixtures/test-plugin-1",
                'path'    => "$fixtures/test-plugin-1/test-plugin-1.php",
                'version' => '1.0.0'
            ],
            [
                'root'    => "$fixtures/test-plugin-2",
                'path'    => "$fixtures/test-plugin-2/test-plugin-2.php",
                'version' => ''
            ],
            [
                'root'    => "$fixtures/test-theme-1",
                'path'    => "$fixtures/test-theme-1/style.css",
                'version' => '0.1.0'
            ],
            [
                'root'    => "$fixtures/test-theme-2",
                'path'    => "$fixtures/test-theme-2/res/style.css",
                'version' => '0.2.0'
            ],

        ];
    }
}