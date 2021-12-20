<?php

namespace Tests\Unit;

use App\Exceptions\CarrierServiceException;
use App\MicroServices\CarrierService;
use Carbon\Carbon;
use Faker\Factory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * unit test for post shipment data to carrier
 */
class CarrierServicePostShipmentTest extends TestCase
{
    /**
     * @var string[]
     */
    private $headers =  ['Accept','application/json'];

    /**
    *
    * @test
    *
    * unit testing method postConsigment
    *
    */
    public function testMethodCarrierServicePostConsignment(){

        $body =  json_encode(
            ['tracking_code' => Str::uuid(),
                'delivery_date' => Carbon::today()->addDays(rand(1, 365))->toDateString()]);

        $carrierService = $this->getCarrierService(201, $body);

        $faker = Factory::create();
        $service = ['express','economy'];
        $shipment = [
            'recipient_address' => [
                'street_name' => $faker->streetName,
                'street_number' => rand(1,1000),
                'country_code' => 'NL',
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'phone' => $faker->phoneNumber,
            ],
            'quantity' => rand(1,100),
            'service' => $service[rand(0,1)],
        ];

        $result = $carrierService->postConsignment($shipment);


        $this->assertEquals(json_decode($body,true), $result);

    }

    /*
     * @test
     * unit test
     */
    public function testShouldThrowExceptionForInvalidShipmentData()
    {
        $this->expectException(CarrierServiceException::class);
        $this->expectExceptionMessage('Give data for shipment is not correctly');
        $this->expectExceptionCode(400);

        $carrierService = $this->getCarrierService(400);

        $carrierService->postConsignment(['Invalid']);

    }

    private function getCarrierService($status, $body = null)
    {
        $mock = new MockHandler([new Response($status, $this->headers, $body)]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return new CarrierService($client);

    }
}
