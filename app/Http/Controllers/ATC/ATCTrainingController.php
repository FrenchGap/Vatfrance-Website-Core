<?php

namespace App\Http\Controllers\ATC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataHandlers\Utilities;
use App\Mail\Mentoring\NewRequestMail;
use App\Models\ATC\Airport;
use App\Models\ATC\ATCStudent;
use App\Models\ATC\MentoringRequest;
use App\Models\ATC\TrainingSession;
use App\Models\Users\User;
use Carbon\Carbon;
use Godruoyi\Snowflake\Snowflake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ATCTrainingController extends Controller
{
    public function index()
    {
        $activeStudent = ATCStudent::where('vatsim_id', auth()->user()->vatsim_id)->first();
        $existingRequest = MentoringRequest::where('student_id', auth()->user()->id)->first();
        if (!is_null($activeStudent)) {
            if ($activeStudent->active == true) {
                $studySessions = config('vaccfr.student_progress_'.app()->getLocale());
                $progSteps = 100/(int)count($studySessions);

                $sessions = TrainingSession::where('student_id', $activeStudent->id)
                ->with('mentorUser')
                ->get();

                $positions = Airport::orderBy('city', 'ASC')
                ->with(['positions' => function($q) {
                    $q->whereIn('solo_rank', app(Utilities::class)->getAuthedRanks(auth()->user()->atc_rating_short));
                }])
                ->get();

                if (!is_null($existingRequest)) {
                    $trainingPlatform = $existingRequest->icao;
                } else {
                    $trainingPlatform = "N/A";
                }

                $mentorObj = User::where('id', $activeStudent->mentor_id)->first();

                return view('app.atc.training', [
                    'steps' => $studySessions,
                    'progSteps' => $progSteps,
                    'student' => $activeStudent,
                    'sessions' => $sessions,
                    'positions' => $positions,
                    'mentorObj' => $mentorObj,
                    'sessionsCount' => count($sessions),
                    'trainingPlatform' => $trainingPlatform,
                ]);
            } else {
                $mRequest = MentoringRequest::where('student_id', auth()->user()->id)->first();
                return view('app.atc.training_req', [
                    'show' => "APPLIED",
                    'mRequest' => $mRequest,
                ]);
            }
        } else {
            if (auth()->user()->subdiv_id != "FRA") {
                return view('app.atc.training_req', [
                    'show' => "NOREGION",
                ]);
            }

            $platforms = Airport::orderBy('city', 'ASC')->get();
            return view('app.atc.training_req', [
                'platforms' => $platforms,
                'excl' => config('vaccfr.excluded_mentoring_airports'),
                'show' => "NORMAL",
            ]);
        }

        return view('app.atc.training_req', [
            'show' => "ERROR",
        ]);
    }

    public function mentoringRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reqposition' => ['required'],
            'reqmotivation' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('app.atc.training', app()->getLocale());
        } elseif (in_array($request->get('reqposition'), config('vaccfr.excluded_mentoring_airports'))) {
            return redirect()->route('app.atc.training', app()->getLocale());
        }

        $consent = true;
        if (is_null($request->get('reqallowmail'))) {
            $consent = false;
        }

        MentoringRequest::create([
            'id' => (new Snowflake)->id(),
            'student_id' => auth()->user()->id,
            'icao' => $request->get('reqposition'),
            'motivation' => $request->get('reqmotivation'),
            'mail_consent' => $consent,
        ]);

        ATCStudent::create([
            'id' => auth()->user()->id,
            'vatsim_id' => auth()->user()->vatsim_id,
        ]);

        // EMAIL_STUFF_TO_REPAIR
        // Mail::to(config('vaccfr.ATC_staff_email'))->send(new NewRequestMail([
        //     'sender' => auth()->user()->fullname()." - ".auth()->user()->vatsim_id,
        //     'body' => $request->get('reqmotivation'),
        // ]));

        return redirect()->route('app.atc.training', app()->getLocale())->with('pop-success', trans('app/alerts.success_application'));
    }

    public function requestSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mentorid' => ['required'],
            'reqposition' => ['required'],
            'sessiondate' => ['required', 'date_format:d.m.Y'],
            'starttime' => ['required', 'before:endtime', 'date_format:H:i'],
            'endtime' => ['required', 'after:starttime', 'date_format:H:i'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.session_req_error'));
        }

        // dd(htmlspecialchars($request->get('sessiondate')));

        TrainingSession::create([
            'id' => (new Snowflake)->id(),
            'student_id' => auth()->user()->id,
            'mentor_id' => $request->get('mentorid'),
            'position' => htmlspecialchars($request->get('reqposition')),
            'date' => htmlspecialchars($request->get('sessiondate')),
            'time' => htmlspecialchars($request->get('starttime')) . ' - ' . htmlspecialchars($request->get('endtime')),
            'start_time' => htmlspecialchars($request->get('starttime')),
            'end_time' => htmlspecialchars($request->get('endtime')),
            'requested_by' => 'Student ('.auth()->user()->fname.' '.auth()->user()->lname.')',
            'accepted_by_student' => true,
            'accepted_by_mentor' => false,
            'status' => 'Awaiting mentor approval',
            'student_comment' => htmlspecialchars($request->get('reqcomment')),
        ]);

        return redirect()->route('app.atc.training', app()->getLocale())->with('toast-success', trans('app/alerts.session_req_succ'));
    }

    public function acceptSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionid' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.error_occured'));
        }

        $session = TrainingSession::where('id', $request->get('sessionid'))->firstOrFail();
        $session->status = "Confirmed";
        $session->accepted_by_student = true;
        $session->save();

        return redirect()->route('app.atc.training', app()->getLocale())->with('toast-success', trans('app/alerts.session_accepted'));
    }

    public function cancelSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionid' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.error_occured'));
        }

        $session = TrainingSession::where('id', $request->get('sessionid'))->firstOrFail();
        $session->delete();

        return redirect()->route('app.atc.training', app()->getLocale())->with('toast-success', trans('app/alerts.session_cancelled'));
    }
}
