<?php

namespace App\Http\Controllers;

use App\Services\AdobeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdobeController extends Controller
{
    public $service;

    public function __construct()
    {
        $this->service = new AdobeService;
    }

    public function index()
    {
        if (!Cache::has('adobe.refresh_token')) {
            return redirect($this->service->getCode());
        }

        return view('adobe', ['templates' => $this->service->getTemplates(), 'agreements' => $this->service->getAgreements()]);
    }

    public function setCredentials(Request $request)
    {
        $tokens =  $this->service->getTokens($request->code);

        Cache::add('adobe.access_token', $tokens->access_token, 60 * 60);

        if (!Cache::has('adobe.refresh_token')) {
            Cache::forever('adobe.refresh_token', $tokens->refresh_token);
        }

        return redirect(route('adobe.index'))->with('success', ['text' => 'Access granted.', 'data' => 'Access token: ' . $tokens->access_token]);
    }

    public function createAgreement(Request $request)
    {
        try {
            $response = $this->service->createAgreement($request);

            if (isset($response->code)) {
                return back()->with('error', $response->message);
            }

            return back()->with('success', ['text' => 'Agreement created successfully.', 'data' => 'Agreement ID: ' .  $response->id]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function viewAgreement(string $id, string $status)
    {
        if ($status === "SIGNED") {
            return $this->service->getSignedAgreementLink($id);
        }

        $agreement = $this->service->getAgreement($id);

        if (isset($agreement->code)) {
            return back()->with('error', $agreement->message);
        }

        return view('view-agreement', ['agreement' => $agreement]);
    }

    public function getTemplateFields(Request $request)
    {
        // return $this->service->getTemplateFields($request->id);
        return view('template-fields', ['fields' => $this->service->getTemplateFields($request->id)]);
    }
}
