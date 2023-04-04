<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests;

use Composer\Composer;
use Composer\Config;
use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Factory;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\BufferIO;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginManager;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryInterface;
use Composer\Script\ScriptEvents;
use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\ChangelogsPlugin;

class ChangelogsPluginTest extends TestCase
{
    private BufferIO $io;

    private Composer $composer;

    private Config $config;

    private string $tempDir;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->tempDir = __DIR__ . '/temp';
        $baseDir = realpath(__DIR__ . '/fixtures/local') ? realpath(__DIR__ . '/fixtures/local') : null;
        $this->config = new Config(false, $baseDir);
        $this->config->merge([
            'config' => [
                'home' => __DIR__,
            ],
        ]);

        $this->io = new BufferIO();

        $this->composer = Factory::create($this->io, $this->config->raw()['config']);
        /* $this->composer = new Composer(); */
        $this->composer->setConfig($this->config);
        $this->composer->setPackage(new RootPackage('my/project', '1.0.0', '1.0.0'));
//        $this->composer->setLocker(new Locker($this->io,));
        $this->composer->setPluginManager(new PluginManager($this->io, $this->composer));
        $this->composer->setEventDispatcher(new EventDispatcher($this->composer, $this->io));

        self::cleanTempDir();
        mkdir($this->tempDir);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        self::cleanTempDir();
    }

    /**
     * Completely remove the temp dir and its content if it exists.
     */
    private function cleanTempDir(): void
    {
        if (!is_dir($this->tempDir)) {
            return;
        }
        $files = glob($this->tempDir . '/*');
        if (is_array($files)) {
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->tempDir);
        }
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function testItIsRegisteredAndActivated(): void
    {
        $plugin = new ChangelogsPlugin();

        $this->addComposerPlugin($plugin);

        $this->assertSame([$plugin], $this->composer->getPluginManager()->getPlugins());
    }

    /**
     * @test
     *
     * @throws \ReflectionException
     */
    public function testItReceivesEvent(): void
    {
        $this->addComposerPlugin(new ChangelogsPlugin());

        $operation = $this->getUpdateOperation();

        $this->dispatchPostPackageUpdateEvent($operation);

        $this->composer->getEventDispatcher()->dispatchScript(ScriptEvents::POST_UPDATE_CMD);

        $expectedOutput = <<<OUTPUT
Changelogs summary:

 - foo/bar updated from v1.0.0 to v1.0.1 patch
   See changes: https://github.com/foo/bar/compare/v1.0.0...v1.0.1
   Release notes: https://github.com/foo/bar/releases/tag/v1.0.1


OUTPUT;

        $this->assertSame($expectedOutput, $this->io->getOutput());
    }

    /**
     * @test
     */
    public function testEventsAreHandled(): void
    {
        $plugin = new ChangelogsPlugin();
        $plugin->activate($this->composer, $this->io);

        $operation = $this->getUpdateOperation();

        $packageEvent = $this->createPostPackageUpdateEvent($operation);

        $plugin->postPackageOperation($packageEvent);

        $plugin->postUpdate();

        $expectedOutput = <<<OUTPUT
Changelogs summary:

 - foo/bar updated from v1.0.0 to v1.0.1 patch
   See changes: https://github.com/foo/bar/compare/v1.0.0...v1.0.1
   Release notes: https://github.com/foo/bar/releases/tag/v1.0.1


OUTPUT;

        $this->assertSame($expectedOutput, $this->io->getOutput());
    }

    /**
     * @test
     */
    public function testItWriteTextSummaryFile(): void
    {
        $this->config->merge([
            'config' => [
                'home' => realpath(__DIR__ . '/fixtures/write-summary-file'),
            ],
        ]);

        $plugin = new ChangelogsPlugin();

        $plugin->activate($this->composer, $this->io);

        $operation = $this->getUpdateOperation();

        $packageEvent = $this->createPostPackageUpdateEvent($operation);

        $plugin->postPackageOperation($packageEvent);

        $plugin->postUpdate();

        $this->assertFileExists($this->tempDir . '/changelogs-' . date('Y-m-d') . '.txt');
        $fileContent = file_get_contents($this->tempDir . '/changelogs-' . date('Y-m-d') . '.txt');
        $expectedContent = 'Changelogs summary:

 - foo/bar updated from v1.0.0 to v1.0.1 patch
   See changes: https://github.com/foo/bar/compare/v1.0.0...v1.0.1
   Release notes: https://github.com/foo/bar/releases/tag/v1.0.1
';
        $this->assertStringMatchesFormat($expectedContent, $fileContent);
    }

    /**
     * @test
     */
    public function testItWriteJsonSummaryFile(): void
    {
        $this->config->merge([
            'config' => [
                'home' => realpath(__DIR__ . '/fixtures/write-json-summary-file'),
            ],
        ]);

        $plugin = new ChangelogsPlugin();

        $plugin->activate($this->composer, $this->io);

        $operation = $this->getUpdateOperation();

        $packageEvent = $this->createPostPackageUpdateEvent($operation);

        $plugin->postPackageOperation($packageEvent);

        $plugin->postUpdate();

        $this->assertFileExists($this->tempDir . '/changelogs-' . date('Y-m-d') . '.json');
        $fileContent = file_get_contents($this->tempDir . '/changelogs-' . date('Y-m-d') . '.json');
        $expectedContent = '[{"operation":"update","package":"foo/bar","action":"updated","phrasing":"updated from","versionFrom":"v1.0.0","versionTo":"v1.0.1","semver":"patch","changesUrl":"https://github.com/foo/bar/compare/v1.0.0...v1.0.1","releaseUrl":"https://github.com/foo/bar/releases/tag/v1.0.1"}]';
        $this->assertStringMatchesFormat($expectedContent, $fileContent);
    }

    /**
     * @param PluginInterface $plugin
     *
     * @throws \ReflectionException
     *
     * @return void
     */
    private function addComposerPlugin(PluginInterface $plugin): void
    {
        $pluginManagerReflection = new \ReflectionClass($this->composer->getPluginManager());
        $addPluginReflection = $pluginManagerReflection->getMethod('addPlugin');
        $addPluginReflection->setAccessible(true);
        $addPluginReflection->invoke($this->composer->getPluginManager(), $plugin);
    }

    private function getUpdateOperation(): UpdateOperation
    {
        $initialPackage = new Package('foo/bar', '1.0.0.0', 'v1.0.0');
        $initialPackage->setSourceUrl('https://github.com/foo/bar.git');

        $targetPackage = new Package('foo/bar', '1.0.1.0', 'v1.0.1');
        $targetPackage->setSourceUrl('https://github.com/foo/bar.git');

        return new UpdateOperation($initialPackage, $targetPackage);
    }

    private function createPostPackageUpdateEvent($operation): PackageEvent
    {
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0.0') >= 0) {
            return new PackageEvent(
                PackageEvents::POST_PACKAGE_UPDATE,
                $this->composer,
                $this->io,
                false,
                $this->createMock(RepositoryInterface::class),
                [$operation],
                $operation
            );
        }

        return new PackageEvent(
            PackageEvents::POST_PACKAGE_UPDATE,
            $this->composer,
            $this->io,
            false,
            new DefaultPolicy(false, false),
            new Pool(),
            new CompositeRepository([]),
            new Request(new Pool()),
            [$operation],
            $operation
        );
    }

    /**
     * @param $operation
     *
     * @return void
     */
    private function dispatchPostPackageUpdateEvent($operation): void
    {
        if (version_compare(PluginInterface::PLUGIN_API_VERSION, '2.0.0') >= 0) {
            $this->composer->getEventDispatcher()->dispatchPackageEvent(
                PackageEvents::POST_PACKAGE_UPDATE,
                false,
                $this->createMock(RepositoryInterface::class),
                [$operation],
                $operation
            );

            return;
        }

        $this->composer->getEventDispatcher()->dispatchPackageEvent(
            PackageEvents::POST_PACKAGE_UPDATE,
            false,
            new DefaultPolicy(false, false),
            new Pool(),
            new CompositeRepository([]),
            new Request(new Pool()),
            [$operation],
            $operation
        );
    }
}
