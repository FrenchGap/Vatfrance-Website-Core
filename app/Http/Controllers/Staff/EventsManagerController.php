<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataHandlers\DiscordAnnouncer;
use App\Models\Data\File;
use App\Models\General\Event;
use DateTime;
use Godruoyi\Snowflake\Snowflake;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventsManagerController extends Controller
{
    public function dashboard()
    {
        $eventsList = Event::where('date', '>=', Carbon::now()->format('d.m.Y'))
        ->orderBy('created_at', 'DESC')
        ->get();
        return view('app.staff.events_dashboard', [
            'events2come' => $eventsList,
            'eventCount' => count($eventsList),
        ]);
    }

    public function newEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required'],
            'description' => ['required'],
            'date' => ['required', 'date_format:d.m.Y'],
            'starttime' => ['required', 'before:endtime', 'date_format:H:i'],
            'endtime' => ['required', 'after:starttime', 'date_format:H:i'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-error', 'Error occured');
        }

        $hasImgBool = false;

        if ($request->hasFile('event_img')) {
            if ($request->file('event_img')->isValid()) {
                $imgValidate = Validator::make($request->all(), [
                    'event_img' => ['mimes:jpeg,png,jpg', 'max:5120']
                ]);
                if ($imgValidate->fails()) {
                    return redirect()->back()->with('toast-error', 'Image is invalid.');
                } else {
                    $imgName = Str::random(50);
                    $imgExtension = $request->event_img->extension();
                    $request->event_img->storeAs('/public/event_images', $imgName.".".$imgExtension);
                    $imgUrl = Storage::url('event_images/'.$imgName.'.'.$imgExtension);

                    $imgID = (new Snowflake)->id();
                    $file = File::create([
                        'id' => $imgID,
                        'name' => $imgName.'.'.$imgExtension,
                        'url' => $imgUrl,
                    ]);
                    $hasImgBool = true;
                }
            }
        } else {
            $imgUrl = null;
            $imgID = null;
        }

        if ($request->has('eventurl') && !is_null(request('eventurl'))) {
            $url = request('eventurl');
        } else {
            $url = "#";
        }

        $date = date_create_from_format('d.m.Y H:i', request('date').' '.request('starttime'));
        $timestamp = $date->format(DateTime::ATOM);

        $dmsgid = app(DiscordAnnouncer::class)->sendAnnouncement(
            request('title'), $url, $imgUrl, request('description'), $request->user()->fname." ".$request->user()->lname, request('date'), request('starttime'), request('endtime'), $timestamp
        );

        $event = Event::create([
            'id' => (new Snowflake)->id(),
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'date' => $request->get('date'),
            'url' => $url,
            'start_time' => $request->get('starttime'),
            'end_time' => $request->get('endtime'),
            'has_image' => $hasImgBool,
            'image_id' => $imgID,
            'image_url' => $imgUrl,
            'publisher_id' => $request->user()->id,
            'discord_msg_id' => $dmsgid,
        ]);

        return redirect()->route('app.staff.events.dashboard', app()->getLocale())->with('pop-success', 'New event registered!');
    }

    public function delEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eventid' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-error', 'Error occured');
        }

        $event = Event::where('id', $request->get('eventid'))->first();
        if (is_null($event)) {
            return redirect()->back()->with('toast-error', 'Event was not found and could not be deleted');
        }

        $event->delete();
        return redirect()->route('app.staff.events.dashboard', app()->getLocale())->with('toast-info', 'Event deleted');
    }

    public function editEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eventid' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('toast-error', 'Error occured');
        }

        $event = Event::where('id', $request->get('eventid'))->first();
        if (is_null($event)) {
            return redirect()->back()->with('toast-error', 'Event was not found and could not be edited');
        }

        $event->title = $request->get('edittitle');
        $event->description = $request->get('editdescription');
        $event->date = $request->get('editdate');
        $event->start_time = $request->get('editstarttime');
        $event->end_time = $request->get('editendtime');
        $event->url = $request->get('editurl');
        $event->save();

        if (!is_null($event->discord_msg_id)) {
            $date = date_create_from_format('d.m.Y H:i', request('editdate').' '.request('editstarttime'));
            $timestamp = $date->format(DateTime::ATOM);

            $dmsgid = app(DiscordAnnouncer::class)->editAnnouncement(
                $event->discord_msg_id, request('edittitle'), $event->url, $event->image_url, request('editdescription'), $request->user()->fname." ".$request->user()->lname, request('editdate'), request('editstarttime'), request('editendtime'), $timestamp
            );
        }

        return redirect()->route('app.staff.events.dashboard', app()->getLocale())->with('toast-info', 'Event content edited');
    }

    public function editImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eventid' => ['required'],
            'newimage' => ['required', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        if ($validator->fails()) {
            dd($validator->errors());
            return redirect()->back()->with('toast-error', 'Image is invalid.');
        } else {
            $imgName = Str::random(50);
            $imgExtension = $request->newimage->extension();
            $request->newimage->storeAs('/public/event_images', $imgName.".".$imgExtension);
            $imgUrl = Storage::url('event_images/'.$imgName.'.'.$imgExtension);

            $imgID = (new Snowflake)->id();
            $file = File::create([
                'id' => $imgID,
                'name' => $imgName.'.'.$imgExtension,
                'url' => $imgUrl,
            ]);
        }

        $event = Event::where('id', $request->get('eventid'))->first();
        if (is_null($event)) {
            return redirect()->back()->with('toast-error', 'Event was not found and could not be edited');
        }

        $event->has_image = true;
        $event->image_id = $imgID;
        $event->image_url = $imgUrl;
        $event->save();
        return redirect()->route('app.staff.events.dashboard', app()->getLocale())->with('toast-info', 'Event content edited');
    }
}
