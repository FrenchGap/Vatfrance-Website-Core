@extends('layouts.app')

@section('page-title')
  ATC Mentoring | My students
@endsection

@section('page-header')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>ATC Mentoring - My students</h1>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
@endsection

@section('page-content')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" href="{{ asset('dashboard/stepbar.css') }}">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2">
        <div class="info-box">
          <span class="info-box-icon bg-warning"><i class="fas fa-user"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Students</span>
            <span class="info-box-number">{{ $studentCount }}</span>
          </div>
        </div>
        <div class="card card-outline card-info">
          <div class="card-header">
            <h3 class="card-title">Mentor's toolbox</h3>
          </div>
          <div class="card-body p-0">
            <table class="table">
              <thead>
              </thead>
              <tbody>
                <tr>
                  <td><a href="#" target="_blank" rel="noopener noreferrer">Coming Soon!</a></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-10">
        <script src="{{ asset('dashboard/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('dashboard/jquery/jquery.validate.js') }}"></script>
        <script src="{{ asset('dashboard/jquery/additional-methods.js') }}"></script>
        <script src="{{ asset('dashboard/adminlte/dist/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('dashboard/adminlte/dist/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        @foreach ($students as $s)
          <div class="card card-outline collapsed-card @if(true) card-success @else card-danger @endif">
            <div class="card-header" data-card-widget="collapse">
              <h3 class="card-title">{{ $s['user']['fname'] }} {{ $s['user']['lname'] }} - {{ $s['user']['atc_rating_short'] }} - {{ $s['mentoringRequest']['icao'] }}</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                  <h4>{{ $s['user']['fname'] }}'s progress</h4>
                  <div class="steps">
                    <ul class="steps-container">
                      @foreach ($steps as $step)
                      @php
                        $progCurrent = $s['progress'] * $progSteps;
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
              <div class="row mt-3">
                <div class="col-md-12">
                  <h4>All training sessions</h4>
                  <table
                    id="upcoming_sessions_{{ $s['user']['vatsim_id'] }}"
                    class="table table-bordered table-hover"
                    data-order='[[ 1, "desc" ]]'>
                    <thead>
                    <tr>
                      <th>Position</th>
                      <th>When</th>
                      <th>Scheduled by</th>
                      <th>Mentor Comment</th>
                      <th>Student Comment</th>
                      <th>Status</th>
                      <th>Options</th>
                    </tr>
                    </thead>
                    <tbody>
                      @foreach ($s['sessions'] as $training)
                        <tr>
                          <td>{{ $training['position'] }}</td>
                          <td>{{ $training['date'] }} {{ $training['time'] }}</td>
                          <td>{{ $training['requested_by'] }}</td>
                          <td>
                            @if (!is_null($training['mentor_comment']))
                            <button type="button" class="btn btn-flat btn-info" data-toggle="modal" data-target="#mentor_comment_{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}"><i class="far fa-eye"></i></button>
                            @else
                              (No comment)
                            @endif
                          </td>
                          <td>
                            @if (!is_null($training['student_comment']))
                            <button type="button" class="btn btn-flat btn-info" data-toggle="modal" data-target="#student_comment_{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}"><i class="far fa-eye"></i></button>
                            @else
                              (No comment)
                            @endif
                          </td>
                          <td>{{ $training['status'] }}</td>
                          <td>
                            @if ($training['accepted_by_mentor'] == true && $training['accepted_by_student'] == false)

                              {{-- Only accepted by mentor --}}
                              <button type="button" class="btn btn-block btn-danger btn-flat" data-toggle="modal" data-target="#cancel-session-{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}"><i class="fa fa-times"></i></button>

                            @else

                              @if ($training['accepted_by_mentor'] == false && $training['accepted_by_student'] == true)

                                {{-- Only accepted by student --}}
                                <form action="{{ route('app.staff.atc.mine.acceptsession', app()->getLocale()) }}" method="POST">
                                  @csrf
                                  <input type="hidden" name="sessionid" value="{{ $training['id'] }}">
                                  <button type="submit" class="btn btn-block btn-success btn-flat"><i class="fa fa-check"></i></button>
                                </form>
                                <button type="button" class="btn btn-block btn-danger btn-flat" data-toggle="modal" data-target="#cancel-session-{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}"><i class="fa fa-times"></i></button>

                              @else

                                @if ($training['accepted_by_mentor'] == true && $training['accepted_by_student'] == true && $training['completed'] == false)

                                  {{-- Training accepted by both --}}
                                  <form action="{{ route('app.staff.atc.mine.completesession', app()->getLocale()) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="sessionid" value="{{ $training['id'] }}">
                                    <button type="submit" class="btn btn-block btn-success btn-flat">Complete</button>
                                  </form>
                                  <button type="button" class="btn btn-block btn-danger btn-flat" data-toggle="modal" data-target="#cancel-session-{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}"><i class="fa fa-times"></i></button>

                                @else

                                 @if ($training['accepted_by_mentor'] == true && $training['accepted_by_student'] == true && $training['completed'] == true)

                                  @if (is_null($training['mentor_report']))

                                    {{-- Training completed, awaiting report --}}
                                    <button type="button" class="btn btn-block btn-info btn-flat" data-toggle="modal" data-target="#add-report-{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}"><i class="fa fa-edit"></i></button>

                                  @else

                                    {{-- Training completed, has report --}}
                                    <button type="button" class="btn btn-block btn-info btn-flat" data-toggle="modal" data-target="#mentor_report_{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}">See Report</button>

                                  @endif
                                 @endif
                                @endif
                              @endif
                            @endif
                          </td>
                        </tr>
                        @if (!is_null($training['mentor_comment']))
                        <div class="modal fade" id="mentor_comment_{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Mentor's comment</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <p>{{ $training['mentor_comment'] }}</p>
                              </div>
                              <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                              </div>
                            </div>
                          </div>
                        </div>
                        @endif
                        @if (!is_null($training['student_comment']))
                        <div class="modal fade" id="student_comment_{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Student's comment</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <p>{{ $training['student_comment'] }}</p>
                              </div>
                              <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                              </div>
                            </div>
                          </div>
                        </div>
                        @endif
                        @if (!is_null($training['mentor_report']))
                        <div class="modal fade" id="mentor_report_{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Mentor report</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                <p>{{ $training['mentor_report'] }}</p>
                              </div>
                              <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                              </div>
                            </div>
                          </div>
                        </div>
                        @endif
                        <div class="modal fade" id="cancel-session-{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}">
                          <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Cancel session</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <form action="{{ route('app.staff.atc.mine.cancelsession', app()->getLocale()) }}" method="post">
                                @csrf
                                <div class="modal-body">
                                  <p>Are you sure you want to cancel this session?</p>
                                </div>
                                <div class="modal-footer justify-content-between">
                                  <input type="hidden" name="sessionid" value="{{ $training['id'] }}">
                                  <button type="submit" class="btn btn-danger">Confirm</button>
                                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                </div>
                              </form>
                            </div>
                            <!-- /.modal-content -->
                          </div>
                          <!-- /.modal-dialog -->
                        </div>
                        <div class="modal fade" id="add-report-{{ $training['id'] }}-{{ $s['user']['vatsim_id']}}">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Cancel session</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <form action="{{ route('app.staff.atc.mine.sessionreport', app()->getLocale()) }}" method="post">
                                @csrf
                                <div class="modal-body">
                                  <div class="form-group">
                                    <label for="report_box_{{ $training['id'] }}_{{ $s['user']['vatsim_id']}}">Write session report</label>
                                    <textarea
                                      class="form-control"
                                      name="report_box"
                                      id="report_box_{{ $training['id'] }}_{{ $s['user']['vatsim_id']}}"
                                      rows="10"
                                      required></textarea>
                                  </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                  <input type="hidden" name="sessionid" value="{{ $training['id'] }}">
                                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                  <button type="submit" class="btn btn-success">Confirm</button>
                                </div>
                              </form>
                            </div>
                            <!-- /.modal-content -->
                          </div>
                          <!-- /.modal-dialog -->
                        </div>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="button" class="btn btn-info btn-flat" data-toggle="modal" data-target="#book-session-{{ $s['user']['vatsim_id'] }}">Book Session</button>
              <button type="button" class="btn btn-info btn-flat" data-toggle="modal" data-target="#send-message-{{ $s['user']['vatsim_id'] }}">Send Message</button>
              <button type="button" class="btn btn-warning btn-flat" data-toggle="modal" data-target="#edit-progress-{{ $s['user']['vatsim_id']}}">Edit Progress</button>
              <button type="button" class="btn btn-warning btn-flat" data-toggle="modal" data-target="#edit-solo{{ $s['user']['vatsim_id']}}">Solo Validation</button>
              <button type="button" class="btn btn-danger btn-flat" data-toggle="modal" data-target="#terminate-{{ $s['user']['vatsim_id']}}">Terminate Mentoring</button>
              {{-- Edit progress modal  --}}
              <div class="modal fade" id="edit-progress-{{ $s['user']['vatsim_id']}}">
                <div class="modal-dialog modal-sm">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">Edit {{ $s['user']['fname'] }}'s progress</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <form action="{{ route('app.staff.atc.mine.progress', app()->getLocale()) }}" method="post">
                      @csrf
                      <div class="modal-body">
                        <div class="form-group">
                          <label for="reqposition">Select {{ $s['user']['fname'] }}'s latest achievement</label>
                          <select class="form-control" name="stuprogress" id="stuprogress">
                            @if ($s['progress'] == 0)
                              <option value="0" disabled selected>Choose...</option>
                            @else
                              <option value="{{ $s['progress'] }}">{{ $steps[$s['progress']]['title'] }}</option>
                            @endif
                            @foreach ($steps as $step)
                              <option value="{{ $loop->index + 1 }}">{{ $step['title'] }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="modal-footer justify-content-between">
                        <input type="hidden" name="userid" value="{{ $s['user']['id'] }}">
                        <button type="submit" class="btn btn-danger">Confirm</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                      </div>
                    </form>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              {{-- Send private message  --}}
              <div class="modal fade" id="send-message-{{ $s['user']['vatsim_id'] }}">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">Send message to {{ $s['user']['fname'] }} {{ $s['user']['lname'] }}</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <form action="{{ route('app.inmsg.send', app()->getLocale()) }}" method="post">
                      @csrf
                      <div class="modal-body">
                        <div class="form-group">
                          <label for="msgsubject">Subject</label>
                          <input type="text" class="form-control" id="msgsubject" name="msgsubject" placeholder="Subject">
                        </div>
                        <div class="form-group">
                          <label for="msgbody">Message</label>
                          <textarea class="form-control" rows="5" name="msgbody" id="msgbody" placeholder="Your message"></textarea>
                        </div>
                      </div>
                      <input type="hidden" name="msgrecipient" value="{{ $s['user']['id'] }}">
                      <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Send message</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              {{-- Approve Solo  --}}
              <div class="modal fade" id="edit-solo{{ $s['user']['vatsim_id']}}">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">Manage {{ $s['user']['fname'] }}'s solo approvals</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form action="{{ route('app.staff.atc.mine.soloadd', app()->getLocale()) }}" method="post">
                        @csrf
                        <div class="row border-bottom">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label for="selectpos">Select position to approve</label>
                              <select class="form-control" name="selectpos" id="selectpos">
                                @foreach ($positions as $pos)
                                  @if (count($pos['positions']) > 0)
                                    <optgroup label="{{ $pos['city'] }} {{ $pos['airport'] }}"></optgroup>
                                    @foreach ($pos['positions'] as $solopos)
                                      <option value="{{ $solopos['code'] }}">{{ $solopos['code'] }}</option>
                                    @endforeach
                                    <optgroup></optgroup>
                                  @endif
                                @endforeach
                              </select>
                            </div>
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="start-date-solo-{{ $s['user']['vatsim_id'] }}">Start date</label>
                                  <input type="text" class="form-control" id="start-date-solo-{{ $s['user']['vatsim_id'] }}" name="startdate" placeholder="Start date">
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label for="length">Duration</label>
                                  <select class="form-control" name="length" id="length">
                                    @foreach ($soloLengths as $sl)
                                      <option value="{{ $sl }}">{{ $sl }} days</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-12 mb-3">
                            <input type="hidden" name="userid" value="{{ $s['user']['id'] }}">
                            <button type="submit" class="btn btn-success btn-flat mr-0">Submit</button>
                          </div>
                        </div>
                      </form>
                        <div class="row">
                          <div class="col-md-12 mt-3">
                            <h4>Current solo approvals</h4>
                            <table
                              id="solo_sessions_{{ $s['user']['vatsim_id'] }}"
                              class="table table-bordered table-hover"
                              data-order='[[ 1, "desc" ]]'>
                              <thead>
                                <tr>
                                  <th>Position</th>
                                  <th>Start date</th>
                                  <th>End date</th>
                                  <th>Valid</th>
                                  <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach ($s['soloApprovals'] as $slapp)
                                    <tr>
                                      <td>{{ $slapp['position'] }}</td>
                                      <td>{{ $slapp['start_date'] }}</td>
                                      <td>{{ $slapp['end_date'] }}</td>
                                      <td>
                                        @if (\Illuminate\Support\Carbon::now()->format('d.m.Y') > \Illuminate\Support\Carbon::parse($slapp['end_date'])->format('d.m.Y'))
                                          No
                                        @else
                                          Yes 
                                        @endif
                                      </td>
                                      <td>
                                        <form action="{{ route('app.staff.atc.mine.solodel', app()->getLocale()) }}" method="post">
                                          @csrf
                                          <input type="hidden" name="soloid" value="{{ $slapp['id'] }}">
                                          <button type="submit" class="btn btn-flat btn-danger">Cancel</button>
                                        </form>
                                      </td>
                                    </tr>
                                  @endforeach
                                </tbody>
                            </table>
                          </div>
                        </div>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              {{-- Termination modal  --}}
              <div class="modal fade" id="terminate-{{ $s['user']['vatsim_id']}}">
                <div class="modal-dialog modal-md">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">Are you sure?</h4>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <form action="{{ route('app.staff.atc.mine.terminate', app()->getLocale()) }}" method="post">
                      @csrf
                      <div class="modal-body">
                        <p>You are about to terminate your mentoring with {{ $s['user']['fname'] }}. This cannot be undone.</p>
                      </div>
                      <div class="modal-footer justify-content-between">
                        <input type="hidden" name="userid" value="{{ $s['user']['id'] }}">
                        <button type="submit" class="btn btn-danger">Confirm</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                      </div>
                    </form>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
            </div>
          </div>
          <div class="modal fade" id="book-session-{{ $s['user']['vatsim_id'] }}">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Book a session with {{ $s['user']['fname'] }}</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form action="{{ route('app.staff.atc.mine.booksession', app()->getLocale()) }}" method="post">
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
                          <input type="text" class="form-control" id="session-date-{{ $s['user']['vatsim_id'] }}" name="sessiondate" placeholder="{{__('app/atc/atc_training_center.date')}}">
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="starttime">{{__('app/atc/atc_training_center.st_time')}} (UTC)</label>
                          <input type="text" class="form-control" id="starttime-{{ $s['user']['vatsim_id'] }}" name="starttime" placeholder="{{__('app/atc/atc_training_center.st_time')}}">
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="form-group">
                          <label for="endtime">{{__('app/atc/atc_training_center.end_time')}} (UTC)</label>
                          <input type="text" class="form-control" id="endtime-{{ $s['user']['vatsim_id'] }}" name="endtime" placeholder="{{__('app/atc/atc_training_center.end_time')}}">
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="reqcomment">{{__('app/atc/atc_training_center.comment_for')}}</label>
                      <textarea class="form-control" rows="3" name="reqcomment" id="reqcomment" style="resize: none;" placeholder="..."></textarea>
                    </div>
                  </div>
                  <input type="hidden" name="userid" value="{{ $s['user']['id'] }}">
                  <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Send request</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <script>
            $("#upcoming_sessions_{{ $s['user']['vatsim_id'] }}").DataTable({
              "paging": false,
              "lengthChange": false,
              "searching": false,
              "ordering": false,
              "autoWidth": false,
              "info": false,
              "language": {
                "emptyTable": "No training sessions found."
              }
            });
            $("#solo_sessions_{{ $s['user']['vatsim_id'] }}").DataTable({
              "paging": false,
              "lengthChange": false,
              "searching": false,
              "ordering": false,
              "autoWidth": false,
              "info": false,
              "language": {
                "emptyTable": "No training sessions found."
              }
            });
            flatpickr("#session-date-{{ $s['user']['vatsim_id'] }}", {
                enableTime: false,
                dateFormat: "d.m.Y",
                minDate: "today",
                allowInput: true,
            });
            flatpickr("#start-date-solo-{{ $s['user']['vatsim_id'] }}", {
                enableTime: false,
                dateFormat: "d.m.Y",
                minDate: "today",
                allowInput: true,
            });
            d = new Date();
            flatpickr("#starttime-{{ $s['user']['vatsim_id'] }}", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                defaultHour: d.getUTCHours(),
                defaultMinute: 00,
                allowInput: true,
                time_24hr: true,
                minuteIncrement: 15
            });
            flatpickr("#endtime-{{ $s['user']['vatsim_id'] }}", {
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
        @endforeach
      </div>
    </div>
  </div>
@endsection