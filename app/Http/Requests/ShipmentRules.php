<?php
namespace App\Http\Requests;

class ShipmentRules
{

    static function getValidationRules()
    {

        return [
            'recipient_address' => 'required|array',
            'recipient_address.street_name' => 'required|string',
            'recipient_address.street_number' => 'required|integer|min:1',
            'recipient_address.country_code' => 'required|in:' . self::getCountrySymbols(),
            'recipient_address.first_name' => 'required|string',
            'recipient_address.last_name' => 'required|string',
            'recipient_address.phone' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'service' => 'required|in:express,economy',
        ];
    }

    /**
     * Get Country Symbols
     * @return string
     */
    static function getCountrySymbols()
    {
        $symbols = 'AF,AL,DZ,AD,AO,AG,AR,AM,AU,AT,AZ,BS,BH,BD,BB,BY,BE,BZ,BJ,BT,BO,BA,BW,BR,BN,BG,BF,BI,KH,CM,CA,CV,CF,TD,CL,CN,CO,KM,CG,CD,CR,CI,HR,CU,CY,CZ,DK,DJ,DM,DO,EC,EG,SV,GQ,ER,EE,ET,FJ,FI,FR,GA,GM,GE,DE,GH,GR,GD,GT,GN,GW,GY,HT,HN,HU,IS,IN,ID,IR,IQ,IE,IL,IT,JM,JP,JO,KZ,KE,KI,KP,KR,KW,KG,LA,LV,LB,LS,LR,LY,LI,LT,LU,MK,MG,MW,MY,MV,ML,MT,MH,MR,MU,MX,FM,MA,MD,MC,MN,ME,MZ,MM,NA,NR,NP,NL,NZ,NI,NE,NG,NO,OM,PK,PW,PA,PG,PY,PE,PH,PL,PT,QA,RO,RU,RW,KN,LC,VC,WS,SM,ST,SA,SN,RS,SC,SL,SG,SK,SI,SB,SO,ZA,SS,ES,LK,SD,SR,SZ,SE,CH,SY,TJ,TZ,TH,TL,TG,TO,TT,TN,TR,TM,TV,UG,UA,AE,GB,US,UY,UZ,VU,VE,VN,YE,ZM,ZW';

        return $symbols;
    }
}
