<?php

namespace App\Http\Requests\Tracks;

use Illuminate\Foundation\Http\FormRequest;

class ImportSpotifyTrackRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'string',
                'max:255',
                'regex:/^(https?:\/\/)?(open\.spotify\.com\/track\/|spotify:track:)[a-zA-Z0-9]+/i',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.regex' => 'The URL must be a valid Spotify track link.',
        ];
    }
}
