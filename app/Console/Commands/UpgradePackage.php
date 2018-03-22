<?php

namespace App\Console\Commands;

use App\Models\Repository;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Composer\Repository\Vcs\GitDriver;
use Illuminate\Console\Command;
use Illuminate\Support\ProcessUtils;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class UpgradePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satis:upgrade {repository-url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $repository = $this->getRepository($this->argument('repository-url'));

        if (!file_exists(storage_path('satis/satis.json'))) {
            $this->generateSatisConfig();
        }

        $php = ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));

        $command = "{$php} vendor/bin/satis build "
            . storage_path('satis/satis.json')
            . ' ' . public_path('packagist/')
            . ' ' . $repository->name;

        $process = new Process($command, app()->basePath());

        $process->run();
    }

    public function getRepository($url)
    {
        if (!$repository = Repository::byUrl($url)->first()) {
            if (!$packageName = $this->getRepositoryPackageNameIfComposerExists($url)) {
                return 0;
            }
            $repository = $this->storeRepositoryAndFlushSatisConfig($url, $packageName);
        }

        return $repository;
    }

    protected function getRepositoryPackageNameIfComposerExists($url)
    {
        $io = new ConsoleIO($this->input, $this->output, $this->getHelperSet());

        $gitRepository = new GitDriver(
            [
                'type' => 'git',
                'url' => $url,
            ],
            $io,
            Factory::createConfig($io)
        );

        $gitRepository->initialize();

        if (!$gitRepository->hasComposerFile($gitRepository->getRootIdentifier())) {
            return false;
        }

        $composerConfig = $gitRepository->getComposerInformation($gitRepository->getRootIdentifier());

        return $composerConfig['name'];
    }

    protected function storeRepositoryAndFlushSatisConfig($url, $packageName)
    {
        $repository = Repository::create([
            'url' => $url,
            'name' => $packageName,
        ]);

        $this->generateSatisConfig();

        return $repository;
    }

    protected function generateSatisConfig()
    {
        $repositories = Repository::all(['name', 'url']);

        $config = [
            'name' => config('satis.name'),
            'homepage' => config('satis.homepage'),
        ];

        foreach ($repositories as $repository) {
            $config['repositories'][] = [
                'type' => 'git',
                'url' => $repository->url,
            ];
            $config['require'][$repository['name']] = '*';
        }

        file_put_contents(
            storage_path('satis/satis.json'),
            json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
