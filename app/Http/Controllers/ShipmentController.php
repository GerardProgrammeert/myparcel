<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestShipments;
use App\MicroServices\CarrierService;
use GuzzleHttp\Client;

/*
 * API Controller for Shipments
 */
class ShipmentController extends Controller
{

    /**
     * Subscribe a shipment to a carrier via MicroService
     *
     * @param  App\Http\Requests\RequestShipments $request
     * @return \Illuminate\Http\Response
     */
    public function create(RequestShipments $request, Client $client)
    {

        //Get Tracking code and Shipment date
        $this->client = $client;
        $carrierService = new CarrierService($client);

        $validated = $request->validated();

        try 
        {
            $consignment = $carrierService->postConsignment($validated);
        }
        catch(\Exception $e) 
        {
            return response()->json(['error'=>$e->getMessage()], $e->getCode());
        }

        $shipment = array_merge($validated, $consignment);

        return response()->json( $shipment,201);

    }


}
