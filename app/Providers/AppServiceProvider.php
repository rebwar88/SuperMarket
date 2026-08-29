<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ئەگەر بەکارهێنەر خاوەنی ڕۆڵی ئەدمین بوو یان لە داتابەیس هەموو مۆڵەتەکانی هەبوو، بێ کۆتوبەند دەتوانێت هەموو بەشەکان بکاتەوە
        Gate::before(function ($user, $ability) {
            $hasAdminRole = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_uuid', $user->id)
                ->whereIn(DB::raw('LOWER(roles.name)'), ['admin', 'super admin', 'super_admin', 'owner'])
                ->exists();

            if ($hasAdminRole) {
                return true;
            }

            return null;
        });
    }
}
