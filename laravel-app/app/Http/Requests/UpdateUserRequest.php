<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic user fields
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $this->route('username') . ',username',
            'job_title' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'social_urls' => 'sometimes|array',
            'bio' => 'sometimes|string|max:1000',
            'expertise' => 'sometimes|string|max:1000',
            'skills' => 'sometimes|string|max:1000',

            // Works validation
            'works' => 'sometimes|array',
            'works.*.id' => 'sometimes|integer|exists:works,id',
            'works.*._delete' => 'sometimes|boolean',
            'works.*.title' => 'sometimes|string|max:255',
            'works.*.description' => 'sometimes|string|max:1000',
            'works.*.url' => 'sometimes|url|max:500',

            // Clients validation
            'clients' => 'sometimes|array',
            'clients.*.id' => 'sometimes|integer|exists:clients,id',
            'clients.*._delete' => 'sometimes|boolean',
            'clients.*.name' => 'sometimes|string|max:255',
            'clients.*.introduction' => 'sometimes|string|max:500',
            'clients.*.photo_url' => 'sometimes|url|max:500',
            'clients.*.job_title' => 'sometimes|string|max:255',
            'clients.*.feedback' => 'sometimes|string|max:1000',

            // Client media validation
            'clients.*.media' => 'sometimes|array',
            'clients.*.media.*.id' => 'sometimes|integer|exists:client_media,id',
            'clients.*.media.*._delete' => 'sometimes|boolean',
            'clients.*.media.*.url' => 'sometimes|url|max:500',
            'clients.*.media.*.type' => 'sometimes|string|max:50',
            'clients.*.media.*.title' => 'sometimes|string|max:255',
            'clients.*.media.*.description' => 'sometimes|string|max:500',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate works
            if ($this->has('works')) {
                foreach ($this->input('works') as $index => $work) {
                    if (!isset($work['id']) && empty($work['title'])) {
                        $validator->errors()->add("works.{$index}.title", 'Work title is required when creating new work.');
                    }
                }
            }

            // Validate clients
            if ($this->has('clients')) {
                foreach ($this->input('clients') as $index => $client) {
                    // Check if new client has required name
                    if (!isset($client['id']) && empty($client['name'])) {
                        $validator->errors()->add("clients.{$index}.name", 'Client name is required when creating new client.');
                    }

                    // Validate client media if present
                    if (isset($client['media']) && is_array($client['media'])) {
                        foreach ($client['media'] as $mediaIndex => $media) {
                            if (!isset($media['id']) && empty($media['url'])) {
                                $validator->errors()->add("clients.{$index}.media.{$mediaIndex}.url", 'Media URL is required when creating new media.');
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already taken by another user.',
            'works.*.url.url' => 'Work URL must be a valid URL.',
            'clients.*.photo_url.url' => 'Client photo URL must be a valid URL.',
            'clients.*.media.*.url.url' => 'Media URL must be a valid URL.',
        ];
    }
}
