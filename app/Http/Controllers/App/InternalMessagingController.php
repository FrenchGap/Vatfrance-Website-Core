<?php

namespace App\Http\Controllers\App;

use App\Events\EventNewInternalMessage;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DataHandlers\Utilities;
use App\Models\Admin\Staff;
use App\Models\ATC\ATCStudent;
use App\Models\ATC\Mentor;
use App\Models\General\InternalMessage;
use App\Models\Users\User;
use Godruoyi\Snowflake\Snowflake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InternalMessagingController extends Controller
{
    protected function recipientsListMaker()
    {
        $recipientList = [];
        $student = ATCStudent::where('id', auth()->user()->id)->first();
        if (!is_null($student)) {
            if (!is_null($student->mentor_id)) {
                $mentor = Mentor::where('id', $student->mentor_id)->first();
                array_push($recipientList, [
                    'verbose' => 'Mentor: '.$mentor->user['fname'].' '.$mentor->user['lname'],
                    'value' => $mentor->id,
                ]);
            }
        }
        $allStaff = Staff::get();
        foreach ($allStaff as $s) {
            $title = $s['title'];
            if (is_null($title)) {
                $title = "N/A";
            }
            array_push($recipientList, [
                'verbose' => 'Staff: ' . $s->user['fname'] . ' ' . $s->user['lname'] . ' (' . $title . ')',
                'value' => $s->id,
            ]);
        }
        return $recipientList;
    }

    public function inbox()
    {
        $inbox = InternalMessage::orderBy('created_at', 'DESC')
        ->where('recipient_id', auth()->user()->id)
        ->with('recipient')
        ->with('sender')
        ->where('recipient_archived', false)
        ->where('recipient_trashed', false)
        ->get();
        return view('app.messaging.inbox', [
            'recipientList' => $this->recipientsListMaker(),
            'header' => 'Messages',
            'display' => $inbox,
        ]);
    }

    public function sent()
    {
        $inbox = InternalMessage::orderBy('created_at', 'DESC')
        ->where('sender_id', auth()->user()->id)
        ->with('recipient')
        ->with('sender')
        ->get();
        return view('app.messaging.inbox', [
            'recipientList' => $this->recipientsListMaker(),
            'header' => 'Sent',
            'display' => $inbox,
        ]);
    }

    public function archive()
    {
        $inbox = InternalMessage::orderBy('created_at', 'DESC')
        ->where('recipient_id', auth()->user()->id)
        ->with('recipient')
        ->with('sender')
        ->where('recipient_archived', true)
        ->where('recipient_trashed', false)
        ->get();
        return view('app.messaging.inbox', [
            'recipientList' => $this->recipientsListMaker(),
            'header' => 'Archive',
            'display' => $inbox,
        ]);
    }

    public function trash()
    {
        $inbox = InternalMessage::orderBy('created_at', 'DESC')
        ->where('recipient_id', auth()->user()->id)
        ->with('recipient')
        ->with('sender')
        ->where('recipient_archived', false)
        ->where('recipient_trashed', true)
        ->get();
        return view('app.messaging.inbox', [
            'recipientList' => $this->recipientsListMaker(),
            'header' => 'Trash',
            'display' => $inbox,
        ]);
    }

    public function read(Request $request)
    {
        $msg = InternalMessage::where('id', $request->get('msgid'))
        ->with('sender')
        ->with('recipient')
        ->first();
        if (!is_null($msg)) {

        }
        if (is_null($msg)) {
            return redirect()->route('app.inmsg.inbox', app()->getLocale())->with('pop-error', trans('app/alerts.msg_not_found'));
        }
        $concerned = [
            $msg->recipient_id,
            $msg->sender_id,
        ];
        if (!in_array(auth()->user()->id, $concerned)) {
            return redirect()->back();
        }
        if ($msg->read == false) {
            $msg->read = true;
            $msg->save();
        }
        return view('app.messaging.read', [
            'recipientList' => $this->recipientsListMaker(),
            'msg' => $msg,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'msgsubject' => ['required', 'max:150'],
            'msgbody' => ['required'],
            'msgrecipient' => ['required'],
        ]);

        $recipient = User::where('id', $request->get('msgrecipient'))->first();
        if ($validator->fails() || is_null($recipient)) {
            return redirect()->back()->with('pop-error', trans('app/alerts.err_not_sent'));
        }

        $formattedBody = nl2br($request->get('msgbody'));

        $newmsgid = (new Snowflake)->id();
        InternalMessage::create([
            'id' => $newmsgid,
            'sender_id' => auth()->user()->id,
            'recipient_id' => $request->get('msgrecipient'),
            'subject' => $request->get('msgsubject'),
            'body' => $formattedBody,
        ]);

        if ((new Utilities)->checkEmailPreference(auth()->user()->id, 'internal_message') == true) {
            event(new EventNewInternalMessage($recipient, [
                'subject' => $request->get('msgsubject'),
                'sender' => auth()->user()->fname." ".auth()->user()->lname,
                'body' => $formattedBody,
                'id' => $newmsgid,
            ]));
        }

        return redirect()->back()->with('toast-success', trans('app/alerts.msg_sent'));
    }

    public function sendReply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'msgsubject' => ['required', 'max:150'],
            'msgbody' => ['required'],
            'msgrecipient' => ['required'],
            'prevmsg_date' => ['required'],
            'prevmsg_subject' => ['required'],
            'prevmsg_body' => ['required'],
            'prevmsg_fname' => ['required'],
            'prevmsg_lname' => ['required'],
        ]);

        $recipient = User::where('id', $request->get('msgrecipient'))->first();
        if ($validator->fails() || is_null($recipient)) {
            return redirect()->back()->with('pop-error', trans('app/alerts.err_reply_not_sent'));
        }

        $formattedBody = nl2br($request->get('msgbody'));
        $replyText = "<i><b>Date:</b> ".$request->get('prevmsg_date').
        "<br><b>Subject:</b> ".$request->get('prevmsg_subject').
        "<br><b>From:</b> ".$request->get('prevmsg_fname')." ".$request->get('prevmsg_lname').
        "<br><br>".$request->get('prevmsg_body').
        "</i><br>==========<br>".$formattedBody;

        $newmsgid = (new Snowflake)->id();
        InternalMessage::create([
            'id' => $newmsgid,
            'sender_id' => auth()->user()->id,
            'recipient_id' => $request->get('msgrecipient'),
            'subject' => $request->get('msgsubject'),
            'body' => $replyText,
        ]);

        if ((new Utilities)->checkEmailPreference(auth()->user()->id, 'internal_message') == true) {
            event(new EventNewInternalMessage($recipient, [
                'subject' => $request->get('msgsubject'),
                'sender' => auth()->user()->fname." ".auth()->user()->lname,
                'body' => $replyText,
                'id' => $newmsgid,
            ]));
        }

        return redirect()->back()->with('toast-success', trans('app/alerts.reply_sent'));
    }

    public function archiveMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'msgid' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.err_archive'));
        } else {
            $msg = InternalMessage::where('id', $request->get('msgid'))->first();
            if (is_null($msg)) {
                return redirect()->back()->with('pop-error', trans('app/alerts.err_archive'));
            } elseif (!$msg->recipient_id == auth()->user()->id) {
                return redirect()->back();
            }
        }

        $msg->recipient_archived = true;
        $msg->save();

        return redirect()->back()->with('toast-success', trans('app/alerts.msg_archived'));
    }

    public function deleteMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'msgid' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('pop-error', trans('app/alerts.err_del'));
        } else {
            $msg = InternalMessage::where('id', $request->get('msgid'))->first();
            if (is_null($msg)) {
                return redirect()->back()->with('pop-error', trans('app/alerts.err_del'));
            } elseif (!$msg->recipient_id == auth()->user()->id) {
                return redirect()->back();
            }
        }

        $msg->recipient_trashed = true;
        $msg->save();

        return redirect()->back()->with('toast-success', trans('app/alerts.msg_del'));
    }
}
