<?php

namespace App\Http\Controllers\Landingpage;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataHandlers\VatsimDataController;
use App\Models\ATC\ATCRequest;
use App\Models\ATC\Booking;
use App\Models\General\ContactForm;
use App\Models\General\Event;
use App\Models\General\FeedbackForm;
use Godruoyi\Snowflake\Snowflake;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use DateTime;

class MainController extends Controller
{
    public function index()
    {
        $bookingsToday = Booking::whereDate('start_date', Carbon::now()->format('Y-m-d'))
        ->with('user')
        ->orderBy('start_date', 'ASC')
        ->get();
        $bookingsTomorrow = Booking::whereDate('start_date', Carbon::now()
        ->addDays(1)
        ->format('Y-m-d'))
        ->with('user')
        ->orderBy('start_date', 'ASC')
        ->get();
        $bookingsAfterTomorrow = Booking::whereDate('start_date', Carbon::now()
        ->addDays(2)
        ->format('Y-m-d'))
        ->with('user')
        ->orderBy('start_date', 'ASC')
        ->get();
        $bookingsDay3 = Booking::whereDate('start_date', Carbon::now()
        ->addDays(3)
        ->format('Y-m-d'))
        ->with('user')
        ->orderBy('start_date', 'ASC')
        ->get();
        $onlineATC = app(VatsimDataController::class)->getOnlineATC();
        $livemap = app(VatsimDataController::class)->livemapDataGenerator();

        $eventList = Event::orderBy('start_date', 'ASC')
        ->where('start_date', '>=', Carbon::now()->format('Y-m-d H:i:s'))
        ->where('start_date', '<=', Carbon::now()->addDays(7)->format('Y-m-d H:i:s'))
        ->get();

        return view('landingpage.index', [
            'book0' => $bookingsToday,
            'book1' => $bookingsTomorrow,
            'book2' => $bookingsAfterTomorrow,
            'book3' => $bookingsDay3,
            'atconline' => $onlineATC,
            'eventsList' => $eventList,
            'livemap' => $livemap,
        ]);
    }

    public function trainingPilote()
    {
        return redirect()->back()->with('toast-info', trans('app/alerts.page_unavailable'));
    }

    public function trainingATC()
    {
        return view('landingpage.atc.training_'.app()->getLocale());
    }

    public function visitingATC()
    {
        return redirect()->back()->with('toast-info', trans('app/alerts.page_unavailable'));
    }

    public function events()
    {
        // return view('landingpage.events');
        return redirect()->back()->with('toast-info', trans('app/alerts.page_unavailable'));
    }

    public function feedback()
    {
        return view('landingpage.contact.feedback');
        // return redirect()->back()->with('toast-info', trans('app/alerts.page_unavailable'));
    }

    public function feedbackForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'cid' => ['required'],
            'controller_cid' => ['required'],
            'date' => ['required'],
            'message' => ['required'],
        ]);

        if ($validator->fails()) {
            dd($validator->errors());
            return redirect()->back()->with('pop-error', trans('app/alerts.atc_req_fields_error'));
        }

        $newID = (new Snowflake)->id();
        FeedbackForm::create([
            'id' => $newID,
            'name' => request('name'),
            'vatsim_id' => request('cid'),
            'controller_cid' => request('controller_cid'),
            'message' => request('message'),
        ]);

        return redirect()->route('landingpage.home.contact', app()->getLocale())->with('pop-success', trans('app/alerts.feedback_success'));
    }

    public function contact()
    {
        return view('landingpage.contact.contact');
    }

    public function contactForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'cid' => ['required'],
            'email' => ['required', 'email'],
            'message' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.atc_req_fields_error'));
        }

        $newID = (new Snowflake)->id();        
        ContactForm::create([
            'id' => $newID,
            'name' => $request->get('name'),
            'vatsim_id' => $request->get('cid'),
            'email' => $request->get('email'),
            'message' => $request->get('message'),
        ]);

        return redirect()->route('landingpage.home.contact', app()->getLocale())->with('pop-success', trans('app/alerts.contact_success'));
    }

    public function reqatc()
    {
        return view('landingpage.contact.reqatc');
    }

    public function reqatcForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'cid' => ['required', 'integer'],
            'email' => ['required', 'email'],
            'event_name' => ['required'],
            'event_date' => ['required'],
            'sponsors' => ['required'],
            'website' => ['required'],
            'dep' => ['required'],
            'arr' => ['required'],
            'positions' => ['required'],
            'pilots' => ['required'],
            'route' => ['required'],
            'message' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.atc_req_fields_error'));
        }

        $newID = (new Snowflake)->id();
        ATCRequest::create([
            'id' => $newID,
            'name' => $request->get('name'),
            'vatsim_id' => $request->get('cid'),
            'email' => $request->get('email'),
            'event_name' => $request->get('event_name'),
            'event_date' => $request->get('event_date'),
            'event_sponsors' => $request->get('sponsors'),
            'event_website' => $request->get('website'),
            'dep_airport_and_time' => $request->get('dep'),
            'arr_airport_and_time' => $request->get('arr'),
            'requested_positions' => $request->get('positions'),
            'expected_pilots' => $request->get('pilots'),
            'route' => $request->get('route'),
            'message' => $request->get('message'),
        ]);

        return redirect()->route('landingpage.home.reqatc', app()->getLocale())->with('pop-success', trans('app/alerts.atcreq_success'));
    }

    public function policies()
    {
        return view('landingpage.statutes_policies');
    }
}
