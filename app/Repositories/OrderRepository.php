<?php

namespace App\Repositories;

use App\Models\AppraisalDetail;
use App\Models\AppraisalType;
use App\Models\BorrowerInfo;
use App\Models\Client;
use App\Models\CompanyUser;
use App\Models\ContactInfo;
use App\Models\LoanType;
use App\Models\Order;
use App\Models\PropertyInfo;
use App\Models\ProvidedService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use JetBrains\PhpStorm\NoReturn;
use Spatie\Permission\Models\Role;

class OrderRepository extends BaseRepository
{
    protected object $company;
    protected object $role;
    protected object $users;

    public function __construct(Order $model)
    {
        parent::__construct($model);
        $this->users = collect();
    }

    /**
     * @param string $role
     *
     * @return Collection|\Illuminate\Support\Collection|array
     */
    public function getUserByRoleWise(string $role): Collection|\Illuminate\Support\Collection|array
    {
        $this->company = $this->getAuthUserCompany();
        $role = $this->getRoleByName($role);
        if ($role) {
            $this->users = $this->getCompanyUsers($role);
        }


        return $this->users;
    }

    /**
     * @param object $role
     *
     * @return Builder[]|Collection
     */
    #[NoReturn] public function getCompanyUsers(object $role): Collection|array
    {
        $company_user_ids = CompanyUser::query()?->where([
            ['company_id', '=', $this->company->id],
            ['role_id', $role->id],
            ['status', 1]
        ])->pluck('user_id');

        return User::query()->whereIn('id', $company_user_ids)->get(['id', 'name', 'email']);
    }

    /**
     * @return mixed
     */
    public function getAuthUserCompany(): mixed
    {
        return auth()->user()->companies()->first();
    }

    /**
     * @param string $name
     *
     * @return Model|Builder|null
     */
    public function getRoleByName(string $name): Model|Builder|null
    {
        return Role::query()->where('name', $name)->first();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getAppraisalTypes(): Collection|array
    {
        return AppraisalType::query()->where('company_id', $this->company->id)->get();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getLoanTypes(): Collection|array
    {
        return LoanType::query()->where('company_id', $this->company->id)->get();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getClients(): Collection|array
    {
        return Client::query()->where('company_id', $this->company->id)->get();
    }

    /**
     * @param $order_id
     * @return Builder|Model
     */
    public function getAppraisalDetails($order_id): Builder|Model
    {
        return AppraisalDetail::query()->with([
            'appraiser' => function ($query) {
                $query->select('id', 'name');
            }, 'loantype' => function ($query) {
                $query->select('id', 'name');
            }])->where('order_id', $order_id)->first();
    }

    /**
     * @param $order_id
     * @return Builder|Model
     */
    public function getProvidedService($order_id): Builder|Model
    {
        return ProvidedService::query()->where('order_id', $order_id)->first();
    }

    /**
     * @param $order_id
     * @return Builder|Model
     */
    public function getPropertyInfo($order_id): Builder|Model
    {
        return PropertyInfo::query()->where('order_id', $order_id)->first();
    }

    /**
     * @param $order_id
     * @param $data
     * @return bool
     */
    public function updatePropertyInfo($order_id, $data): bool
    {
        $property_info = PropertyInfo::query()->where('order_id', $order_id)->update([
            "search_address" => $data["search_address"],
            "street_name" => $data["street_name"],
            "city_name" => $data["city_name"],
            "state_name" => $data["state_name"],
            "zip" => $data["zip"],
            "country" => $data["country"],
            "unit_no" => $data["unit_no"]
        ]);
        $appraisal_details = AppraisalDetail::query()->where('order_id', $order_id)->update([
            "client_order_no" => $data["order_no"],
            "received_date" => Carbon::parse($data["received_date"])->format('Y-m-d'),
            "due_date" => Carbon::parse($data["due_date"])->format('Y-m-d')
        ]);
        return ($property_info && $appraisal_details);
    }

    /**
     * @param $order_id
     * @param $data
     * @return bool
     */
    public function updateAppraisalDetails($order_id,$data): bool
    {
        return AppraisalDetail::query()->where('order_id',$order_id)
            ->update($data);
    }

    /**
     * @param $order_id
     * @return Builder|Model
     */
    public function getBorrowerDetails($order_id): Builder|Model
    {
        return BorrowerInfo::query()->where('order_id',$order_id)->first();
    }

    public function getContactDetails($order_id): Builder|Model
    {
        return ContactInfo::query()->where('order_id',$order_id)->first();
    }

    /**
     * @param $order_id
     * @return Builder|Model
     */
    public function getClientDetails($order_id): Builder|Model
    {

        return Order::with('amc')->first();
//        $order = Order::with([
//            'amc','lender' => function($query){
//                return $query->select('id','name');
//            }])->where('id',$order_id)->first();
//        dd(['order_details' => $order]);
//        return Order::query()->where('id',$order_id)->with([
//            'amc'=> function($query){
//                return $query->select('id','name');
//            },'lender' => function($query){
//                return $query->select('id','name');
//            }])->first();
    }

}
