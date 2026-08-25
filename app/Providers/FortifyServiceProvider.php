<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Exceptions\ServiceException;
use App\Facades\AppService;
use App\Facades\AuthService;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LogoutResponse;
use Laravel\Fortify\Http\Responses\PasswordResetResponse;
use Laravel\Fortify\Http\Responses\PasswordUpdateResponse;
use Laravel\Fortify\Http\Responses\RegisterResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(LoginResponse::class, \App\Http\Responses\Auth\LoginResponse::class);
        $this->app->singleton(RegisterResponse::class, \App\Http\Responses\Auth\RegisterResponse::class);
        $this->app->singleton(LogoutResponse::class, \App\Http\Responses\Auth\LogoutResponse::class);
        $this->app->singleton(PasswordResetResponse::class, \App\Http\Responses\Auth\PasswordResetResponse::class);
        $this->app->singleton(PasswordUpdateResponse::class, \App\Http\Responses\Auth\PasswordUpdateResponse::class);

        Fortify::authenticateUsing(function (Request $request) {
            Validator::make($request->input(), [
                'login_type' => ['required', 'string', 'in:username,email,phone'],
                'username' => ['required', 'string'],
                'password' => ['required', 'string'],
                'country_code' => [
                    'nullable',
                    'string',
                    Rule::requiredIf($request->input('login_type') === 'phone'),
                    Rule::in(array_keys(AppService::getAllCountryCodes())),
                ],
            ])->validate();

            /** @var User $user */
            $user = User::where($request->input('login_type'), $request->input('username'))
                ->when($request->input('login_type') === 'phone', function (Builder $builder) use ($request) {
                    $builder->where('country_code', strtolower($request->input('country_code')));
                })
                ->first();

            if ($user && Hash::check($request->input('password'), $user->password)) {
                if ($request->input('token')) {
                    $content = AuthService::getOAuthLoginVerifyTokenContent($request->input('token'));
                    if (!is_null($content)) {
                        $driverId = data_get($content, 'driver_id');
                        $user->oauth()->updateOrCreate(['driver_id' => $driverId], $content);
                    } else {
                        throw new ServiceException('账号绑定失败，请重试');
                    }
                }

                $user->login_ip = AppService::getRequestIp($request);
                $user->save();

                return $user;
            }
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
