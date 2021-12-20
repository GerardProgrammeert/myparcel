<?php

namespace App\MicroServices;

use GuzzleHttp\Client;

/**
 * Interface which can be use to implement api service of different carriers
 * @property string $baseUrl
 * @property object GuzzleHttp\Client  $client
 */

interface CarrierServiceInterface
{

    public function __construct(Client $client);

    /**
     * Send data to api of carrier
     * @param array $data
     * @return array|mixed|string[]
     */
    public function postConsignment(array $data);

}
