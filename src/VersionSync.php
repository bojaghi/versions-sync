<?php

namespace Bojaghi\VersionSync;

use Exception;

class VersionSync
{
    /**
     * Inspection files
     *
     * @var array
     */
    public array $files;

    /**
     * PHP version constant name
     *
     * @var string
     */
    public string $constant;

    private MainFileVersionDetector $detector;

    /**
     * @throws Exception
     */
    public function __construct(string $root = '')
    {
        if (empty($root)) {
            $root = realpath(getcwd());
            if (!$root) {
                throw new Exception('Path not found.', 1);
            }
        }

        $this->detector = new MainFileVersionDetector($root);
        $this->detector->detect();

        $mainFile = $this->detector->getPath();
        if (!$mainFile) {
            throw new Exception('Main file not found.', 1);
        }

        // Get extra configuration.
        $composer = $root . '/composer.json';
        if (file_exists($composer)) {
            $content = json_decode(file_get_contents($composer), true);
        } else {
            $content = [];
        }

        if ($content) {
            $default = self::getDefault();
            $extra   = $content['extra']['version-sync'] ?? [];
            $conf    = array_merge($default, $extra);
        } else {
            $conf = self::getDefault();
        }

        // Set files property.
        $this->files = [$mainFile, $composer, $root . '/package.json',];

        foreach ((array)$conf['files'] as $file) {
            if (str_starts_with($file, '/')) {
                $this->files[] = realpath($file);
            } else {
                $this->files[] = realpath($root . '/' . $file);
            }
        }

        $this->files = array_unique(
            array_filter(
                $this->files,
                fn($file) => $file && file_exists($file) && is_readable($file) && is_writable($file) && is_file($file)
            )
        );

        // Set constant property.
        $this->constant = $conf['constant'] ?? '';
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        $editor = new VersionEditor();

        /** 0th: version string, 1st: offset */
        [$version] = $this->detector->getVersion();

        foreach ($this->files as $file) {
            $editor->load($file);

            if (str_ends_with($file, '.php') && $this->constant) {
                $editor->editPHPConstant($version, $this->constant);
            }

            if (str_ends_with($file, '.json')) {
                $editor->editJson($version);
            }

            $editor->save();
        }
    }

    public static function getDefault(): array
    {
        return [
            'files' => [],
            'constant' => '',
        ];
    }
}
