<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    public function login()
    {
        // session()->put('state', $state = Str::random(40));
        session()->forget('token');

        $query = http_build_query([
            'client_id' => config('vatsimsso.client_id'),
            'redirect_uri' => config('vatsimsso.redirect'),
            'response_type' => 'code',
            'scope' => 'full_name vatsim_details email',
        ]);
        
        return redirect(config('vatsimsso.url')."?".$query);
    }

    public function validateLogin(Request $request)
    {
        try {
            $response = (new Client)->post('https://auth-dev.vatsim.net/oauth/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('vatsimsso.client_id'),
                    'client_secret' => config('vatsimsso.secret'),
                    'redirect_uri' => config('vatsimsso.redirect'),
                    'code' => $request->code,
                ],
            ]);
        } catch(ClientException $e) {
            dd($e);
            return redirect()->route('landingpage.home');
        }

        session()->put('token', json_decode((string) $response->getBody(), true));

        try {
            $response = (new Client)->get('https://auth-dev.vatsim.net/api/user', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.session()->get('token.access_token')
                ]
            ]);
        } catch(ClientException $e){
            return redirect('/');
        }
        
        $response = json_decode($response->getBody());
        // dd($response);
        User::updateOrCreate(['vatsim_id' => $response->data->cid], [
            'email' => isset($response->data->personal->email) ? $response->data->personal->email : 'noemail@vatfrance.org',
            'fname' => isset($response->data->personal->name_first) ? $response->data->personal->name_first : null,
            'lname' => isset($response->data->personal->name_last) ? $response->data->personal->name_last : null,
            'atc_rating' => $response->data->vatsim->rating->id,
            'atc_rating_short' => $response->data->vatsim->rating->short,
            'atc_rating_long' => $response->data->vatsim->rating->long,
            'pilot_rating' => $response->data->vatsim->pilotrating->id,
            'region_id' => $response->data->vatsim->region->id,
            'region_name' => $response->data->vatsim->region->name,
            'division_id' => $response->data->vatsim->division->id,
            'division_name' => $response->data->vatsim->division->name,
            'subdiv_id' => $response->data->vatsim->subdivision->id,
            'subdiv_name' => $response->data->vatsim->subdivision->name,
        ]);

        $user = User::where('vatsim_id', $response->data->cid)->first();
        Auth::login($user, true);

        return redirect('/');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
