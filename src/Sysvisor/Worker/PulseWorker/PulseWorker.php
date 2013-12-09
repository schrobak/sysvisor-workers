<?php

namespace Sysvisor\Worker\PulseWorker;

use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Psr\Log\LoggerInterface;
use Sysvisor\Sdk\Logger\LoggerAwareInterface;
use Sysvisor\Sdk\Worker\WorkerInterface;
use Sysvisor\Worker\PulseWorker\Config\PulseConfig;
use Zend\Uri\Exception\InvalidUriException;
use Zend\Uri\Uri;
use Zend\XmlRpc\Client;
use Psr\Log\LogLevel;

class PulseWorker implements WorkerInterface, LoggerAwareInterface
{
    /**
     * @var PulseConfig
     */
    private $config;

    /**
     * @var string
     */
    private $timestamp = '';

    /**
     * @var LoggerInterface
     */
    private $logger;

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

    public function execute()
    {
        try {
            $server = new Client($this->config['url']);
            $proxy = $server->getProxy($this->config['namespace']);

            $token = $proxy->RemoteApi->login($this->config['username'], $this->config['password']);

            $data = $proxy->MonitorApi->getStatusForAllProjects($token, false, $this->getTimestamp());
            $this->setTimestamp($data['timestamp']);

            $proxy->RemoteApi->logout($token);

            $this->logger->log(LogLevel::DEBUG, 'Pulse projects.', array('projects' => count($data['projects'])));
        } catch (\Exception $e) {
            $this->logger->log(LogLevel::ERROR, 'Error occurred.', array(
                'exception' => $e->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogHandler()
    {
        return new RotatingFileHandler($this->getName() . '_worker');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pulse';
    }

    /**
     * @param $uri
     * @throws \Zend\Uri\Exception\InvalidUriException
     * @return PulseConfig
     */
    public function parseUri($uri)
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

        $this->config = new PulseConfig($options);

        return $this->config;
    }
}