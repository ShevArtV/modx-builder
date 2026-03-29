<?php

namespace ComponentBuilder;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ConfigManager
{
    private array $defaultConfig;
    public function __construct() {
        $this->defaultConfig = $this->getDefaultConfig();
    }

    public function getDefaultConfig(): array
    {
        $cwd = getcwd() . '/';

        return [
            'packages_path' => $cwd . 'package_builder/packages/',
            'core_path' => $cwd . 'core/components/',
            'assets_path' => $cwd . 'assets/components/',
            'templates_path' => dirname(__DIR__) . '/templates/',
        ];
    }

    public function loadPackageConfig(string $packageName): ?array
    {
        $configPath = $this->defaultConfig['packages_path'] . $packageName . '/config.php';

        if (!file_exists($configPath)) {
            return null;
        }

        $config = include $configPath;

        return is_array($config) ? $config : null;
    }   

    public function getTemplatesPath(): string
    {
        return $this->defaultConfig['templates_path'] . 'default/';
    }

    public function copyTemplates(string $destination): bool
    {
        $isLocalName = !str_contains($destination, '/') && !str_contains($destination, '\\');

        if ($isLocalName) {
            $destination = getcwd() . '/package_builder/templates/' . $destination;
        }

        $source = rtrim($this->defaultConfig['templates_path'] . 'default', '/');
        $destination = rtrim($destination, '/');

        if (!is_dir($source)) {
            return false;
        }

        FileSystem::recursiveCopy($source, $destination);

        return true;
    }

    public function resolveTemplatePath(string $template = ''): string
    {
        if (empty($template)) {
            return $this->defaultConfig['templates_path'] . 'default/';
        }

        $localPath = getcwd() . '/package_builder/templates/' . $template . '/';
        if (is_dir($localPath)) {
            return $localPath;
        }

        $builtinPath = $this->defaultConfig['templates_path'] . $template . '/';
        if (is_dir($builtinPath)) {
            return $builtinPath;
        }

        if (is_dir($template)) {
            return rtrim($template, '/') . '/';
        }

        return $this->defaultConfig['templates_path'] . 'default/';
    }

    public function createPackageTemplate(string $packageName, array $options = []): bool
    {
        try {
            $templatePath = $this->resolveTemplatePath($options['template'] ?? '');

            $this->createPackageStructure($packageName, $options, $templatePath);
            $this->createPackageConfig($packageName, $options, $templatePath);
            $this->createAssetsStructure($packageName, $options, $templatePath);

            return true;
        } catch (\Throwable $e) {
            error_log("Error creating package template: " . $e->getMessage());
            return false;
        }
    }   
    
    private function createPackageStructure(string $packageName, array $options, string $templatePath): void
    {
        $sourcePath = $templatePath . 'core/components';
        $targetPath = $this->defaultConfig['core_path'] . $packageName;
        
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }
        
        $this->recursiveCopyWithProcessing($sourcePath, $targetPath, $packageName, $options);
        
        if (!($options['generateElements'] ?? false)) {
            $elementsPath = $targetPath . '/elements';
            if (is_dir($elementsPath)) {
                FileSystem::removeDirectory($elementsPath);
            }
        }

        if (!($options['phpCsFixer'] ?? false)) {
            $file = $targetPath . '/.php-cs-fixer.dist.php';
            if (file_exists($file)) {
                unlink($file);
            }
        }

