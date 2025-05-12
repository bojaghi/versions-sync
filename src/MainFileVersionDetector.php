<?php

namespace Bojaghi\VersionSync;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use SplFileInfo;

class MainFileVersionDetector implements Detector
{
    public string $path;

    public array $version;

    public function __construct(private string $root)
    {
        $this->path    = '';
        $this->version = [];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getVersion(): array
    {
        return $this->version;
    }

    public function detect(): self
    {
        $detected = $this->maybeDetectPHP();

        if (!$detected) {
            $detected = $this->maybeDetectCSS();
        }

        if ($detected) {
            $this->path    = $detected['path'];
            $this->version = $detected['version'];
        }

        return $this;
    }

    private function maybeDetectPHP(): array
    {
        foreach (glob("$this->root/*.php") as $path) {
            $detected = $this->readFileHeader($path);
            if (isset($detected['Plugin Name'])) {
                return [
                    'path'    => $path,
                    'version' => $detected['Version'] ?? ['', -1],
                ];
            }
        }

        return [];
    }

    private function maybeDetectCSS(): array
    {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root)),
            '/\.css$/i',
            RegexIterator::MATCH
        );

        foreach ($iterator as $item) {
            /** @var SplFileInfo $item */
            $path     = $item->getRealPath();
            $detected = $this->readFileHeader($path);

            if (isset($detected['Theme Name'])) {
                return [
                    'path'    => $path,
                    'version' => $detected['Version'] ?? ['', -1],
                ];
            }
        }

        return [];
    }

    /**
     * Read header information from the file
     *
     * This method is copy of get_file_data(), wp-includes/functions.php.
     *
     * @param string $path
     *
     * @return array
     */
    private function readFileHeader(string $path): array
    {
        $found = [];

        $content = file_get_contents($path, false, null, 0, 8192) ?: '';
        $content = str_replace("\r", "\n", $content);

        foreach (['Theme Name', 'Plugin Name', 'Version'] as $expr) {
            if (
                preg_match(
                    '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote($expr, '/') . ':(.*)$/mi',
                    $content,
                    $match,
                    PREG_OFFSET_CAPTURE
                )
                && $match[1][0]
            ) {
                $value  = self::cleanupHeaderComment($match[1][0]);
                $offset = $match[1][1];

                // Preserve whitespace between colon and version string.
                $offset = strpos($content, $value, $offset);

                $found[$expr] = [$value, $offset];
            }
        }

        return $found;
    }

    private static function cleanupHeaderComment(string $input): string
    {
        return trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $input));
    }
}