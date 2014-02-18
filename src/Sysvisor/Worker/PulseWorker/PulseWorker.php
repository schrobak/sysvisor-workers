<?php

namespace Sysvisor\Worker\PulseWorker;

use Guzzle\Common\Event;
use Guzzle\Http\Client;
use Sysvisor\Sdk\Worker\WorkerInterface;
use Sysvisor\Worker\PulseWorker\Converter\ArrayConverter;
use Sysvisor\Worker\PulseWorker\Model\Build;
use Sysvisor\Worker\PulseWorker\Model\Changelist;
use Sysvisor\Worker\PulseWorker\Model\TestsSummary;

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

    /**
     * @var \Guzzle\Http\Client
     */
    private $client;

    /**
     * @var string
     */
    private $accessToken = '';

    /**
     * @var string
     */
    private $refreshToken = '';

    /**
     * @var array
     */
    private $localConfig;

    /**
     * @param array $localConfig
     */
    public function __construct(array $localConfig)
    {
        $this->localConfig = $localConfig;
        $this->proxy = (new \Zend\XmlRpc\Client($this->getConfiguration()['configuration']['endpoint']))->getProxy();
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
        $this->token = $this->proxy->RemoteApi->login($this->localConfig['username'], $this->localConfig['password']);

        $projects = $this->getProjects();
        $requests = [];

        foreach ($projects as $id => $maps) {
            foreach ($maps as $name) {
                $build = $this->proxy->RemoteApi->getLatestBuildForProject($this->token, $name, false);
                $parsed = $this->parseBuild($build[0]);
                $requests[] = $this->getClient()->post("http://api.sysvisor.dev/projects/$id", null, $parsed);
            }
        }

        $this->proxy->RemoteApi->logout($this->token);
        $this->token = null;

        $request = $this->getClient()->send($requests);

        return $request->send();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pulse';
    }

    private function parseBuild(array $data)
    {
        $converter = new ArrayConverter();
        $build = $this->createBuild($data);

        $changes = $this->proxy->RemoteApi->getChangesInBuild($this->token, $build->getProjectName(), $build->getNumber());

        $changelists = [];
        foreach ($changes as $change) {
            $changelists[] = $this->createChangelist($change);
        }

        $build->setTestsSummary($this->createTestSummary($data['tests']));
        $build->setChangelists($changelists);

        return $converter->convert($build);
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

    /**
     * @return \Guzzle\Http\Client
     */
    private function getClient()
    {
        if (null === $this->client) {
            $client = new Client();
            $client->getEventDispatcher()->addListener('request.before_send', function(Event $event) {
                $event['request']->setHeader('Authorization', 'Bearer ' . $this->accessToken);
            });

            $key = $this->localConfig['key'];
            $secret = $this->localConfig['secret'];

            $client->getEventDispatcher()->addListener('request.sent', function(Event $event) use ($key, $secret) {
                /** @var \Guzzle\Http\Message\Request $request */
                $request = $event['request'];
                /** @var \Guzzle\Http\Message\Response $response */
                $response = $event['response'];

                if (401 == $response->getStatusCode()) {
                    $client = new Client();

                    $data = $response->json();
                    if (array_key_exists('error', $data) && $data['error'] == 'invalid_token') {
                        $body = [
                            'grant_type' => 'refresh_token',
                            'refresh_token' => $this->refreshToken
                        ];
                    } else {
                        $body = ['grant_type' => 'client_credentials'];
                    }

                    $tokenRequest = $client->post('http://api.sysvisor.dev/oauth/v2/token', null, $body);
                    $tokenRequest->setAuth($key, $secret);
                    $authResponse = $tokenRequest->send();

                    if ($authResponse->isSuccessful()) {
                        $data = $authResponse->json();
                        $this->accessToken = $data['access_token'];
                        $this->refreshToken = $data['refresh_token'];

                        return $request->send();
                    } else {
                        return $response;
                    }
                }
            });

            $this->client = $client;
        }

        return $this->client;
    }

    /**
     * @return array
     */
    private function getConfiguration()
    {
        if (!apc_exists('config')) {
            $config = $this->getClient()
                ->get('http://api.sysvisor.dev/workers/me')
                ->send()
                ->json();

            apc_store('config', $config, 60);
        }

        return apc_fetch('config');
    }

    /**
     * @return array
     */
    private function getProjects()
    {
        if (!apc_exists('projects')) {
            $projects = $this->getClient()
                ->get('http://api.sysvisor.dev/projects')
                ->send()
                ->json();

            $maps = [];
            foreach ($projects as $project) {
                foreach ($project['projectIds'] as $id) {
                    $maps[$project['id']][$id] = $this->proxy->RemoteApi->getProjectNameById($this->token, (string) $id);
                }
            }

            apc_store('projects', $maps, 60);
        }

        return apc_fetch('projects');
    }
}