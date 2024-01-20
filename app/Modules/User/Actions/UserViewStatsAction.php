<?php

namespace App\Modules\User\Actions;

use App\Entities\Role;
use App\Entities\User;
use Illuminate\Support\Facades\DB;

class UserViewStatsAction
{
    public function handle()
    {
        $advisorUsers = User::role(Role::ROLE_LECTURER)
            ->whereHas('advisorTopics')
            ->get();

        $genderGroup = User::query()
            ->select([
                "gender",
                DB::raw('COUNT(id) as ttl'),
            ])
            ->groupBy('gender')
            ->get();

        $dataAdvisor = [];
        foreach ($advisorUsers as $advisorUser) {
            $dataAdvisor[] = [
                'name' => data_get($advisorUser, 'name'),
                'count' => $advisorUser->advisorTopics->count(),
            ];
        }

        $dataGender = [];
        foreach ($genderGroup as $gender) {
            $dataGender[] = [
                'name' => data_get($gender, 'gender'),
                'count' => data_get($gender, 'ttl'),
            ];
        }

        return [
            'advisor_stats' => $dataAdvisor,
            'gender_stats' => $dataGender,
        ];
    }
}