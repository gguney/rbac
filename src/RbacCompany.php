<?php

namespace GGuney\Rbac;

use Illuminate\Support\Facades\Cache;

trait RbacCompany
{
    private $time = 60000; //seconds

    public function getModules()
    {
        $cacheKey = config('rbac.company_modules_cache_key', 'rbac_company_modules');
        $data = Cache::tags([$cacheKey])->get($this->id);
        if (!$data) {
            $data = $this->modules()->get();
            Cache::tags([$cacheKey])->put($this->id, $data, $this->time);
        }

        return $data;
    }

    public function syncModules($moduleIds)
    {
        $this->modules()->sync($moduleIds);
        $this->forgetModules();
    }

    public function forgetModules(){
        $cacheKey = config('rbac.company_modules_cache_key', 'rbac_company_modules');
        Cache::tags([$cacheKey])->flush();
    }
}