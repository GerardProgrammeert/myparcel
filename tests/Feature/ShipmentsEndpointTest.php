<?php

namespace Tests\Unit;

use Tests\TestCase;
use Faker\Factory;
use App\Services\HttpClient;
use Illuminate\Support\Str;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use App\Http\Controllers\ShipmentController;
use App\Exceptions\CarrierServiceException;
use App\MicroServices\CarrierService;


class ShipmentsEndpointTest extends TestCase
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var string[]
     */
    private $headers =  ['Accept','application/json'];

    /**
     * Setup for test
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->faker =  Factory::create();
    }


    /**
     * Default Response data for Carrier Api
     *
     * @return false|string
     */
    private function mockResponseData(){

        return json_encode(
            ['tracking_code' => Str::uuid(),
            'delivery_date' => Carbon::today()->addDays(rand(1, 365))->toDateString()]);

    }

    /**
     * Default Shipment data for Carrier Api
     *
     * @return false|string
     */
    private function mockShipmentData(){

        $service = ['express','economy'];

        $shipment = [
            'recipient_address' => [
                'street_name' => $this->faker->streetName,
                'street_number' => rand(1,1000),
                'country_code' => 'NL',
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'phone' => $this->faker->phoneNumber,
            ],
            'quantity' => rand(1,100),
            'service' => $service[rand(0,1)],
        ];

        return $shipment;


    }

    private function setupMockGuzzle(MockHandler $mock){

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $this->swap(Client::class,$client);
    }


    /**
     * @test
     *
     * Post a shipment to api successfully with statuscode 201 and 301
     *
     * @return void
     */
    public function SendShipmentSuccessfully()
    {

        $mock = new MockHandler(
                [new Response(201, $this->headers, $this->mockResponseData()),
                new Response(301, $this->headers, $this->mockResponseData())]
        );

        $this->setupMockGuzzle($mock);

        $this->json('post', 'api/shipments',$this->mockShipmentData())
            ->assertHeader('Content-Type','application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'tracking_code',
                'delivery_date',
            ]);

        $this->json('post', 'api/shipments',$this->mockShipmentData())
            ->assertHeader('Content-Type','application/json')
            ->assertStatus(201)
            ->assertJsonStructure([
                'tracking_code',
                'delivery_date',
            ]);


    }

    /**
     * @test
     *
     * Post a shipment to api unsuccessfully to carrier
     *
     * @return void
     */
    public function SendShipmentUnsuccessfullyToCarrier()
    {

        $mock = new MockHandler([new Response(400, $this->headers ,json_encode(['error'=>'forbidden']))]);

        $this->setupMockGuzzle($mock);

        $this->json('post', 'api/shipments',$this->mockShipmentData())
            ->assertHeader('Content-Type','application/json')
            ->assertStatus(400)
            ->assertJsonStructure([
                'error',
            ]);

    }

    /**
     * @test
     *
     * Post a shipment to api when Carrier APi is unavailable
     * feature test
     * @return void
     */
    public function SendShipmentServiceCarrierUnavailable()
    {

        $mock = new MockHandler([new Response(500, $this->headers ,json_encode(['error'=>'unavailable']))]);

        $this->setupMockGuzzle($mock);

        $this->json('post', 'api/shipments',$this->mockShipmentData())
            ->assertHeader('Content-Type','application/json')
            ->assertStatus(500)
            ->assertJsonStructure([
                'error',
            ]);



    }

    /**
     * @test
     * Post a shipment with no data
     *
     * @return void
     */
    public function TestPostShipmentUnsuccessfulWithErrors()
    {
        //no guzzle needed, since request is rejected

        $shipment = [];

        $this->json('post', 'api/shipments',$shipment)
            ->assertHeader('Content-Type','application/json')
            ->assertStatus(422)
            ->assertJsonValidationErrors(
                    ['recipient_address',
                    'recipient_address.street_name',
                    'recipient_address.street_number',
                    'recipient_address.country_code',
                    'recipient_address.first_name',
                    'recipient_address.last_name',
                    'recipient_address.phone',
                    'quantity',
                    'service']);

    }

}
