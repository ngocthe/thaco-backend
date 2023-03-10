<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Admin;
use App\Rules\NoBr;
use App\Rules\NoSpaces;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class AdminImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'user_code',
        'user_name',
        'company',
        'role_name',
        'password'
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'User Code',
        'name' => 'User Name',
        'company' => 'Company',
        'role' => 'Role Name',
        'password' => 'Password'
    ];

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @var string
     */
    protected string $uniqueAttributes = '';

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);
        if (isset($data['password']) && $data['password'] != '') {
            $data['password_default'] = false;
        } else {
            $data['password'] = config('env.PASSWORD_DEFAULT');
            $data['password_default'] = true;
        }

        $userData = [
            'code' => $data['user_code'],
            'username' => $data['user_code'],
            'name' => $data['user_name'],
            'company' => $data['company'],
            'password' => $data['password'],
            'password_default' => $data['password_default'],
            'role' => explode(',', $data['role_name'])
        ];

        $this->uniqueData[$index] = [$userData['code']];

        return $userData;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'alpha_num_dash_shift', 'unique:admins,username,NULL,username,deleted_at,NULL', 'min:4', 'max:7', new NoSpaces],
            'name' => ['required','max:255',new NoBr()],
            'password' => 'required|string|min:6|max:16',
            'company' => 'required|string|max:255',
            'role' => 'required|exists:roles,name',
        ];
    }

    /**
     * @return array
     */
    public function customValidationAttributes(): array
    {
        return self::MAP_HEADING_ROW;
    }

    /**
     * @param Collection $collection
     * @return void
     * @throws ValidationException
     */
    public function collection(Collection $collection)
    {
        $duplicate = ImportHelper::findDuplicateInMultidimensional($this->uniqueData);
        if (count($duplicate)) {
            ImportHelper::handleDuplicateError($duplicate, $this->uniqueAttributes, $this->failures);
        }

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $collection = $collection->toArray();
        $loggedId = auth()->id();
        $now = Carbon::now();
        foreach ($collection as $item) {
            $row = array_merge($item, [
                'password' => Hash::make($item['password']),
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $admin = Admin::query()->create($row);
            $admin->assignRole($item['role']);
        }
    }
}
