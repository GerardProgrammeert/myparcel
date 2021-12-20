<?php

namespace App\MicroServices;

use App\MicroServices\CarrierServiceInterface;
use App\Http\Requests\ShipmentRules;
use App\Exceptions\CarrierServiceException;
use GuzzleHttp\Client;

/**
 * The specific layer to handle request to a specific carrier
 */
class CarrierService implements CarrierServiceInterface
{

    /**
     * base url of carrier service api
     * @var string
     */
    private  $baseUrl = 'https:://carrierurl.com/api';

    /**
     * A Guzzle Client to Communicatie with api of carriers
     *
     * @var GuzzleHttp\Client Client
     *
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send data to api of carrier
     * @param array $data
     * @return array|mixed|string[]
     */
    public function postConsignment(array $data){

        //check given data
        $validator = \Validator::make($data,$this->getValidationRulesShipment());

        //report when data format is not met
        if($validator->fails()){

            $this->throwException('Give data for shipment is not correctly',400);

        }

        //handle to post request
        $response = $this->client->post($this->baseUrl . '/consignment', $this->transformData($data));

        //handle when somethong went wrong in the request to api
        if($response->getStatusCode() >= 400 && $response->getStatusCode() < 500){


            $this->throwException('Connection with Carrier API failed',400);

        }

        //handle when service is unavailable
        if($response->getStatusCode() >= 500){

            $this->throwException('Connection with Carrier API failed',500);

        }

        //return a response
        if($response->getStatusCode() >= 200 && $response->getStatusCode() < 400){

            return json_decode($response->getBody()->getContents(),true);

        }

    }

    /**
     *
     * transform data receive form user to carrier data
     *
     * @param $data
     * @return array
     *
     */
    protected function transformData(array $data){

        $transformed = [];

        $transformed['send_to']['address'] = $data['recipient_address']['street_name'] . ' ' .  $data['recipient_address']['street_number'] ;
        $transformed['send_to']['country'] = $data['recipient_address']['country_code'] ;
        $transformed['send_to']['recipient'] = $data['recipient_address']['first_name'] . ' ' . $data['recipient_address']['last_name'] ;
        $transformed['send_to']['phone'] = $data['recipient_address']['phone'];
        $transformed['quantity'] = $data['quantity'];
        $transformed['service'] =   $data['service'];

        return $transformed;
    }

    /**
     * Getter for data structure for a Shipment
     * @return string[]
     */
    protected function getValidationRulesShipment(){

       return ShipmentRules::getValidationRules();

    }


    /**
     * @param $message
     * @param int $code
     * @throws CarrierServiceException
     */
    private function throwException($message, $code = 400)
    {
        throw new CarrierServiceException($message, $code);
    }
}