        if (!($options['eslint'] ?? false)) {
            foreach (['eslint.config.js', 'package.json'] as $f) {
                $file = $targetPath . '/' . $f;
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        if (!($options['tests'] ?? true)) {
            $testsPath = $targetPath . '/tests';
            if (is_dir($testsPath)) {
                FileSystem::removeDirectory($testsPath);
            }
            $phpunitXml = $targetPath . '/phpunit.xml';
            if (file_exists($phpunitXml)) {
                unlink($phpunitXml);
            }
        }

        $this->applyToolsConfig($targetPath, $options);
    }

    private function applyToolsConfig(string $targetPath, array $options): void
    {
        $toolsPath = $options['toolsConfigPath'] ?? '';

        if (empty($toolsPath) || !is_dir($toolsPath)) {
            return;
        }

        $toolsPath = rtrim($toolsPath, '/');

        $map = [
            'phpstan.neon' => 'phpstan.neon',
            '.php-cs-fixer.dist.php' => '.php-cs-fixer.dist.php',
            'eslint.config.js' => 'eslint.config.js',
            'package.json' => 'package.json',
        ];

        foreach ($map as $source => $target) {
            $sourcePath = $toolsPath . '/' . $source;
            $targetFile = $targetPath . '/' . $target;

            if (file_exists($sourcePath) && file_exists($targetFile)) {
                copy($sourcePath, $targetFile);
            }
        }
    }
    
    private function createAssetsStructure(string $packageName, array $options, string $templatePath): void
    {
        $sourcePath = $templatePath . 'assets/components';
        $targetPath = $this->defaultConfig['assets_path'] . $packageName;

        if (!is_dir($sourcePath)) {
            return;
        }

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        $this->recursiveCopyWithProcessing($sourcePath, $targetPath, $packageName, $options);
    }

    private function createPackageConfig(string $packageName, array $options, string $templatePath): void
    {
        $packagesTemplatePath = $templatePath . 'package_builder/packages';
        $packageConfigPath = $this->defaultConfig['packages_path'] . $packageName;
        
        if (!is_dir($packageConfigPath)) {
            mkdir($packageConfigPath, 0755, true);
        }
        
        $this->recursiveCopyWithProcessing($packagesTemplatePath, $packageConfigPath, $packageName, $options);
    }
    
    private function recursiveCopyWithProcessing(string $source, string $destination, string $packageName, array $options): void
    {
        if (!is_dir($source)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        $sourceLength = strlen($source);
        
        foreach ($iterator as $file) {
            $relativePath = substr($file->getPathname(), $sourceLength);
            $relativePath = ltrim($relativePath, '/\\');
            
            $relativePath = str_replace('{{package_name}}', $packageName, $relativePath);
            
            $targetPath = $destination . '/' . $relativePath;
            
            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                $fileName = basename($targetPath);
                $fileName = $this->replacePlaceholders($fileName, $packageName, $options);
                $targetPath = dirname($targetPath) . '/' . $fileName;
                
                if (str_ends_with($fileName, '.template')) {
                    $targetPath = str_replace('.template', '', $targetPath);
                    $this->processTemplateFile($file->getPathname(), $targetPath, $packageName, $options);
                } else {
                    $targetDir = dirname($targetPath);
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    copy($file->getPathname(), $targetPath);
                }
            }
        }
    }
    
    private function replacePlaceholders(string $text, string $packageName, array $options): string
    {
        $placeholders = $this->getPlaceholders($packageName, $options);
        
        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }
    
    private function processTemplateFile(string $sourcePath, string $targetPath, string $packageName, array $options): void
    {
        $content = $this->replacePlaceholders(file_get_contents($sourcePath), $packageName, $options);
        
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        file_put_contents($targetPath, $content);
    }
    
    private function toPascalCase(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
    }

    private function resolveTestUtilsPath(): string
    {
        return '../../../package_builder/test-utils';
    }

    private function getPlaceholders(string $packageName, ?array $options = []): array
    {
        return [
            '{{package_name}}' => strtolower($packageName),
            '{{Package_name}}' => $this->toPascalCase($packageName),
            '{{short_name}}' => $options['shortName'] ?? substr($packageName, 0, 3),
            '{{author_name}}' => $options['author'] ?? 'Your Name',
            '{{author_email}}' => $options['email'] ?? 'your-email@example.com',
            '{{php_version}}' => $options['phpVersion'] ?? '8.1',
            '{{gitlogin}}' => $options['gitlogin'] ?? 'your-username',
            '{{repository}}' => $options['repository'] ?? 'https://github.com/',
            '{{current_year}}' => date('Y'),
            '{{current_date}}' => date('Y-m-d'),
            '{{composer_require_dev_tests}}' => ($options['tests'] ?? true)
                ? "\"phpunit/phpunit\": \"^10.0|^11.0\",\n    \"modx/test-utils\": \"dev-main\",\n    "
                : '',
            '{{composer_require_dev_extra}}' => ($options['phpCsFixer'] ?? false)
                ? ",\n    \"friendsofphp/php-cs-fixer\": \"^3.0\""
                : '',
            '{{composer_autoload_dev}}' => ($options['tests'] ?? true)
                ? "\n  \"autoload-dev\": {\n    \"psr-4\": {\n      \"" . $this->toPascalCase($packageName) . "\\\\Tests\\\\\": \"tests/\"\n    }\n  },"
                : '',
            '{{composer_scripts_tests}}' => ($options['tests'] ?? true)
                ? "\"test\": \"phpunit\",\n    "
                : '',
            '{{composer_scripts_extra}}' => ($options['phpCsFixer'] ?? false)
                ? ",\n    \"cs-check\": \"php-cs-fixer fix --dry-run\",\n    \"cs-fix\": \"php-cs-fixer fix\""
                : '',
            '{{composer_repositories_tests}}' => ($options['tests'] ?? true)
                ? "\n  \"repositories\": [\n    {\n      \"type\": \"path\",\n      \"url\": \"" . $this->resolveTestUtilsPath() . "\"\n    }\n  ],"
                : '',
        ];
    }
}
