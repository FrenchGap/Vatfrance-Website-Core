@extends('layouts.app')

@section('page-title')
  Home | {{ Auth::user()->fname }}
@endsection

@section('page-header')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>{{__('app/user/index.welcomeback')}}, {{ Auth::user()->fname }}!</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('app.user.settings', app()->getLocale()) }}">{{__('app/app_menus.my_settings')}}</a></li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
@endsection

@section('page-content')
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card card-widget widget-user elevation-3">
          <div class="widget-user-header text-white" style="background-color: #17a2b8;">
            <h3 class="widget-user-username">{{ Auth::user()->fullname() }}</h3>
            <h5 class="widget-user-desc">{{ Auth::user()->account_type }}</h5>
            <div class="widget-user-image">
              <img class="img-circle elevation-3" src="{{ asset('media/img/dashboard/default_upp.png') }}" alt="User Avatar">
            </div>
          </div>
          <div class="card-footer">
            <div class="row">
              <div class="col-sm-3 border-right">
                <div class="description-block">
                  <span class="description-text">Vatsim ID</span>
                  <h5 class="description-header">{{ Auth::user()->vatsim_id }}</h5>
                </div>
                <!-- /.description-block -->
              </div>
              <div class="col-sm-3 border-right">
                <div class="description-block">
                  <span class="description-text">{{__('app/user/index.atc_rank')}}</span>
                  <h5 class="description-header">{{ Auth::user()->fullAtcRank() }}</h5>
                </div>
                <!-- /.description-block -->
              </div>
              <!-- /.col -->
              <div class="col-sm-3 border-right">
                <div class="description-block">
                  <span class="description-text">{{__('app/user/index.pilot_rank')}}</span>
                  <h5 class="description-header">P{{ Auth::user()->pilot_rating }}</h5>
                </div>
                <!-- /.description-block -->
              </div>
              <div class="col-sm-3">
                <div class="description-block">
                  <span class="description-text">{{__('app/user/index.approved_atc')}}</span>
                  <h5 class="description-header">@if (Auth::user()->isApprovedAtc() == true)
                    {{__('app/user/index.approved')}}
                  @else
                    {{__('app/user/index.not_approved')}}
                  @endif</h5>
                </div>
                <!-- /.description-block -->
              </div>
            </div>
            <!-- /.row -->
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4">
        <div class="card card-info elevation-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="nav-icon far fa-calendar-alt"></i>
              {{__('app/user/index.ev_title')}}
            </h3>
          </div>
        </div>
        <div class="card elevation-0" style="background-color: #f8f9fa;">
          <div class="card-body p-0">
            @forelse ($events as $e)
            <div class="card card-dark elevation-3">
              <div class="card-header">
                <h3 class="card-title">{{$e['title']}}</h3>
                <span class="float-right">
                  {{date_create_from_format('Y-m-d H:i:s', $e['start_date'])->format('d.m.Y')}} | {{date_create_from_format('Y-m-d H:i:s', $e['start_date'])->format('H:i')}}z - {{date_create_from_format('Y-m-d H:i:s', $e['end_date'])->format('H:i')}}z
                </span>
              </div>
              <div class="card-body" style="padding: 0 0 0 0;">
                <a href="{{ $e['url'] }}" target="_blank">
                  <img class="img-fluid pad" src="
                    @if ($e['has_image'] == true)
                      {{config('app.url')}}/{{$e['image_url']}}
                    @else
                      {{asset('media/img/placeholders/events_placeholder_noimg.png')}}
                    @endif"
                  alt="Placeholder">
                </a>
              </div>
              <div class="card-footer">
                {!!nl2br($e['description'])!!}
              </div>
            </div>
            @empty
            <div class="card elevation-3">
              <div class="card-header">
                <h3 class="card-title"><i>{{__('app/user/index.ev_noevents')}}</i></h3>
              </div>
            </div>
            @endforelse
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-info elevation-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="nav-icon fas fa-bullhorn"></i>
              {{__('app/user/index.n_title')}}
            </h3>
          </div>
        </div>
        <div class="card elevation-0" style="background-color: #f8f9fa;">
          <div class="card-body p-0">
            @forelse ($news as $n)
            <div class="card card-dark elevation-3">
              <div class="card-header">
                <h3 class="card-title">{{ $n['title'] }}</h3>
                <span class="float-right">
                  {{ Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i:s', $n['created_at'])->format('d.m.Y | H:i\z') }}
                </span>
              </div>
              <div class="card-body">
                {!!nl2br($n['content'])!!}
              </div>
              <div class="card-footer">
                <i>Author: {{$n['author']['fname']}} {{$n['author']['lname']}} @if (!is_null($n['author']['staff']['title']))
                    ({{$n['author']['staff']['title']}})
                @endif</i>
              </div>
            </div>
            @empty
            <div class="card elevation-3">
              <div class="card-header">
                <h3 class="card-title"><i>{{__('app/user/index.n_nonews')}}</i></h3>
              </div>
            </div>
            @endforelse
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-info elevation-3">
          <div class="card-header">
            <h3 class="card-title">
              <i class="nav-icon far fa-calendar-check"></i>
              {{__('app/user/index.b_title')}}
            </h3>
          </div>
        </div>
        <div class="card elevation-3">
          @if (count($bookings) == 0)
          <div class="card-header">
            <h3 class="card-title"><i>{{__('app/user/index.b_nobook')}}</i></h6>
          </div>
          @else
          <div class="card-body p-0">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th style="width: 30%;">{{__('app/user/index.b_pos')}}</th>
                  <th style="width: 20%;">{{__('app/user/index.b_time')}}</th>
                  <th style="width: 50%;">{{__('app/user/index.b_who')}}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($bookings as $b)
                <tr>
                  <td>{{$b['position']}}</th>
                  <td>{{date_create_from_format('Y-m-d H:i:s', $b['start_date'])->format('H:i') }}z - {{ date_create_from_format('Y-m-d H:i:s', $b['end_date'])->format('H:i') }}z</td>
                  <td @if ($b['training'] == true) style="color: blueviolet;" @endif>{{$b['user']['fname']}} {{$b['user']['lname']}} - {{$b['user']['atc_rating_short']}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>

    </div>
  </div>
@endsection