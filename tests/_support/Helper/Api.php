<?php

namespace Helper;

use Codeception\Module\Symfony;

class Api extends \Codeception\Module
{
    /**
     * @throws \Codeception\Exception\ModuleException
     * @return mixed
     */
    public function getDoctrine()
    {
        return $this->getSymfonyModule()->grabService('doctrine');
    }

    public function getContainer()
    {
        return $this->getSymfonyModule()->_getContainer();
    }

    /**
     * @throws \Codeception\Exception\ModuleException
     * @throws \Exception
     * @return \Codeception\Module\Symfony
     */
    private function getSymfonyModule()
    {
        $symfony = $this->getModule('Symfony');
        if ($symfony instanceof Symfony) {
            return $symfony;
        }
        throw new \Exception(sprintf('Unexpected type (%s) of module Symfony.', gettype($symfony)));
    }
}
