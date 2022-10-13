<?php

namespace App\Http\Controllers;
use AmoCRM\Exceptions\AmoCRMApiException;
use League\OAuth2\Client\Token\AccessTokenInterface;

use App\AmoCrm\ImportAmoCrm;
use App\Models\Lead;

class SaveLeadsController extends Controller
{
    public function index()
    {
        $apiClient = ImportAmoCrm::getApiClient();
        $accessToken = ImportAmoCrm::getToken();

        $apiClient->setAccessToken($accessToken)
            ->setAccountBaseDomain($accessToken->getValues()['baseDomain'])
            ->onAccessTokenRefresh(
                function (AccessTokenInterface $accessToken, string $baseDomain) {
                    saveToken(
                        [
                            'accessToken' => $accessToken->getToken(),
                            'refreshToken' => $accessToken->getRefreshToken(),
                            'expires' => $accessToken->getExpires(),
                            'baseDomain' => $baseDomain,
                        ]
                    );
                }
            );


        $leadsService = $apiClient->leads();

        //Получим сделки
        try {
            $leadsCollection = $leadsService->get();
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }

        dump($leadsCollection);

        foreach ($leadsCollection as $lead) {
            Lead::updateOrCreate([
                'lead_id' => $lead->id
            ], [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'lead_responsible' => $lead->responsibleUserId,
                'lead_price' => $lead->price,
            ]);
        }
        return 'leads saved to database!';
    }
}
