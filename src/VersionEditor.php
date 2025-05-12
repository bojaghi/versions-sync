<?php

namespace Bojaghi\VersionSync;

class VersionEditor
{
    private string $path;
    private string $content;

    public function __construct()
    {
        $this->path    = '';
        $this->content = '';
    }

    public function load($path): void
    {
        $this->path    = $path;
        $this->content = file_get_contents($this->path) ?: '';
    }

    public function save(): void
    {
        file_put_contents($this->path, $this->content);
        $this->path    = '';
        $this->content = '';
    }

    public function editPhpHeader(string $newVersion, array $versionInfo): void
    {
        [$oldVersion, $offset] = $versionInfo;

        $this->content = substr_replace($this->content, $newVersion, $offset, strlen($oldVersion));
    }

    public function editPHPConstant(string $newVersion, string $constant): void
    {
        $const = preg_quote($constant, '/');
        $ver   = '([0-9]+)\.([0-9]+)\.([0-9]+)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?';

        $expr = "/const\s+$const\s*=\s*[\"']{$ver}[\"']\s*;/";

        $this->content = preg_replace(
            $expr,
            "const $constant = '$newVersion';",
            $this->content,
            1
        );

        $expr = "/define\s*\(\s*[\"']{$const}[\"']\s*,\s*[\"']{$ver}[\"']\s*\)\s*;/";

        $this->content = preg_replace(
            $expr,
            "define( '$constant', '$newVersion' );",
            $this->content,
            1
        );
    }

    public function editJson(string $newVersion): void
    {
        $object          = json_decode($this->content);
        $object->version = $newVersion;
        $this->content   = json_encode($object, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
