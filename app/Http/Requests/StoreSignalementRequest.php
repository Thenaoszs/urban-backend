<?php

namespace App\Http\Requests;

use App\Models\Signalement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreSignalementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', Rule::in(Signalement::TYPES)],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
            'images'      => ['nullable', 'array', 'max:2'],
            'images.*'    => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5 Mo max par image
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'        => 'Le type de signalement est requis.',
            'type.in'              => 'Le type de signalement est invalide.',
            'description.required' => 'La description est requise.',
            'description.min'      => 'La description doit contenir au moins 10 caractères.',
            'latitude.required'    => 'La latitude est requise.',
            'latitude.between'     => 'La latitude est invalide.',
            'longitude.required'   => 'La longitude est requise.',
            'longitude.between'    => 'La longitude est invalide.',
            'images.max'           => 'Maximum 2 photos autorisées.',
            'images.*.image'       => 'Le fichier doit être une image.',
            'images.*.mimes'       => 'Format accepté : JPEG, JPG, PNG ou WEBP.',
            'images.*.max'         => 'Chaque image ne doit pas dépasser 5 Mo.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Données invalides.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
