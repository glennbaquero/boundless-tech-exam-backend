<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LookupCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerLookupController extends Controller
{
    /**
     * Look up a customer by phone number so the form can either greet them
     * by name or ask for first name / last name / email.
     */
    public function __invoke(LookupCustomerRequest $request): JsonResponse
    {
        $customer = Customer::findByPhone($request->validated('phone'));

        if (! $customer) {
            return response()->json(['recognized' => false]);
        }

        return response()->json([
            'recognized' => true,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
        ]);
    }
}
