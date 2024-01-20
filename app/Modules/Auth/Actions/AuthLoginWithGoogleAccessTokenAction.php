<?php

namespace App\Modules\Auth\Actions;

use App\Entities\User;
use App\Exceptions\UserException;
use App\Modules\Auth\DTO\AuthLoginWithGoogleAccessTokenDTO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthLoginWithGoogleAccessTokenAction
{
    public AuthLoginWithGoogleAccessTokenDTO $dto;
    public $body;
    public $user;
    public $token;

    /**
     * handle
     * @param AuthLoginWithGoogleAccessTokenDTO $dto
     */
    public function handle($dto)
    {
        $this->dto = $dto;

        $this->getUserInfo()
            ->validateEmail()
            ->generateTokenAndUpdateUserPicture();

        $roles = [];
        foreach ($this->user->roles as $role) {
            $roles[] = [
                'id' => data_get($role, 'id'),
                'name' => data_get($role, 'name'),
            ];
        }
        return [
            'userInfo' => [
                '_id' => $this->user->id,
                'email' => $this->user->email,
                'code' => $this->user->code,
                'name' => $this->user->name,
                'gender' => $this->user->gender,
                'notificationIds' => [],
                'picture' => $this->user->picture,
            ],
            'role' => $this->dto->type,
            'roles' => $roles,
            'accessToken' => $this->token,
        ];
    }

    private function getUserInfo()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->dto->access_token
        ])->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if ($response->status() != 200) {
            throw new UserException('Invalid access_token');
        }

        $this->body = json_decode($response->body());

        return $this;
    }

    private function validateEmail()
    {
        $email = data_get($this->body, 'email');

        $this->user = User::role($this->dto->type)->where('email', $email)->first();
        if (!$this->user) {
//            throw new UserException('User not found');
            throw new UserException('Email không tồn tại!', 400);
        }

        if ($this->user->status == User::STATUS_INACTIVE) {
//            throw new UserException('User is inactive');
            throw new UserException('Tài khoản đã bị khóa!', 400);
        }

        return $this;
    }

    private function generateTokenAndUpdateUserPicture()
    {
        $this->token = $this->user->generateToken([
            'email' => $this->user->email,
            'role' => $this->dto->type,
        ]);

        if ($picture = data_get($this->body, 'picture')) {
            $this->user->picture = $picture;
            Auth::login($this->user);
            $this->user->save();
        }
    }
}