<?php 
namespace App\Utils;

class ApisConsumer {
    public static function getDataBi($bi){
        $url = 'https://api.gov.ao/consultarBI/v2/?bi='.$bi;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = json_decode(curl_exec($ch), true);

        if (count($data)) {
            $data_bi = [
                'FIRST_NAME' => ucwords(strtolower($data[0]['FIRST_NAME'])),
                'LAST_NAME' => ucwords(strtolower($data[0]['LAST_NAME'])),
                'GENDER_NAME' => ucwords(strtolower($data[0]['GENDER_NAME'])),
                'BIRTH_DATE' => Auxiliar::formatData($data[0]['BIRTH_DATE']),
                'FATHER_FIRST_NAME' => ucwords(strtolower($data[0]['FATHER_FIRST_NAME'])),
                'FATHER_LAST_NAME' => ucwords(strtolower($data[0]['FATHER_LAST_NAME'])),
                'MOTHER_FIRST_NAME' => ucwords(strtolower($data[0]['MOTHER_FIRST_NAME'])),
                'MOTHER_LAST_NAME' => ucwords(strtolower($data[0]['MOTHER_LAST_NAME'])),
                'BIRTH_PROVINCE_NAME' => ucwords(strtolower($data[0]['BIRTH_PROVINCE_NAME'])),
                'BIRTH_MUNICIPALITY_NAME' => ucwords(strtolower($data[0]['BIRTH_MUNICIPALITY_NAME'])),
                'ISSUE_DATE' => Auxiliar::formatData($data[0]['ISSUE_DATE']),
                'RESIDENCE_COUNTRY_NAME' => ucwords(strtolower($data[0]['RESIDENCE_COUNTRY_NAME'])),
            ];
        }
        if (isset($data_bi)) {
            return response()->json($data_bi);
        }

        return response()->json([
            'error' => 500,
            'message' => 'BI não encontrado',
        ]);
    }
}