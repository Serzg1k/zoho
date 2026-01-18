<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZohoSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        // English comment: allow for now; add auth later if needed
        return true;
    }

    public function rules(): array
    {
        return [
            'deal_name' => ['required', 'string', 'max:255'],
            'deal_stage' => ['required', 'string', 'max:255'],
            'deal_pipeline' => ['nullable', 'string', 'max:255'],

            'account_name' => ['required', 'string', 'max:255'],
            'account_website' => ['nullable', 'url', 'max:255'],
            'account_phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        // English comment: customize messages if you want nicer UX
        return [
            'deal_name.required' => 'Deal name is required.',
            'deal_stage.required' => 'Deal stage is required.',
            'account_name.required' => 'Account name is required.',
            'account_website.url' => 'Website must be a valid URL.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // English comment: normalize data before validation
        $this->merge([
            'deal_name' => $this->input('deal_name') ? trim((string) $this->input('deal_name')) : null,
            'deal_stage' => $this->input('deal_stage') ? trim((string) $this->input('deal_stage')) : null,
            'deal_pipeline' => $this->input('deal_pipeline') ? trim((string) $this->input('deal_pipeline')) : null,

            'account_name' => $this->input('account_name') ? trim((string) $this->input('account_name')) : null,
            'account_website' => $this->input('account_website') ? trim((string) $this->input('account_website')) : null,
            'account_phone' => $this->input('account_phone') ? trim((string) $this->input('account_phone')) : null,
        ]);
    }
}
