<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use stdClass;

class AdobeService
{
    protected $BASE_URI = "https://secure.na4.adobesign.com/";
    protected $clientId;
    protected $clientSecret;
    protected $redirectURI;
    protected $state;
    protected $webhook_url;

    public function __construct()
    {
        $this->clientId = config('services.adobe.client_id');
        $this->clientSecret = config('services.adobe.client_secret');
        $this->redirectURI = config('services.adobe.redirect_uri');
        $this->state = config('services.adobe.state');
        $this->webhook_url = config('services.adobe.webhook_url');
    }

    public function getCode(): string
    {
        $scope = "user_read:account+user_write:account+user_login:account+agreement_read:account+agreement_write:account+agreement_send:account+widget_read:account+widget_write:account+library_read:account+library_write:account+workflow_read:account+workflow_write:account+webhook_read:account+webhook_write:account+webhook_retention:account";
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

    public function getTemplateFields(string $id): array
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->BASE_URI}/api/rest/v6/libraryDocuments/{$id}/formFields",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken()
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $this->filterPrefillData(json_decode($response)->fields);
    }

    private function filterPrefillData(array $data): array
    {
        return array_filter(
            $data,
            function ($filterable) {
                return $filterable->assignee === "PREFILL";
            }
        );
    }

    private function setupAgreementPayload(Request $request): array
    {
        $mergeData = [];

        if (isset($request->mergedata)) {
            $mergeData = $this->setupMergeData($request->mergedata);
        }

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
                            "email" => $request->email,
                            "self" => false
                        ]
                    ],
                    "order" => 1,
                    "role" => "SIGNER"
                ],
                [
                    "memberInfos" => [
                        [
                            "email" => "legal@abroadworks.com",
                            "self" => true
                        ]
                    ],
                    "order" => 1,
                    "role" => "SIGNER"
                ]
            ],
            "mergeFieldInfo" => $mergeData,
            "message" => 'Please fill and sign',
            "signatureType" => "ESIGN",
            "emailOption" => [
                "sendOptions" => [
                    "completionEmails" => "NONE",
                    "inFlightEmails" => "NONE",
                    "initEmails" => "NONE"
                ]
            ],
            "state" => "DRAFT"
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

        $id = json_decode($response)->id;

        $etagForMerge = $this->agreementEtag($id, true);

        $this->mergeData($id, $etagForMerge, $request->mergedata);

        $this->changeAgreementState('IN_PROCESS', $id);

        $this->agreementSignedWebhook($id);

        return json_decode($response);
    }

    public function mergeData(string $id, string $etag, array $data): stdClass | null
    {
        $mergeData = [
            "fieldMergeInfos" => $this->setupMergeData($data)
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->BASE_URI}/api/rest/v6/agreements/{$id}/formFields/mergeInfo",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($mergeData),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken(),
                'Content-Type: application/json',
                'If-Match: ' . $etag
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }

    public function changeAgreementState(string $state, string $id): stdClass | null
    {
        $state = [
            "state" => $state
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->BASE_URI}/api/rest/v6/agreements/{$id}/state",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($state),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken(),
                "Content-Type: application/json",
                "If-Match: " . $this->agreementEtag($id)
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }

    public function agreementEtag(string $id, $forMerge = false): string
    {
        $url = $forMerge ? "{$this->BASE_URI}/api/rest/v6/agreements/{$id}/formFields/mergeInfo" :  "{$this->BASE_URI}/api/rest/v6/agreements/{$id}";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HEADER => 1,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken()
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return $this->extractEtag($response);
    }

    private function extractEtag(string $response): string
    {
        $out = preg_split('/(\r?\n){2}/', $response, 2);
        $headers = $out[0];
        $headersArray = preg_split('/\r?\n/', $headers);
        $headersArray = array_map(function ($h) {
            return preg_split('/:\s{1,}/', $h, 2);
        }, $headersArray);

        $tmp = [];
        foreach ($headersArray as $h) {
            $tmp[strtolower($h[0])] = isset($h[1]) ? $h[1] : $h[0];
        }
        $headersArray = $tmp;
        $tmp = null;
        return $headersArray['etag'];
    }

    private function setupMergeData(array $data): array
    {
        $processedData = [];

        foreach ($data as $key => $value) {
            $processedData[] = [
                "defaultValue" => $value,
                "fieldName" => trim($key, '\'"')
            ];
        }

        return $processedData;
    }

    public function agreementSignedWebhook($id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "{$this->BASE_URI}/api/rest/v6/webhooks",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "name": "Webform Resource Webhook TESTING-1",
                "scope": "RESOURCE",
                "state": "ACTIVE",
                "resourceType": "AGREEMENT",
                "resourceId":"' . $id . '",
                "webhookSubscriptionEvents": [
                  "AGREEMENT_ALL"
                ],
                "webhookUrlInfo": {
                  "url": "' . $this->webhook_url . '"
                }
              }',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $this->getAccessToken(),
                "Content-Type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
