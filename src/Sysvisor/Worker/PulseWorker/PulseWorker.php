<?php

namespace Sysvisor\Worker\PulseWorker;

use Sysvisor\Sdk\Worker\WorkerInterface;
use Sysvisor\Worker\PulseWorker\Converter\ArrayConverter;
use Sysvisor\Worker\PulseWorker\Model\Build;
use Sysvisor\Worker\PulseWorker\Model\Changelist;
use Sysvisor\Worker\PulseWorker\Model\TestsSummary;
use Zend\XmlRpc\Client;

class PulseWorker implements WorkerInterface
{
    /**
     * @var string
     */
    private $timestamp;

    /**
     * @var \Zend\XmlRpc\Client\ServerProxy
     */
    private $proxy;

    /**
     * @var string
     */
    private $token;

    public function __construct($url, $username, $password)
    {
        $server = new Client($url);
        $this->proxy = $server->getProxy();

        $this->token = $this->proxy->RemoteApi->login($username, $password);
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
        if (!$this->timestamp) {
            $this->timestamp = (string) round(microtime(true) * 1000);
        }

        return $this->timestamp;
    }

    public function execute()
    {
        $builds = [];
        foreach ($this->getAllProjects() as $project) {
            if ($project['inProgress']) {
                $parsed = $this->parseBuilds($project['inProgress']);
            } elseif ($project['completedSince']) {
                $parsed = $this->parseBuilds($project['completedSince']);
            } else {
                $parsed = $this->parseBuilds([$project['latestCompleted']]);
            }

            $builds[] = $parsed;
        }

        $this->proxy->RemoteApi->logout($this->token);

        $client = new \Guzzle\Http\Client();
        $request = $client->post('http://api.sysvisor.dev/tools/pulse', null, $builds);
        $request->setHeader('Authorization', 'Bearer Nzg2YmNkODY2OTc0ZTY1YjhlZDc5NzI5MzE0ZGU2ZmY1ZTBlMGM1MDdiNTllNzE4MDI5OGI0YTAzNTgyNWJiYg');

        return $request->send();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pulse';
    }

    private function getAllProjects()
    {
        $data = $this->proxy->MonitorApi->getStatusForAllProjects($this->token, false, $this->getTimestamp());
        $this->setTimestamp($data['timestamp']);

        return $data['projects'];
    }

    private function parseBuilds(array $data)
    {
        $converter = new ArrayConverter();
        $builds = [];
        foreach ($data as $buildData) {
            $build = $this->createBuild($buildData);

            $changes = $this->proxy->RemoteApi->getChangesInBuild($this->token, $build->getProjectName(), $build->getNumber());

            $changelists = [];
            foreach ($changes as $change) {
                $changelists[] = $this->createChangelist($change);
            }

            $build->setTestsSummary($this->createTestSummary($buildData['tests']));
            $build->setChangelists($changelists);

            $builds[] = $converter->convert($build);
        }

        return $builds;
    }

    /**
     * @param array $change
     * @return Changelist
     */
    private function createChangelist(array $change)
    {
        $changelist = new Changelist();

        $changelist->setAuthor($change['author']);
        $changelist->setComment($change['comment']);
        $changelist->setRevision($change['revision']);
        $changelist->setDateTime(\DateTime::createFromFormat('Ymd?H:i:s', $change['date'], new \DateTimeZone('UTC')));

        return $changelist;
    }

    /**
     * @param array $summary
     * @return TestsSummary
     */
    private function createTestSummary(array $summary)
    {
        $testsSummary = new TestsSummary();

        $testsSummary->setErrors($summary['errors']);
        $testsSummary->setExpectedFailures($summary['expectedFailures']);
        $testsSummary->setFailures($summary['failures']);
        $testsSummary->setPassed($summary['passed']);
        $testsSummary->setSkipped($summary['skipped']);
        $testsSummary->setTotal($summary['total']);

        return $testsSummary;
    }

    /**
     * @param array $buildData
     * @return Build
     */
    private function createBuild(array $buildData)
    {
        $build = new Build($buildData['id']);

        $build->setProjectName($buildData['project']);
        $build->setProgress($buildData['progress']);
        $build->setRevision($buildData['revision']);
        $build->setReason($buildData['reason']);
        $build->setStatus($buildData['status']);

        if ($buildData['startTimeMillis'] > 0) {
            $build->setStartedAt(\DateTime::createFromFormat('U', round($buildData['startTimeMillis'] / 1000), new \DateTimeZone('UTC')));
        }

        if ($buildData['endTimeMillis'] > 0) {
            $build->setEndedAt(\DateTime::createFromFormat('U', round($buildData['endTimeMillis'] / 1000), new \DateTimeZone('UTC')));
        }

        return $build;
    }
}