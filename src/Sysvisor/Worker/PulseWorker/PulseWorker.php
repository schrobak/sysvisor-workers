<?php

namespace Sysvisor\Worker\PulseWorker;

use Sysvisor\Sdk\Worker\WorkerInterface;
use Zend\XmlRpc\Client;

class PulseWorker implements WorkerInterface
{
    /**
     * @var string
     */
    private $timestamp = '';

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
        $url = '';
        $namespace = '';
        $username = '';
        $password = '';

//        try {
            $server = new Client($url);
            $proxy = $server->getProxy($namespace);

            $token = $proxy->RemoteApi->login($username, $password);

            $data = $proxy->MonitorApi->getStatusForAllProjects($token, false, $this->getTimestamp());
            $this->setTimestamp($data['timestamp']);

            $proxy->RemoteApi->logout($token);
//        } catch (\Exception $e) {
//            syslog(LOG_ERR, $e->getMessage());
//        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pulse';
    }
}