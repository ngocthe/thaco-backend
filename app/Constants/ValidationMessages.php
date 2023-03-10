<?php


namespace App\Constants;


class ValidationMessages
{
    const RULE_TO_CODE = [
        'accepted'                          => '10001',
        'accepted_if'                       => '10002',
        'active_url'                        => '10003',
        'after'                             => '10004',
        'after_or_equal'                    => '10005',
        'alpha'                             => '10006',
        'alpha_dash'                        => '10007',
        'alpha_num'                         => '10008',
        'array'                             => '10009',
        'before'                            => '10010',
        'before_or_equal'                   => '10011',
        'between_array'                     => '10012',
        'between_file'                      => '10013',
        'between_numeric'                   => '10014',
        'between_string'                    => '10015',
        'boolean'                           => '10016',
        'confirmed'                         => '10017',
        'current_password'                  => '10018',
        'date'                              => '10019',
        'date_equals'                       => '10020',
        'date_format'                       => '10021',
        'declined'                          => '10022',
        'declined_if'                       => '10023',
        'different'                         => '10024',
        'digits'                            => '10025',
        'digits_between'                    => '10026',
        'dimensions'                        => '10027',
        'distinct'                          => '10028',
        'email'                             => '10029',
        'ends_with'                         => '10030',
        'enum'                              => '10031',
        'exists'                            => '10032',
        'file'                              => '10033',
        'filled'                            => '10034',
        'gt_array'                          => '10035',
        'gt_file'                           => '10036',
        'gt_numeric'                        => '10037',
        'gt_string'                         => '10038',
        'gte_array'                         => '10039',
        'gte_file'                          => '10040',
        'gte_numeric'                       => '10041',
        'gte_string'                        => '10042',
        'image'                             => '10043',
        'in'                                => '10044',
        'in_array'                          => '10045',
        'integer'                           => '10046',
        'ip'                                => '10047',
        'ipv4'                              => '10048',
        'ipv6'                              => '10049',
        'json'                              => '10050',
        'lt_array'                          => '10051',
        'lt_file'                           => '10052',
        'lt_numeric'                        => '10053',
        'lt_string'                         => '10054',
        'lte_array'                         => '10055',
        'lte_file'                          => '10056',
        'lte_numeric'                       => '10057',
        'lte_string'                        => '10058',
        'mac_address'                       => '10059',
//        'max_array'                         => '10060',
//        'max_file'                          => '10061',
//        'max_numeric'                       => '10062',
//        'max_string'                        => '10063',
        'max'                               => '10063',
        'mimes'                             => '10064',
        'mimetypes'                         => '10065',
//        'min_array'                         => '10066',
//        'min_file'                          => '10067',
//        'min_numeric'                       => '10068',
//        'min_string'                        => '10069',
        'min'                               => '10069',
        'multiple_of'                       => '10070',
        'not_in'                            => '10071',
        'not_regex'                         => '10072',
        'numeric'                           => '10073',
        'present'                           => '10074',
        'prohibited'                        => '10075',
        'prohibited_if'                     => '10076',
        'prohibited_unless'                 => '10077',
        'prohibits'                         => '10078',
        'regex'                             => '10079',
        'required'                          => '10080',
        'required_array_keys'               => '10081',
        'required_if'                       => '10082',
        'required_unless'                   => '10083',
        'required_with'                     => '10084',
        'required_with_all'                 => '10085',
        'required_without'                  => '10086',
        'required_without_all'              => '10087',
        'same'                              => '10088',
        'size_array'                        => '10089',
        'size_file'                         => '10090',
        'size_numeric'                      => '10091',
        'size_string'                       => '10092',
        'starts_with'                       => '10093',
        'string'                            => '10094',
        'timezone'                          => '10095',
        'unique'                            => '10096',
        'uploaded'                          => '10097',
        'url'                               => '10098',
        'uuid'                              => '10099',
        'reset'                             => '10100',
        'sent'                              => '10101',
        'throttled'                         => '10102',
        'token'                             => '10103',
        'user'                              => '10104',
        'failed'                            => '10105',
        'password'                          => '10106',
        'throttle'                          => '10107',
        'remove_success'                    => '10108',
        'not_found_data'                    => '10109',
        'save_success'                      => '10110',
        'download_fail'                     => '10111',
        'confirm_delete'                    => '10112',
        'download_success'                  => '10113',
        'remove_failed'                     => '10114',
        '500'                               => '10115',
        'no_internet'                       => '10116',
        'record_exists'                     => '10117',
        'upload_failed'                     => '10118',
    ];

    const UNIQUE_CODE = '10096';
    const UNIQUE_MESSAGE = 'The :key has already been taken.';

    /**
     * @param $rule
     * @return string
     */
    public static function getMessageCode($rule): string
    {
        $rule = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $rule));
        return static::RULE_TO_CODE[$rule] ?? '-1';
    }

    /**
     * @param $attribute
     * @return string
     */
    public static function getMessageUnique($attribute): string
    {
        return str_replace(':key', str_replace('_', ' ', $attribute), self::UNIQUE_MESSAGE);
    }
}
