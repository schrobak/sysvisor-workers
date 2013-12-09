<?php

namespace Sysvisor\Worker\PulseWorker\Tests\Config;

use Sysvisor\Worker\PulseWorker\Config\PulseConfig;

class PulseConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function configureWithValidData()
    {
        $config = new PulseConfig(array(
            'username' => 'user',
            'password' => 'pass',
            'url' => 'hostname',
            'namespace' => 'ns'
        ));

        $this->assertTrue(isset($config['username']));
        $this->assertSame('user', $config['username']);
        $this->assertTrue(isset($config['password']));
        $this->assertSame('pass', $config['password']);
        $this->assertTrue(isset($config['url']));
        $this->assertSame('hostname', $config['url']);
        $this->assertTrue(isset($config['namespace']));
        $this->assertSame('ns', $config['namespace']);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @dataProvider configureWithMissingRequiredKeyDataProvider
     */
    public function configureWithMissingRequiredKey($options)
    {
        new PulseConfig($options);

        $this->fail('Required option is missing');
    }

    public static function configureWithMissingRequiredKeyDataProvider()
    {
        return array(
            array(
                array(
                    'password' => null,
                    'url' => null,
                )
            ),
            array(
                array(
                    'username' => null,
                    'url' => null
                )
            ),
            array(
                array(
                    'username' => null,
                    'password' => null,
                )
            ),
        );
    }
}
 