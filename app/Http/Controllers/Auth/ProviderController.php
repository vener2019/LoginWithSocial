<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class ProviderController extends Controller
{
    public function redirect($provider){
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider){

        try {
            $users = Socialite::driver($provider)->user();
            
            if(User::where('email',$users->getEmail())->exists()){
                return redirect('/login')->withErrors(['email' => 'This email user different method to login']);
            }

            $user = User::where([
                'provider' => $provider,
                'provider_id' => $users->id
            ])->first();

            if(!$user){
                $user = User::create([
                    'name' => $users->name ? $users->name : $users->getNickname(),
                    'email' => $users->email ??null,
                    'username' => User::generateUserName($users->nickname),
                    'provider' => $provider,
                    'provider_id' => $users->id,
                    'provider_token' => $users->token,
                    'email_verified_at' => now(),
                ]);
            }
            
            Auth::login($user);
        
            return redirect('/dashboard');
        } catch (\Exception $e) {
            // Log or handle the exception
            dd($e->getMessage()); // Output the error message for debugging
        }
        
        
    }
}
