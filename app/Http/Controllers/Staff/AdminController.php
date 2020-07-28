<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Admin\Staff;
use App\Models\ATC\ATCRosterMember;
use App\Models\ATC\ATCStudent;
use App\Models\ATC\Booking;
use App\Models\ATC\Mentor;
use App\Models\ATC\MentoringRequest;
use App\Models\ATC\SoloApproval;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {
        $members = User::get();
        $memberCount = count($members);
        $atcCount = count(ATCRosterMember::get());
        $bookingCount = count(Booking::where('date', Carbon::now()->format('d.m.Y'))->with('user')->get());
        return view('app.staff.admin', [
            'members' => $members,
            'memberCount' => $memberCount,
            'atcCount' => $atcCount,
            'bookingsCount' => $bookingCount,
            'locale' => app()->getLocale(),
        ]);
    }

    public function editUser(Request $request)
    {
        $user = User::where('id', $request->get('userid'))->firstOrFail();

        if ($user->subdiv_id == "FRA") {
            $utypes = config('vatfrance.usertypes');
        } else {
            $utypes = config('vatfrance.visiting_usertypes');
        }

        $ranks = [];
        foreach (array_keys(config('vatfrance.atc_ranks')) as $r) {
            if ((int)$user->atc_rating >= (int)$r) {
                array_push($ranks, config('vatfrance.atc_ranks')[$r]);
            }
        }
        $currentMentorRank = Mentor::where('id', $user->id)->first();
        if (!is_null($currentMentorRank)) {
            $currentMentorRank = $currentMentorRank->allowed_rank;
        }

        if (Auth::user()->isAdmin() == true) {
            $staffData = Staff::where('id', $user->id)->first();
        } else {
            $staffData = null;
        }

        return view('app.staff.admin_edit', [
            'user' => $user,
            'staff' => $staffData,
            'usertypes' => $utypes,
            'mentoring_ranks' => $ranks,
            'curr_mentor_rank' => $currentMentorRank,
        ]);
    }

    public function editUserFormDetails(Request $request)
    {
        $currentUser = User::where('id', $request->get('userid'))->firstOrFail();

        switch ($currentUser->is_approved_atc) {
            case true:
                if (is_null($request->get('approveatc'))) {
                    $currentUser->is_approved_atc = false;
                    $currentUser->save();
                    $userATCRoster = ATCRosterMember::where('id', $request->get('userid'))->first();
                    if (!is_null($userATCRoster)) {
                        $userATCRoster->approved_flag = false;
                        $userATCRoster->save();
                    }
                }
                break;
            
            case false:
                if (!is_null($request->get('approveatc'))) {
                    $currentUser->is_approved_atc = true;
                    $currentUser->save();
                    $userATCRoster = ATCRosterMember::where('id', $request->get('userid'))->first();
                    if (!is_null($userATCRoster)) {
                        $userATCRoster->approved_flag = true;
                        $userATCRoster->save();
                    }
                }
                break;
            
            default:
                break;
        }

        switch ($currentUser->subdiv_id) {
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

        return redirect()->route('app.staff.admin.edit', [
            'locale' => app()->getLocale(),
            'userid' => $currentUser->id,
        ])->with('toast-info', trans('app/alerts.details_edited'));
    }

    public function editUserAtcMentor(Request $request)
    {
        $currentUser = User::where('id', $request->get('userid'))->firstOrFail();
        $currentMentor = Mentor::where('id', $request->get('userid'))->first();

        switch ($currentUser->isAtcMentor()) {
            case false:
                if (!is_null($request->get('atcmentorswitch'))) {
                    Mentor::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'allowed_rank' => $request->get('allowedrank'),
                    ]);
                    Staff::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'atc_dpt' => 1,
                    ]);
                    $currentUser->is_staff = true;
                    $currentUser->save();
                }
                break;
            
            case true:
                if (is_null($request->get('atcmentorswitch'))) {
                    $todel = Mentor::where('vatsim_id', $currentUser->vatsim_id)->firstOrFail();
                    $todel->delete();
                    $currentUser->save();
                    Staff::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'atc_dpt' => 0,
                    ]);
                } elseif ($currentMentor->allowed_rank !== $request->get('allowedrank')) {
                    Mentor::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'allowed_rank' => $request->get('allowedrank'),
                    ]);
                }
                break;
            
            default:
                break;
        }

        return redirect()->route('app.staff.admin.edit', [
            'locale' => app()->getLocale(),
            'userid' => $currentUser->id,
        ])->with('toast-info', trans('app/alerts.atc_mentor_edited'));
    }

    public function editUserFormStaff(Request $request)
    {
        $currentUser = User::where('id', $request->get('userid'))->firstOrFail();

        // Edit Staff Status
        switch ($currentUser->is_staff) {
            case false:
                if (!is_null($request->get('staffswitch'))) {
                    Staff::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'staff_level' => 0,
                    ]);
                    $currentUser->is_staff = true;
                    $currentUser->save();
                }
                break;
            
            case true:
                if (is_null($request->get('staffswitch'))) {
                    $todel = Staff::where('vatsim_id', $currentUser->vatsim_id)->firstOrFail();
                    $todel->delete();
                    $currentUser->is_staff = false;
                    $currentUser->save();

                    $todelMentor = Mentor::where('id', $request->get('userid'))->first();
                    if (!is_null($todelMentor)) {
                        $todelMentor->delete();
                    }
                    return redirect()->route('app.staff.admin.edit', [
                        'locale' => app()->getLocale(),
                        'userid' => $currentUser->id,
                    ])->with('toast-info', trans('app/alerts.staff_edited'));
                }
                break;
            
            default:
                break;
        }

        switch ($currentUser->isExecStaff()) {
            case false:
                if (!is_null($request->get('execswitch'))) {
                    Staff::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'executive' => 1,
                    ]);
                }
                break;
            
            case true:
                if (is_null($request->get('execswitch'))) {
                    Staff::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'executive' => 0,
                    ]);
                }
                break;
            
            default:
                break;
        }

        switch ($currentUser->isAdmin()) {
            case false:
                if (!is_null($request->get('adminswitch'))) {
                    Staff::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'admin' => 1,
                    ]);
                }
                break;
            
            case true:
                if (is_null($request->get('adminswitch'))) {
                    Staff::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
                        'id' => $currentUser->id,
                        'admin' => 0,
                    ]);
                }
                break;
            
            default:
                break;
        }

        // Staff::updateOrCreate(['vatsim_id' => $currentUser->vatsim_id], [
        //     'id' => $currentUser->id,
        //     'title' => 
        // ]);

        $staff = Staff::where('id', $currentUser->id)->first();
        $staff->title = $request->get('stafftitle');
        $staff->save();

        return redirect()->route('app.staff.admin.edit', [
            'locale' => app()->getLocale(),
            'userid' => $currentUser->id,
        ])->with('toast-info', trans('app/alerts.staff_edited'));
    }

    public function atcAdmin()
    {
        $roster = ATCRosterMember::get();
        $soloApproved = SoloApproval::orderBy('end_date', 'ASC')
        ->with('user')
        ->with('mentor.user')
        ->with('station')
        ->get();
        $applications = MentoringRequest::orderBy('created_at', 'DESC')
        ->with('user')
        ->with('mentor')
        ->with(['mentor.user' => function($query) {
            $query->select('id', 'vatsim_id', 'fname', 'lname');
        }])
        ->get();
        return view('app.staff.atc_admin', [
            'rosterCount' => count($roster),
            'approvedRosterCount' => count($roster->where('approved_flag', true)),
            'roster' => $roster,
            'soloApproved' => $soloApproved,
            'apps' => $applications,
        ]);
    }

    public function approveSpecialPosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position' => ['required'],
            'userid' => ['required'],
        ]);

        $approvals = [
            'lfpg_twr',
            'lfpg_app',
            'lfmn_twr',
            'lfmn_app',
        ];

        if ($validator->fails() or !in_array($request->get('position'), $approvals)) {
            return redirect()->back()->with('pop-error', trans('app/alerts.error_occured'));
        }

        switch ($request->get('position')) {
            case 'lfpg_twr':
                $user = ATCRosterMember::where('id', $request->get('userid'))->firstOrFail();
                $user->appr_lfpg_twr = !$user->appr_lfpg_twr;
                $user->save();
                break;
            
            case 'lfpg_app':
                $user = ATCRosterMember::where('id', $request->get('userid'))->firstOrFail();
                $user->appr_lfpg_app = !$user->appr_lfpg_app;
                $user->save();
                break;
            
            case 'lfmn_twr':
                $user = ATCRosterMember::where('id', $request->get('userid'))->firstOrFail();
                $user->appr_lfmn_twr = !$user->appr_lfmn_twr;
                $user->save();
                break;
            
            case 'lfmn_app':
                $user = ATCRosterMember::where('id', $request->get('userid'))->firstOrFail();
                $user->appr_lfmn_app = !$user->appr_lfmn_app;
                $user->save();
                break;
            
            default:
                return redirect()->back()->with('pop-error', trans('app/alerts.error_occured'));
                break;
        }

        return redirect()->route('app.staff.atcadmin', app()->getLocale())->with('toast-success', trans('app/alerts.mod_atcappr_success', ['FNAME' => $user->fname]));
    }

    public function delSolo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'soloid' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.error_occured'));
        }

        $soloSession = SoloApproval::where('id', $request->get('soloid'))->delete();

        return redirect()->route('app.staff.atcadmin', app()->getLocale())->with('toast-success', trans('app/alerts.solo_deleted'));
    }

    public function delApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appid' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.error_occured'));
        }

        $request = MentoringRequest::where('id', $request->get('appid'))->firstOrFail();
        $stuId = $request->student_id;
        $request->delete();
        $student = ATCStudent::where('id', $stuId)->firstOrFail();
        $student->delete();

        return redirect()->route('app.staff.atcadmin', app()->getLocale())->with('toast-success', trans('app/alerts.mentoring_deleted'));
    }
}
