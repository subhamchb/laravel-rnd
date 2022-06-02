<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use stdClass;

class AdobeService
{
    protected $BASE_URI = "https://secure.na4.adobesign.com/";
    protected $clientId;
    protected $clientSecret;
    protected $redirectURI;
    protected $state;

    public function __construct()
    {
        $this->clientId = config('services.adobe.client_id');
        $this->clientSecret = config('services.adobe.client_secret');
        $this->redirectURI = config('services.adobe.redirect_uri');
        $this->state = config('services.adobe.state');
    }

    public function getCode(): string
    {
        $scope = "user_read:account+user_write:account+user_login:account+agreement_read:account+agreement_write:account+agreement_send:account+widget_read:account+widget_write:account+library_read:account+library_write:account+workflow_read:account+workflow_write:account";
        $url = "{$this->BASE_URI}/public/oauth/v2?redirect_uri={$this->redirectURI}&response_type=code&client_id={$this->clientId}&state={$this->state}&scope={$scope}";
        return $url;
    }

    public function getTokens(string $code): stdClass
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$this->BASE_URI}/oauth/v2/token");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            "code={$code}&client_id={$this->clientId}&client_secret={$this->clientSecret}&redirect_uri={$this->redirectURI}&grant_type=authorization_code"
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $credentials = curl_exec($ch);
        curl_close($ch);

        return json_decode($credentials);
    }

    private function getAccessToken(): string
    {
        return Cache::remember('adobe.access_token', 60 * 60, function () {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "{$this->BASE_URI}/oauth/v2/refresh");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                "refresh_token=" . Cache::get('adobe.refresh_token') . "&client_id={$this->clientId}&client_secret={$this->clientSecret}&grant_type=refresh_token"
            );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $credentials = curl_exec($ch);
            curl_close($ch);

            return json_decode($credentials)->access_token;
        });
    }

    public function getTemplates(): stdClass
    {
        return Cache::remember('adobe.templates', 60 * 60 * 24, function () {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "{$this->BASE_URI}/api/rest/v6/libraryDocuments");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $this->getAccessToken()));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $templates = curl_exec($ch);
            curl_close($ch);
            return json_decode($templates);
        });
    }

    private function setupAgreementPayload(Request $request): array
    {
        $payload = [
            "fileInfos" => [
                [
                    "libraryDocumentId" => $request->template_id
                ]
            ],
            "name" => $request->name,
            "participantSetsInfo" => [
                [
                    "memberInfos" => [
                        [
                            "email" => $request->email
                        ]
                    ],
                    "order" => 1,
                    "role" => "SIGNER"
                ]
            ],
            "message" => 'Please fill and sign',
            "signatureType" => "ESIGN",
            "emailOption" => [
                "sendOptions" => [
                    "completionEmails" => "NONE",
                    "inFlightEmails" => "NONE",
                    "initEmails" => "NONE"
                ]
            ],
            "state" => "IN_PROCESS"
        ];

        return $payload;
    }

    public function createAgreement(Request $request): stdClass
    {
        $payload = $this->setupAgreementPayload($request);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->BASE_URI}/api/rest/v6/agreements",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken(),
                "Content-Type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function getAgreements(): stdClass
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->BASE_URI}/api/rest/v6/agreements",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken()
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function getAgreement(string $id): stdClass
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->BASE_URI}/api/rest/v6/agreements/{$id}/signingUrls",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken()
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    public function getSignedAgreementLink(string $id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->BASE_URI}/api/rest/v6/agreements/{$id}/combinedDocument",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken()
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $filename = 'test.pdf';

        return Response::make($response, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename={$filename}"
        ]);
    }
}
