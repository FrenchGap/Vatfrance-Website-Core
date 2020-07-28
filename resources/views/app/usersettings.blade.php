@extends('layouts.app')

@section('page-title')
  User settings | {{ Auth::user()->fname }}
@endsection

@section('page-header')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>User Settings - {{ Auth::user()->fname }}</h1>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
@endsection

@section('page-content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-4">
      <!-- Profile Image -->
      <div class="card card-primary card-outline">
        <div class="card-body box-profile">
          <h3 class="profile-username text-center">{{ Auth::user()->fullname() }}</h3>

          <p class="text-muted text-center">{{ Auth::user()->account_type }}</p>

          <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
              <b>Contact email</b> <a class="float-right">{{ $useremail }}</a>
            </li>
            <li class="list-group-item">
              <b>{{__('app/app_indexpage.atc_rank')}}</b> <a class="float-right">{{ Auth::user()->fullAtcRank() }}</a>
            </li>
            <li class="list-group-item">
              <b>{{__('app/app_indexpage.pilot_rank')}}</b> <a class="float-right">{{ Auth::user()->pilot_rating }}</a>
            </li>
            <li class="list-group-item">
              <b>{{__('app/app_indexpage.approved_atc')}}</b> <a class="float-right">@if (Auth::user()->isApprovedAtc() == true)
                {{__('app/app_indexpage.approved')}}
              @else
                {{__('app/app_indexpage.not_approved')}}
              @endif</a>
            </li>
            <li class="list-group-item">
              <b>Staff Status</b> <a class="float-right">@if (Auth::user()->isStaff() == true)
                {{__('app/app_indexpage.approved')}}
              @else
                {{__('app/app_indexpage.not_approved')}}
              @endif</a>
            </li>
          </ul>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->

      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">GDPR Request</h3>
        </div>
        <div class="card-body">
          <form>
            <button type="submit" class="btn btn-primary">View your data</button>
          </form>
          <form>
            <button type="submit" class="btn btn-danger">Delete all your data</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Edit Your Details</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form role="form" name="usereditform" id="usereditform" action="{{ route('app.user.settings.edit', app()->getLocale()) }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="form-group">
              <label>Custom email for your account</label>
              <input type="email" class="form-control" name="customemail" value="{{ Auth::user()->custom_email }}">
            </div>
            <div class="form-group">
              <label>Modify your account type</label>
                <select class="form-control" name="editusertype">
                  @foreach ($usertypes as $ut)
                    @if ($ut == Auth::user()->account_type)
                      <option value="{{ $ut }}" selected>{{ $ut }}</option>
                    @else
                      <option value="{{ $ut }}">{{ $ut }}</option>
                    @endif
                  @endforeach
                </select>
            </div>
            <div class="form-group">
              <div class="custom-control custom-switch custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="hide-details-switch" name="hidedetails" @if (Auth::user()->hiddenDetails() == true) checked @endif>
                <label class="custom-control-label" for="hide-details-switch">Hide your personal details from rosters</label>
              </div>
            </div>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <input type="hidden" name="userid" value="{{ Auth::user()->id }}">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">Edit Your Email Preferences</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form role="form" name="usereditform" id="usereditform" action="{{ route('app.user.settings.editemail', app()->getLocale()) }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="form-group">
              <div class="custom-control custom-switch custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="atcbookingemail" name="atcbookingemail" @if (!Auth::user()->emailPreferences() == false && Auth::user()->emailPreferences()->atc_booking_emails == true) checked @endif>
                <label class="custom-control-label" for="atcbookingemail">Receive your ATC Booking emails</label>
              </div>
            </div>
            <div class="form-group">
              <div class="custom-control custom-switch custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="atcmentoring" name="atcmentoring" @if (!Auth::user()->emailPreferences() == false && Auth::user()->emailPreferences()->atc_mentoring_emails == true) checked @endif>
                <label class="custom-control-label" for="atcmentoring">Receive your ATC Mentoring emails</label>
              </div>
            </div>
            <div class="form-group">
              <div class="custom-control custom-switch custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="inmsgemail" name="inmsgemail" @if (!Auth::user()->emailPreferences() == false && Auth::user()->emailPreferences()->internal_messaging_emails == true) checked @endif>
                <label class="custom-control-label" for="inmsgemail">Receive notifications of new 'Pigeon Voyageur' messages</label>
              </div>
            </div>
            <div class="form-group">
              <div class="custom-control custom-switch custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="eventemail" name="eventemail" @if (!Auth::user()->emailPreferences() == false && Auth::user()->emailPreferences()->event_emails == true) checked @endif>
                <label class="custom-control-label" for="eventemail">Receive vACC Event emails</label>
              </div>
            </div>
            <div class="form-group">
              <div class="custom-control custom-switch custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="websiteupdates" name="websiteupdates" @if (!Auth::user()->emailPreferences() == false && Auth::user()->emailPreferences()->website_update_emails == true) checked @endif>
                <label class="custom-control-label" for="websiteupdates">Receive Website Update emails</label>
              </div>
            </div>
            <div class="form-group">
              <div class="custom-control custom-switch custom-switch-on-success">
                <input type="checkbox" class="custom-control-input" id="newsemail" name="newsemail" @if (!Auth::user()->emailPreferences() == false && Auth::user()->emailPreferences()->news_emails == true) checked @endif>
                <label class="custom-control-label" for="newsemail">Receive vACC News emails</label>
              </div>
            </div>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <input type="hidden" name="userid" value="{{ Auth::user()->id }}">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="{{ asset('dashboard/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('dashboard/adminlte/dist/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('dashboard/adminlte/dist/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
$('#usereditform').validate({
    rules: {
      customemail: {
        email: true,
        regex: /^\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i
      },
      editusertype: {
        required: true
      },
    },
    messages: {
      customemail: {
        email: "Invalid email format",
        regex: "Invalid email format"
      },
      editusertype: {
        required: "Please select an option"
      },
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
      error.addClass('invalid-feedback');
      element.closest('.form-group').append(error);
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).removeClass('is-invalid');
    }
  })
</script>
@endsection