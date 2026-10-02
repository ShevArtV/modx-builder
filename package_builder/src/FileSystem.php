<?php

declare(strict_types=1);

namespace ComponentBuilder;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class FileSystem
{
    /** Пути через '/': Windows их понимает, а zip-архив и PHP-конфиги требуют. */
    public static function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Домашняя папка пользователя: HOME (Linux/macOS), USERPROFILE (Windows).
     * getenv() возвращает false, а не null, поэтому цепочка `??` здесь не работает.
     */
    public static function homeDir(): string
    {
        foreach ([$_SERVER['HOME'] ?? '', getenv('HOME'), getenv('USERPROFILE')] as $dir) {
            if (is_string($dir) && $dir !== '') {
                return rtrim(self::normalizePath($dir), '/');
            }
        }

        $drive = getenv('HOMEDRIVE');
        $path = getenv('HOMEPATH');
        if ($drive && $path) {
            return rtrim(self::normalizePath($drive . $path), '/');
        }

        throw new \RuntimeException('Cannot determine home directory: set HOME or USERPROFILE.');
    }

    public static function recursiveCopy(string $source, string $destination): void
    {
        if (!is_dir($source)) {
            return;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $sourceLen = strlen($source) + 1;

        foreach ($iterator as $file) {
            $target = $destination . '/' . substr($file->getPathname(), $sourceLen);

            if ($file->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                $targetDir = dirname($target);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                copy($file->getPathname(), $target);
            }
        }
    }

    public static function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }

    public static function resolveFileContent(string $contentValue, string $basePath, ?string &$missingFile = null): string
    {
        if (str_starts_with($contentValue, 'file:')) {
            $filePath = $basePath . substr($contentValue, 5);
            if (!file_exists($filePath)) {
                $missingFile = $filePath;
                return '';
            }

            $raw = file_get_contents($filePath);
            return $raw !== false ? trim($raw) : '';
        }

        return $contentValue;
    }

    public static function resolveStaticFilePath(string $contentValue): string
    {
        if (str_starts_with($contentValue, 'file:')) {
            return substr($contentValue, 5);
        }

        return '';
    }

    public static function normalizePhpElementContent(string $content): string
    {
        $content = ltrim($content, "\xEF\xBB\xBF");
        $content = preg_replace('/^\s*<\?php\b\s*/i', '', $content) ?? $content;
        $content = preg_replace('/\s*\?>\s*$/', '', $content) ?? $content;

        return trim($content);
    }
}
