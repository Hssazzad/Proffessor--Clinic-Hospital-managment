<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|integer|exists:patients,id',
            'patient_code' => 'nullable|string',
            'patient_name' => 'nullable|string',
            'patient_age' => 'nullable|string',
            'mobile_no' => 'nullable|string',
            'admission_id' => 'nullable|integer|exists:nursing_admissions,id',
            'total_bill' => 'required|numeric|min:1',
            'discount_type' => 'sometimes|nullable|in:percent,flat',
            'discount_value' => 'sometimes|nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,mobile_banking,card,bank_transfer,cheque',
            'collected_by' => 'nullable|string',
            'reference_name' => 'nullable|string|max:255',
            'ref_doctor_id' => 'nullable|integer|exists:doctors,id',
            'items' => 'required|array|min:1',
            'items.*.category' => 'nullable|integer',
            'items.*.category_name' => 'nullable|string',
            'items.*.service_name' => 'required|string|max:255',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
            'items.*.doctor_id' => 'nullable|integer|exists:doctors,id',
            'items.*.chamber_fee_config_id' => 'nullable|integer|exists:chamber_fee_configs,id',
            'delivery_datetime' => 'nullable|string|max:30',
        ];
    }

    public function messages()
    {
        return [
            'items.min' => '??????? ???? ????? ??? ???? ?????',
            'paid_amount.min' => '??????? ?????????? ? ?? ???? ??? ?????',
            'discount_type.in' => 'Discount type must be either percent or flat.',
        ];
    }

    /**
     * Conditional discount rules — depend on discount_type:
     *  - percent: value must be within 0-100
     *  - flat:    value must not exceed the total bill
     *  - discount (final taka amount) is ALWAYS capped at total_bill
     *    (backend verify — never rely on the frontend clamp alone).
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $totalBill = (float) $this->input('total_bill', 0);
            $discountType = (string) $this->input('discount_type', 'percent'); // absent → percent (backward compat)
            $valueRaw = (float) $this->input('discount_value', 0);
            $discount = (float) $this->input('discount', 0);

            // Chamber fee is fully config-driven — any doctor item MUST carry
            // chamber_fee_config_id. A missing id is rejected with a clear
            // error (never silently resolved to a default config).
            foreach ((array) $this->input('items', []) as $i => $item) {
                if (! empty($item['doctor_id']) && empty($item['chamber_fee_config_id'])) {
                    $validator->errors()->add(
                        "items.$i.chamber_fee_config_id",
                        'Chamber fee config is required for doctor consultation items (doctor_id: '
                        .$item['doctor_id'].').'
                    );
                }
            }

            if ($discountType === 'flat') {
                // Flat (taka): raw value (or the pre-computed taka amount when raw is absent)
                $flatValue = $valueRaw > 0 ? $valueRaw : $discount;
                if ($flatValue > $totalBill) {
                    $validator->errors()->add(
                        'discount_value',
                        'Flat discount cannot exceed the total bill (৳ '.number_format($totalBill, 0).').'
                    );
                }
            } else {
                // Percent: raw value (or the legacy discount-as-percent when raw is absent)
                $pct = $valueRaw > 0 ? $valueRaw : $discount;
                if ($pct < 0 || $pct > 100) {
                    $validator->errors()->add(
                        'discount_value',
                        'Percent discount must be between 0 and 100.'
                    );
                }
            }

            // Security: the final taka discount amount must never exceed the total bill.
            if ($discount > $totalBill) {
                $validator->errors()->add(
                    'discount',
                    'Discount amount cannot exceed the total bill (৳ '.number_format($totalBill, 0).').'
                );
            }

            // Approval rules (mirror InvoiceService::createInvoice exactly):
            //  - min50 is ALWAYS 50% of the ORIGINAL total_bill (never of the
            //    discounted net), so big discounts can't lower the threshold.
            //  - ANY discount (> 0) REQUIRES a reference name and always goes
            //    to pending admin approval — regardless of amount paid.
            //  - The 50%-of-total rule only applies when there is NO discount.
            $discountTaka = $discount;
            if ($discountTaka <= 0 && $valueRaw > 0) {
                $discountTaka = $discountType === 'flat'
                    ? $valueRaw
                    : round($totalBill * $valueRaw / 100, 2);
            }

            $paidAmount = (float) $this->input('paid_amount', 0);
            $netPayable = max(0, $totalBill - $discountTaka);
            $min50 = ceil($totalBill * 0.5); // always on ORIGINAL total, never on net
            $referenceName = trim((string) $this->input('reference_name', ''));

            if ($paidAmount > $netPayable) {
                $validator->errors()->add(
                    'paid_amount',
                    'Paid amount cannot exceed net payable (৳ '.number_format($netPayable, 0).').'
                );
            }

            // Rule 4: ANY discount requires a reference — rejected without one,
            // regardless of how much was paid (even the full total).
            if ($discountTaka > 0 && $referenceName === '') {
                $validator->errors()->add(
                    'discount_value',
                    'Discount requires a Reference By name for admin approval.'
                );
            }

            // Rules 1–3: the 50%-of-total rule applies only when there is NO
            // discount (discount + reference always goes to pending).
            if ($discountTaka == 0 && $paidAmount < $min50 && $referenceName === '') {
                $validator->errors()->add(
                    'paid_amount',
                    'Paid amount must be at least 50% (৳ '.number_format($min50, 0).') of the total bill.'
                );
            }
        });
    }
}
