<?php

namespace GGuney\Rbac;

use Illuminate\Support\Facades\Cache;

trait RbacCompany
{
    protected $cachedModules;
    private $time = 60000; //seconds

    public function getModules()
    {
        $cacheKey = config('rbac.company_modules_cache_key');
        $cached = Cache::tags([$cacheKey])->get($this->id);
        if ($cached) {
            $this->cachedModules = Cache::tags([$cacheKey])->get($this->id);
        } else {
            $companyModules = $this->modules()->get();
            $this->cachedModules = $companyModules;
            Cache::tags([$cacheKey])->put($this->id, $companyModules, $this->time);
        }

        return $this->cachedModules;
    }

    public function forgetModules(){
        $cacheKey = config('rbac.company_modules_cache_key');
        Cache::tags([$cacheKey])->flush();
    }
}