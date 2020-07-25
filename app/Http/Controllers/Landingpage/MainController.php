<?php

namespace App\Http\Controllers\Landingpage;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataHandlers\VatsimDataController;
use App\Models\ATC\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MainController extends Controller
{
    public function index()
    {
        $bookingsToday = Booking::where('date', Carbon::now()->format('d.m.Y'))
        ->with('user')
        ->get();
        $bookingsTomorrow = Booking::where('date', Carbon::now()
        ->addDays(1)
        ->format('d.m.Y'))
        ->with('user')
        ->get();
        $bookingsAfterTomorrow = Booking::where('date', Carbon::now()
        ->addDays(2)
        ->format('d.m.Y'))
        ->with('user')
        ->get();
        $dayToday = Carbon::now()->format('D. d/m');
        $dayTomorrow = Carbon::now()->addDays(1)->format('D. d/m');
        $dayAfterTomorrow = Carbon::now()->addDays(2)->format('D. d/m');
        $onlineATC = app(VatsimDataController::class)->getOnlineATC();
        return view('landingpage.index', [
            'book0' => $bookingsToday,
            'book1' => $bookingsTomorrow,
            'book2' => $bookingsAfterTomorrow,
            'day0' => $dayToday,
            'day1' => $dayTomorrow,
            'day2' => $dayAfterTomorrow,
            'atconline' => $onlineATC,
        ]);
    }

    public function events()
    {
        // return view('landingpage.events');
        return redirect()->back()->with('toast-info', 'This page is not yet available');
    }

    public function contact()
    {
        return view('landingpage.contact');
    }

    public function reqatc()
    {
        return view('landingpage.reqatc');
    }

    public function policies()
    {
        return view('landingpage.statutes_policies');
    }
}
