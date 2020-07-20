<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataHandlers\VatsimDataController;
use App\Models\Users\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MainController extends Controller
{
    public function index()
    {
        $sessions = app(VatsimDataController::class)->getATCSessions();
        $times = app(VatsimDataController::class)->getUserHours();

        return view('app.index', [
            'sessions' => $sessions,
            'atcTimes' => $times['atc'],
            'pilotTimes' => $times['pilot'],
        ]);
    }

    public function usersettings()
    {
        $useremail = Auth::user()->email;
        if (!is_null(Auth::user()->custom_email)) {
            $useremail = Auth::user()->custom_email." (custom)";
        }
        if (Auth::user()->subdiv_id == "FRA") {
            $utypes = config('vatfrance.usertypes');
        } else {
            $utypes = config('vatfrance.visiting_usertypes');
        }
        return view('app.usersettings', [
            'usertypes' => $utypes,
            'useremail' => $useremail,
        ]);
    }

    public function usersettingsedit(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'customemail' => ['email'],
        // ]);

        // if ($validator->fails()) {
        //     return redirect()->route('app.user.settings', app()->getLocale());
        // }

        $currentUser = User::where('id', $request->get('userid'))->firstOrFail();
        if (!is_null($request->get('customemail'))) {
            $currentUser->custom_email = $request->get('customemail');
            $currentUser->save();
        } else {
            $currentUser->custom_email = null;
            $currentUser->save();
        }
        switch (Auth::user()->subdiv_id) {
            case 'FRA':
                if (in_array($request->get('editusertype'), config('vatfrance.usertypes'))) {
                    $currentUser->account_type = $request->get('editusertype');
                    $currentUser->save();
                }
                break;
            
            default:
                if (in_array($request->get('editusertype'), config('vatfrance.visiting_usertypes'))) {
                    $currentUser->account_type = $request->get('editusertype');
                    $currentUser->save();
                }
                break;
        }

        return redirect()->route('app.user.settings', app()->getLocale())->with('toast-success', trans('app/alerts.settings_edited'));
    }
}
