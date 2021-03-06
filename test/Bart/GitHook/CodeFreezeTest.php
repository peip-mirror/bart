<?php
namespace Bart\GitHook;

use Bart\BaseTestCase;
use Bart\Git\Commit;
use Bart\Git\GitRoot;
use PhpOption\Option;

class CodeFreezeTest extends BaseTestCase
{
    private $head;

    public function setUp()
    {
        parent::setUp();

        putenv('GITOSIS_USER=');
        $this->head = new Commit(new GitRoot(), 'abcde123', 'bart.git');
    }

    public function testWhenSuperUser()
    {
        $this->shmockAndDieselify('\Bart\GitHook\GitHookSystemConfig', function($config) {
            $config->frozenRepoNames()->once()->return_value(['all']);
            $config->envVarNameForPushUser()->once()->return_value(
                Option::fromValue('GITOSIS_USER'));
            $config->superUserNames()->once()->return_value(['braynard']);
        }, true);

        // simulate push from John
        putenv('GITOSIS_USER=braynard');

        $freeze = new CodeFreeze();
        $freeze->run($this->head);
    }

    /**
     * @expectedException \Bart\GitHook\GitHookException
     * @expectedExceptionMessage All repositories are frozen
     */
    public function testWhenAllFrozenAndEnvNotSet()
    {
        $this->shmockAndDieselify('\Bart\GitHook\GitHookSystemConfig', function($config) {
            $config->frozenRepoNames()->once()->return_value(['all']);
            $config->envVarNameForPushUser()->once()->return_value(
                Option::fromValue('GITOSIS_USER'));
            $config->superUserNames()->once()->return_value(['braynard']);
        }, true);

        // Note there is no variable set for ENV
        $freeze = new CodeFreeze();
        $freeze->run($this->head);
    }

    public function testWhenNotFrozenRepoAndNoEnvVarEither()
    {
        $this->shmockAndDieselify('\Bart\GitHook\GitHookSystemConfig', function($config) {
            $config->frozenRepoNames()->once()->return_value(['example.git']);
            $config->envVarNameForPushUser()->once()->return_value(
                Option::fromValue(null));
            // This should not be called because env var name is None
            $config->superUserNames()->never();
        }, true);

        $freeze = new CodeFreeze();
        $freeze->run($this->head);
    }

    /**
     * @expectedException \Bart\GitHook\GitHookException
     * @expectedExceptionMessage bart.git repository is frozen
     */
    public function testWhenBartFrozenRepoAndNoEnvVar()
    {
        $this->shmockAndDieselify('\Bart\GitHook\GitHookSystemConfig', function($config) {
            $config->envVarNameForPushUser()->once()->return_value(
                Option::fromValue(null));
            // This should not be called because env var name is None
            $config->superUserNames()->never();
            $config->frozenRepoNames()->once()->return_value(['example', 'bart.git']);
        }, true);

        $freeze = new CodeFreeze();
        $freeze->run($this->head);
    }

    /**
     * @expectedException \Bart\GitHook\GitHookException
     * @expectedExceptionMessage All repositories are frozen
     */
    public function testWhenAllFrozenRepoAndNoEnvVarConfigured()
    {
        $this->shmockAndDieselify('\Bart\GitHook\GitHookSystemConfig', function($config) {
            $config->envVarNameForPushUser()->once()->return_value(
                Option::fromValue(null));
            // This should not be called because env var name is None
            $config->superUserNames()->never();
            $config->frozenRepoNames()->once()->return_value(['all']);
        }, true);

        $freeze = new CodeFreeze();
        $freeze->run($this->head);
    }
}
