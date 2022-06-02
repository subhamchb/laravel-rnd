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
        // return $this->requirements($request->currency);
        $requirements = $this->requirements($request->currency);

        return view('partials.fields', compact('requirements'));
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
            ->get(config('services.transferwise.endpoint') . 'accounts');
        return json_decode($response);
    }

    public function createRecipient(Request $request)
    {
        try {
            $details = [];

            foreach ($request->details as $key => $detail) {
                $details[$key] = $detail;
            }

            $recipient = Http::withToken(config('services.transferwise.key'))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.transferwise.endpoint') . 'accounts', [
                    'currency' => $request->currency,
                    'type' => $request->type,
                    'profile' => config('services.transferwise.profile.personal'),
                    'accountHolderName' => $request->accountHolderName,
                    'details' => $details
                ]);

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
            ->collect();;

        return json_decode($response);
    }
}
