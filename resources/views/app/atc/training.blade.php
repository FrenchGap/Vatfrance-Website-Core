@extends('layouts.app')

@section('page-title')
  ATC Training Center | {{ Auth::user()->fname }}
@endsection

@section('page-header')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>{{__('app/atc/atc_training_center.header_title', ['FNAME' => Auth::user()->fname])}}</h1>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
@endsection

@section('page-content')
<link rel="stylesheet" href="{{ asset('dashboard/stepbar.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<div class="container-fluid">
  <div class="row">
    <div class="col-md-3">
      <div class="small-box bg-success elevation-3">
        <div class="inner">
          <h3>{{ $sessionsCount }}</h3>

          <p>{{__('app/atc/atc_training_center.ment_sessions')}}</p>
        </div>
        <div class="icon">
          <i class="fas fa-headphones"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="small-box bg-warning elevation-3">
        <div class="inner">
          <h3>{{ $trainingPlatform }}</h3>

          <p>{{__('app/atc/atc_training_center.teaching_p')}}</p>
        </div>
        <div class="icon">
          <i class="fas fa-plane-departure"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="small-box bg-info elevation-3">
        <div class="inner">
          <h3>{{ $mentorObj->fname." ".$mentorObj->lname }}</h3>

          <p>{{__('app/atc/atc_training_center.curr_ment')}}</p>
        </div>
        <div class="icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-outline card-info elevation-3">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <button type="button" class="btn btn-flat btn-info" data-toggle="modal" data-target="#request_session">
                {{__('app/atc/atc_training_center.req_session')}}
              </button>
              
            </div>
            <div class="col-md-6">
              <button type="button" class="btn btn-flat btn-info float-right" data-toggle="modal" data-target="#send_message">
                {{__('app/atc/atc_training_center.send_msg_btn', ['MENTOR' => $mentorObj->fname])}}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="request_session">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">{{__('app/atc/atc_training_center.title_req', ['MENTOR' => $mentorObj->fname." ".$mentorObj->lname])}}</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('app.atc.training.requestsession', app()->getLocale()) }}" method="post">
          @csrf
          <div class="modal-body">
            <div class="form-group">
              <label for="reqposition">{{__('app/atc/atc_training_center.pos')}}</label>
              <select class="form-control" name="reqposition" id="reqposition">
                <option value="" disabled selected>{{__('app/atc/atc_training_center.select')}}...</option>
                @foreach ($positions as $p)
                  @if (count($p['positions']) > 0)
                    <optgroup label="{{ $p['city'] }} {{ $p['airport'] }}"></optgroup>
                    @foreach ($p['positions'] as $pos)
                      <option value="{{ $pos['code'] }}">{{ $pos['code'] }}</option>
                    @endforeach
                    <optgroup label=""></optgroup>
                  @endif
                @endforeach
              </select>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="session-date">{{__('app/atc/atc_training_center.date')}}</label>
                  <input type="text" class="form-control" id="session-date" name="sessiondate" placeholder="{{__('app/atc/atc_training_center.date')}}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="starttime">{{__('app/atc/atc_training_center.st_time')}} (UTC)</label>
                  <input type="text" class="form-control" id="starttime" name="starttime" placeholder="{{__('app/atc/atc_training_center.st_time')}}">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="endtime">{{__('app/atc/atc_training_center.end_time')}} (UTC)</label>
                  <input type="text" class="form-control" id="endtime" name="endtime" placeholder="{{__('app/atc/atc_training_center.end_time')}}">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="reqcomment">{{__('app/atc/atc_training_center.comment_for')}}</label>
              <textarea class="form-control" rows="3" name="reqcomment" id="reqcomment" style="resize: none;" placeholder="..."></textarea>
            </div>
          </div>
          <input type="hidden" name="mentorid" value="{{ $mentorObj->id }}">
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('app/atc/atc_training_center.close')}}</button>
            <button type="submit" class="btn btn-success">{{__('app/atc/atc_training_center.send_req')}}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal fade" id="send_message">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">{{__('app/atc/atc_training_center.title_msg', ['MENTOR' => $mentorObj->fname." ".$mentorObj->lname])}}</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('app.inmsg.send', app()->getLocale()) }}" method="post">
          @csrf
          <div class="modal-body">
            <div class="form-group">
              <label for="msgsubject">{{__('app/atc/atc_training_center.subject')}}</label>
              <input type="text" class="form-control" id="msgsubject" name="msgsubject" placeholder="{{__('app/atc/atc_training_center.subject')}}">
            </div>
            <div class="form-group">
              <label for="msgbody">{{__('app/atc/atc_training_center.msg')}}</label>
              <textarea class="form-control" rows="15" name="msgbody" id="msgbody" placeholder="{{__('app/atc/atc_training_center.msg_placeholder')}}"></textarea>
            </div>
          </div>
          <input type="hidden" name="msgrecipient" value="{{ $mentorObj->id }}">
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('app/atc/atc_training_center.close')}}</button>
            <button type="submit" class="btn btn-success">{{__('app/atc/atc_training_center.send_msg')}}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card elevation-3">
        <div class="card-body">
          <div class="steps">
            <ul class="steps-container">
              @foreach ($steps as $step)
                @php
                  $progCurrent = $student['progress'] * $progSteps;
                  $now = ($loop->index + 1)*$progSteps;
                  if ($now > $progCurrent) {
                    $now = false;
                  } else {
                    $now = true;
                  }
                @endphp
                <li style="width:{{ $progSteps }}%;" @if ($now) class="activated" @endif>
                  <div class="step">
                    <div class="step-image"><span></span></div>
                    <div class="step-current">{{ $step['type'] }}</div>
                    <div class="step-description">{{ $step['title'] }}</div>
                  </div>
                </li>
              @endforeach
            </ul>
            <div class="step-bar" style="width: {{ $progCurrent }}%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      {{-- Training sessions table --}}
      <div class="card card-dark elevation-3">
        <div class="card-header">
          <h3 class="card-title">{{__('app/atc/atc_training_center.upcoming_sess')}}</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <table
            id="training_sessions"
            class="table table-bordered table-hover"
            data-order='[[ 1, "desc" ]]'>
            <thead>
            <tr>
              <th>{{__('app/atc/atc_training_center.callsign')}}</th>
              <th>{{__('app/atc/atc_training_center.when')}}</th>
              <th>{{__('app/atc/atc_training_center.mentor')}}</th>
              <th>{{__('app/atc/atc_training_center.prop_by')}}</th>
              <th>{{__('app/atc/atc_training_center.mentor_comment')}}</th>
              <th>{{__('app/atc/atc_training_center.student_comment')}}</th>
              <th>{{__('app/atc/atc_training_center.status')}}</th>
              <th>{{__('app/atc/atc_training_center.options')}}</th>
            </tr>
            </thead>
            <tbody>
              @foreach ($sessions as $training)
                <tr>
                  <td>{{ $training['position'] }}</td>
                  <td>{{ $training['date'] }} {{ $training['time'] }}</td>
                  <td>{{ $training['mentorUser']['fname'] }} {{ $training['mentorUser']['lname'] }} ({{ $training['mentorUser']['vatsim_id'] }})</td>
                  <td>{{ $training['requested_by'] }}</td>
                  <td>
                    @if (!is_null($training['mentor_comment']))
                    <button type="button" class="btn btn-flat btn-info" data-toggle="modal" data-target="#mentor_comment_{{ $training['id'] }}"><i class="far fa-eye"></i></button>
                    @else
                      {{__('app/atc/atc_training_center.no_comment')}}
                    @endif
                  </td>
                  <td>
                    @if (!is_null($training['student_comment']))
                    <button type="button" class="btn btn-flat btn-info" data-toggle="modal" data-target="#student_comment_{{ $training['id'] }}"><i class="far fa-eye"></i></button>
                    @else
                      {{__('app/atc/atc_training_center.no_comment')}}
                    @endif
                  </td>
                  <td>{{ $training['status'] }}</td>
                  <td>
                    @if ($training['accepted_by_mentor'] == false && $training['accepted_by_student'] == true)

                      {{-- Only accepted by student --}}
                      <form action="{{ route('app.atc.training.cancelsession', app()->getLocale()) }}" method="POST">
                        @csrf
                          <input type="hidden" name="sessionid" value="{{ $training['id'] }}">
                          <button type="submit" class="btn btn-block btn-danger btn-flat"><i class="fa fa-times"></i></button>
                      </form>

                    @else

                      @if ($training['accepted_by_mentor'] == true && $training['accepted_by_student'] == false)

                        {{-- Only accepted by mentor --}}
                        <form action="{{ route('app.atc.training.acceptsession', app()->getLocale()) }}" method="POST">
                          @csrf
                          <input type="hidden" name="sessionid" value="{{ $training['id'] }}">
                          <button type="submit" class="btn btn-block btn-success btn-flat"><i class="fa fa-check"></i></button>
                        </form>
                        <form action="{{ route('app.atc.training.cancelsession', app()->getLocale()) }}" method="POST">
                          @csrf
                          <input type="hidden" name="sessionid" value="{{ $training['id'] }}">
                          <button type="submit" class="btn btn-block btn-danger btn-flat"><i class="fa fa-times"></i></button>
                        </form>

                      @else

                        @if ($training['accepted_by_mentor'] == true && $training['accepted_by_student'] == true && $training['completed'] == false)

                          {{-- Training accepted by both --}}
                          <form action="{{ route('app.atc.training.cancelsession', app()->getLocale()) }}" method="POST">
                            @csrf
                          <input type="hidden" name="sessionid" value="{{ $training['id'] }}">
                          <button type="submit" class="btn btn-block btn-danger btn-flat"><i class="fa fa-times"></i></button>
                          </form>

                        @else

                          @if ($training['accepted_by_mentor'] == true && $training['accepted_by_student'] == true && $training['completed'] == true)

                          @if (is_null($training['mentor_report']))

                          {{__('app/atc/atc_training_center.no_report')}}

                          @else

                            {{-- Training completed, has report --}}
                            <button type="button" class="btn btn-block btn-info btn-flat" data-toggle="modal" data-target="#mentor_report_{{ $training['id'] }}">{{__('app/atc/atc_training_center.see_report')}}</button>

                          @endif
                          @endif
                        @endif
                      @endif
                    @endif
                  </td>
                </tr>
                @if (!is_null($training['mentor_comment']))
                <div class="modal fade" id="mentor_comment_{{ $training['id'] }}">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">{{__('app/atc/atc_training_center.mentor_comment')}}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <p>{{ $training['mentor_comment'] }}</p>
                      </div>
                      <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{__('app/atc/atc_training_center.close')}}</button>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
                @if (!is_null($training['student_comment']))
                <div class="modal fade" id="student_comment_{{ $training['id'] }}">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">{{__('app/atc/atc_training_center.student_comment')}}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <p>{{ $training['student_comment'] }}</p>
                      </div>
                      <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{__('app/atc/atc_training_center.close')}}</button>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
                @if (!is_null($training['mentor_report']))
                <div class="modal fade" id="mentor_report_{{ $training['id'] }}">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h4 class="modal-title">{{__('app/atc/atc_training_center.mentor_report')}}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <p>{{ $training['mentor_report'] }}</p>
                      </div>
                      <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{__('app/atc/atc_training_center.close')}}</button>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
        <!-- /.card-body -->
      </div>
    </div>
  </div>
</div>
<script src="{{ asset('dashboard/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('dashboard/jquery/jquery.validate.js') }}"></script>
<script src="{{ asset('dashboard/jquery/additional-methods.js') }}"></script>
<script src="{{ asset('dashboard/adminlte/dist/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('dashboard/adminlte/dist/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
  $('#training_sessions').DataTable({
    "paging": false,
    "lengthChange": false,
    "searching": false,
    "ordering": false,
    "autoWidth": false,
    "info": false,
    "language": {
      "emptyTable" : "{{__('app/atc/atc_training_center.empty_table')}}"
    }
  });
  flatpickr("#session-date", {
      enableTime: false,
      dateFormat: "d.m.Y",
      minDate: "today",
      allowInput: true,
  });
  d = new Date();
  flatpickr("#starttime", {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      defaultHour: d.getUTCHours(),
      defaultMinute: 00,
      allowInput: true,
      time_24hr: true,
      minuteIncrement: 15
  });
  flatpickr("#endtime", {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      defaultHour: d.getUTCHours()+1,
      defaultMinute: 00,
      allowInput: true,
      time_24hr: true,
      minuteIncrement: 15
  });
</script>
@endsection