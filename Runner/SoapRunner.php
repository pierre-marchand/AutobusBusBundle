<?php

namespace Autobus\Bundle\BusBundle\Runner;

use Autobus\Bundle\BusBundle\Context;
use Autobus\Bundle\BusBundle\Entity\Job;
use Autobus\Bundle\BusBundle\Entity\Execution;
use Autobus\Bundle\BusBundle\Soap\Action\SoapAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SoapRunner
 *
 * @author  Simon CARRE <simon.carre@clickandmortar.fr>
 * @package Autobus\Bundle\BusBundle\Runner
 */
class SoapRunner extends WebRunner
{
    /**
     * Wsdl path key in job config array
     *
     * @var string
     */
    const CONFIG_WSDL_PATH = 'wsdlPath';

    /**
     * Current wsdl path
     *
     * @var string
     */
    protected $wsdlPath;

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    protected function run(Context $context, Job $job, Execution $execution)
    {
        // If is a wsdl request
        $request        = $context->getRequest();
        $this->wsdlPath = $this->loadWsdlPath($job);
        if ($request->query->has('wsdl')) {
            return $this->getWsdlResponse();
        }

        // Validate job configuration
        if (!$this->validateConfig($job)) {
            throw new \Exception('Invalid job configuration.');
        }

        // Init SOAP server
        $soapServer = new \SoapServer($this->wsdlPath);
        $soapAction = $this->getSoapAction();
        $soapAction->setJob($job);
        $soapServer->setObject($soapAction);
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');
        ob_start();
        $soapServer->handle();
        $response->setContent(ob_get_clean());
        $context->setResponse($response);
    }

    /**
     * Load wsdl path from job parameter
     *
     * @param Job $job
     *
     * @return string
     */
    protected function loadWsdlPath(Job $job)
    {
        // Check for wsdl path in job configuration
        $config = $job->getConfigArray();
        if (array_key_exists(self::CONFIG_WSDL_PATH, $config)) {
            return $config[self::CONFIG_WSDL_PATH];
        }

        return null;
    }

    /**
     * Get wsdl response
     *
     * @return Response
     */
    protected function getWsdlResponse()
    {
        $response    = new Response();
        $wsdlContent = file_get_contents($this->wsdlPath);
        if ($wsdlContent !== false) {
            $response->headers->set('Content-Type', 'text/xml');
            $response->setContent($wsdlContent);
        }

        return $response->send();
    }

    /**
     * Validate config for $job
     *
     * @param Job $job
     *
     * @return bool
     */
    protected function validateConfig(Job $job)
    {
        // TODO: Implement validateConfig() method.

        return true;
    }

    /**
     * Return SOAP action
     *
     * @return SoapAction
     */
    protected function getSoapAction()
    {
        // TODO: Implement getSoapAction() method.
    }
}
