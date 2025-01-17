<?php

namespace Hypernode\Deploy\Deployer\Task\PlatformConfiguration;

use Deployer\Task\Task;
use Hypernode\Deploy\Deployer\Task\ConfigurableTaskInterface;
use Hypernode\Deploy\Deployer\Task\IncrementedTaskTrait;
use Hypernode\Deploy\Deployer\Task\TaskBase;
use Hypernode\DeployConfiguration\PlatformConfiguration\SupervisorConfiguration;
use Hypernode\DeployConfiguration\TaskConfigurationInterface;

use function Deployer\after;
use function Deployer\get;
use function Deployer\set;
use function Deployer\task;
use function Hypernode\Deploy\Deployer\before;

class SupervisorTask extends TaskBase implements ConfigurableTaskInterface
{
    use IncrementedTaskTrait;

    protected function getIncrementalNamePrefix(): string
    {
        return 'deploy:configuration:supervisor:';
    }

    public function supports(TaskConfigurationInterface $config): bool
    {
        return $config instanceof SupervisorConfiguration;
    }

    public function configureWithTaskConfig(TaskConfigurationInterface $config): ?Task
    {
        set('supervisor/config_path', function () {
            return '/tmp/supervisor-config-' . get('domain');
        });

        $task = task('deploy:supervisor', [
            'deploy:supervisor:prepare',
            'deploy:supervisor:upload',
            'deploy:supervisor:sync',
            'deploy:supervisor:cleanup',
        ]);

        before('deploy:symlink', 'deploy:supervisor');
        foreach ($this->getRegisteredTasks() as $taskName) {
            after('deploy:supervisor:prepare', $taskName);
        }

        return $task;
    }
}
