<?php

namespace App\Http\Requests;

use App\Constants\ValidationMessages;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest as LaravelFormRequest;

/**
 * Class BaseApiRequest
 * @package App\Http\Requests\API
 */
abstract class BaseApiRequest extends LaravelFormRequest
{

    /**
     * @var array
     */
    protected array $unique_fields = [];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The codes has already been taken.';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules(): array;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize(): bool;

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = [];
        $validatorFailed = $validator->failed();
        $message = $validator->errors()->first();
        foreach ($validatorFailed as $key => $failed) {
            $errors[$key] = [
                'code' => ValidationMessages::getMessageCode(array_key_first($failed)),
                'message' => $validator->errors()->get($key)[0]
            ];

            // return same error
            if (in_array($key, $this->unique_fields) && $errors[$key]['code'] === ValidationMessages::UNIQUE_CODE) {
                $message = $this->unique_error_message;
                foreach ($this->unique_fields as $field) {
                    $errors[$field] = [
                        'code' => $errors[$key]['code'],
                        'message' => ValidationMessages::getMessageUnique($field)
                    ];
                }
            }
        }

        throw new HttpResponseException(response()->json(
            [
                'status' => false,
                'message' => $message,
                'data' => $errors
            ], 400));
    }
}
