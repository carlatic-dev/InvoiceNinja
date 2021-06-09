<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Transformers;

use App\Models\Account;
use App\Models\Company;
use App\Models\CompanyToken;
use App\Models\CompanyUser;
use App\Models\User;

class CompanyUserTransformer extends EntityTransformer
{
    /**
     * @var array
     */
    protected $defaultIncludes = [
        // 'user',
     ];

    /**
     * @var array
     */
    protected $availableIncludes = [
        'user',
        'company',
        'token',
        'account',
    ];

    public function transform(CompanyUser $company_user)
    {
        $blank_obj = new \stdClass;

        return [
            'permissions' => $company_user->permissions ?: '',
            'notifications' => (object) $company_user->notifications ?: $blank_obj,
            'settings' => (object) $company_user->settings ?: $blank_obj,
            'is_owner' => (bool) $company_user->is_owner,
            'is_admin' => (bool) $company_user->is_admin,
            'is_locked' => (bool) $company_user->is_locked,
            'updated_at' => (int) $company_user->updated_at,
            'archived_at' => (int) $company_user->deleted_at,
            'created_at' => (int) $company_user->created_at,
            'permissions_updated_at' => (int) $company_user->permissions_updated_at,
            //'number_years_active' => (int) $company_user->number_years_active,
        ];
    }

    public function includeAccount(CompanyUser $company_user)
    {
        $transformer = new AccountTransformer($this->serializer);

        return $this->includeItem($company_user->account, $transformer, Account::class);
    }

    public function includeCompany(CompanyUser $company_user)
    {
        $transformer = new CompanyTransformer($this->serializer);

        return $this->includeItem($company_user->company, $transformer, Company::class);
    }

    public function includeUser(CompanyUser $company_user)
    {
        $transformer = new UserTransformer($this->serializer);
        $company_user->user->company_id = $company_user->company_id;

        return $this->includeItem($company_user->user, $transformer, User::class);
    }

    public function includeToken(CompanyUser $company_user)
    {
        $token = $company_user->tokens->where('company_id', $company_user->company_id)->where('user_id', $company_user->user_id)->first();

        $transformer = new CompanyTokenTransformer($this->serializer);

        return $this->includeItem($token, $transformer, CompanyToken::class);
    }
}