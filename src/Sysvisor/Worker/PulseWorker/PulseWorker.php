<?php

namespace Sysvisor\Worker\PulseWorker;

use Sysvisor\Worker\AbstractWorker;
use Sysvisor\Worker\PulseWorker\Config\PulseConfig;
use Zend\Uri\Exception\InvalidUriException;
use Zend\Uri\Uri;
use Zend\XmlRpc\Client;
use Psr\Log\LogLevel;

class PulseWorker extends AbstractWorker
{
    /**
     * @var PulseConfig
     */
    private $config;

    /**
     * @var string
     */
    private $timestamp = '';

    public function __construct($uri)
    {
        $this->config = $this->parseUri($uri);
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    protected function execute()
    {
        try {
            $server = new Client($this->config['url']);
            $proxy = $server->getProxy($this->config['namespace']);

            $token = $proxy->RemoteApi->login($this->config['username'], $this->config['password']);

            $data = $proxy->MonitorApi->getStatusForAllProjects($token, false, $this->getTimestamp());
            $this->setTimestamp($data['timestamp']);

            $proxy->RemoteApi->logout($token);

            $this->log(LogLevel::DEBUG, 'Pulse projects.', array('projects' => count($data['projects'])));
        } catch (\Exception $e) {
            $this->log(LogLevel::ERROR, 'Error occurred.', array(
                'exception' => $e->getMessage()
            ));
        }
    }

    /**
     * @param $uri
     * @throws \Zend\Uri\Exception\InvalidUriException
     * @return PulseConfig
     */
    private function parseUri($uri)
    {
        $uri = new Uri($uri);

        if (!$uri->isAbsolute())
            throw new InvalidUriException($uri . ' has invalid format');

        $userInfo = explode(':', $uri->getUserInfo());

        $options = array(
            'username'  => $userInfo[0],
            'password'  => $userInfo[1],
            'url'       => sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $uri->getPath())
        );

        return new PulseConfig($options);
    }
}