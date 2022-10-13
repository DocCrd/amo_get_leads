<?php

namespace App\Http\Controllers;

use League\OAuth2\Client\Token\AccessTokenInterface;
use Illuminate\Http\Request;

use App\AmoCrm\ImportAmoCrm;


class ReceiveTokenController extends Controller
{
    public function index(Request $request)
    {
        $apiClient = ImportAmoCrm::getApiClient();

        if ($request->query('referer')) {
            var_dump($request->query('referer'));
            $apiClient->setAccountBaseDomain($request->query('referer'));
        }

        if (!$request->query('code')) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth2state'] = $state;
            
            $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl([
                'state' => $state,
                'mode' => 'post_message',
            ]);

            header('Location: ' . $authorizationUrl);

            die;
        } else {
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($request->query('code'));
            
            if (!$accessToken->hasExpired()) {
                ImportAmoCrm::saveToken([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $apiClient->getAccountBaseDomain(),
                ]);
            }
        }

        return redirect()->route('save_leads');
    }
}
