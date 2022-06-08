<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TransferwiseTest extends Controller
{
    public function index()
    {
        $list = $this->recepientList();
        return view('wise', compact('list'));
    }

    public function getFormFields(Request $request)
    {
        $requirements = $this->requirements($request->currency);
        return view('partials.fields', ['requirements' => $requirements, 'currency' => $request->currency, 'old' => null, 'activeTab' => $requirements[0]->type]);
    }

    public function deleteMember($accountID)
    {
        Http::withToken(config('services.transferwise.key'))
            ->delete(config('services.transferwise.endpoint') . 'accounts/' . $accountID);

        return back();
    }

    private function recepientList()
    {
        $response = Http::withToken(config('services.transferwise.key'))
            ->get(config('services.transferwise.endpoint') . 'accounts')
            ->collect();

        return json_decode($response);
    }

    public function createRecipient(Request $request)
    {
        // return $request->all();
        try {
            $payload = $this->getPayload($request);

            $recipient = Http::withToken(config('services.transferwise.key'))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.transferwise.endpoint') . 'accounts', $payload);

            $recipientData = json_decode($recipient);

            if (isset($recipientData->errors)) {
                return back()->with('errors', $recipientData->errors);
            }

            $data = [
                'id' => $recipientData->id,
                'profile' => $recipientData->profile,
                'accountHolderName' => $recipientData->accountHolderName,
                'currency' => $recipientData->currency,
                'country' => $recipientData->country,
                'accountNumber' => $recipientData->details->accountNumber,
            ];

            return back()->with('success', ['text' => 'Recipient added successfully.', 'data' => json_encode($data)]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function requirements(string $currency)
    {
        $response = Http::withToken(config('services.transferwise.key'))
            ->get(config('services.transferwise.endpoint') . 'account-requirements?source=USD&target=' . $currency . '&sourceAmount=0.01')
            ->collect();

        return json_decode($response);
    }

    public function childRequirements(Request $request)
    {
        $payload = $this->getPayload($request);
        $response = Http::withToken(config('services.transferwise.key'))
            ->post(config('services.transferwise.endpoint') . 'account-requirements?source=USD&target=' . $request->currency . '&sourceAmount=0.01', $payload)
            ->collect();

        return view('partials.fields', ['requirements' => json_decode($response), 'currency' => $request->currency, 'old' => $request, 'activeTab' => $request->type]);
    }

    private function getPayload(Request $request)
    {
        $details = [];
        $address = [];

        if ($request->details != null && count($request->details) > 0) {
            foreach ($request->details as $key => $detail) {
                if ($detail != null) {
                    if (str_starts_with($key, 'address')) {
                        $keys = explode('.', $key);
                        $key = $keys[1];

                        $address[$key] = $detail;
                    } else {
                        $details[$key] = $detail;
                    }

                    $details['address'] = $address;
                }
            }
        }

        return [
            'type' => $request->type,
            'profile' => config('services.transferwise.profile.personal'),
            'accountHolderName' => $request->accountHolderName,
            'currency' => $request->currency,
            'details' => $details
        ];
    }
}
