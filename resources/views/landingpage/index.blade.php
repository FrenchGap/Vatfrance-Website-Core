@extends('layouts.landing')

@section('page-title')
  Home
@endsection

@section('page-masthead')
<header class="masthead">
  <div class="container h-100 bg-overlay justify-content-center">
    <div class="row h-75 align-items-center ">
      <div class="col-12 text-center">
        <h1 class="masthead-heading">{{__('lp/lp_titles.welcome_to_vatfrance')}}@if (Auth::check()), {{ Auth::user()->fname }}@endif!</h1>
        <h2 class="masthead-subheading">{{__('lp/lp_titles.french_branch')}}</h2>
        @if (Auth::check())
        <a href="{{ route('app.index', app()->getLocale()) }}" class="btn btn-xl btn-pill btn-primary px-7 mt-4"
          >{{__('lp/lp_menu.dashboard')}}</a
        >
        @else
        <a href="{{ route('auth.login', ['locale' => app()->getLocale(), 'redirflag' => 'false']) }}" class="btn btn-xl btn-pill btn-primary px-7 mt-4"
          >{{__('lp/lp_titles.join_us')}}!</a
        >
        @endif
      </div>
    </div>
  </div>
</header>
@endsection

@section('page-content')
  <!-- Page Content -->
  <div class="container-fluid py-4">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <h3 class="font-weight-medium mt-3">{{__('lp/lp_titles.welcome')}}!</h3>
          <p class="mt-4">
            {{__('lp/lp_index.director_text')}}
          </p>
          <p class="text-right">
            Patrick Fuchez <br />
            {{__('lp/lp_titles.vacc_director')}}
          </p>
        </div>
        <div class="col-sm">
          <h3 class="font-weight-medium mt-3">{{__('lp/lp_titles.atc_bookings')}}</h3>
          <div class="card text-center mt-4">
            <div class="card-header">
              <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                  <a class="nav-link active" href="#atc-tab-1" data-toggle="tab">{{ $day0 }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#atc-tab-2" data-toggle="tab">{{ $day1 }}</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#atc-tab-3" data-toggle="tab">{{ $day2 }}</a>
                </li>
              </ul>
            </div>
            <div class="card-body">
              <div class="tab-content">
                <div class="tab-pane active" role="tabpanel" id="atc-tab-1">
                  @if (count($book0) == 0)
                  {{__('lp/lp_index.nobook_0')}}
                  @else
                  <table class="table table-borderless mt-n3">
                    <thead class="thead">
                      <tr>
                        <th scope="col">{{__('lp/lp_index.position')}}</th>
                        <th scope="col">{{__('lp/lp_index.name')}}</th>
                        <th scope="col">{{__('lp/lp_index.hours')}}</th>
                        <th scope="col">{{__('lp/lp_index.rating')}}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($book0 as $b)
                      <th scope="row">{{$b['position']}}</th>
                      <td>{{$b['user']['fname']}} {{$b['user']['lname']}} ({{$b['vatsim_id']}})</td>
                      <td>{{$b['time']}}</td>
                      <td>{{$b['user']['atc_rating_short']}}</td>
                      @endforeach
                    </tbody>
                  </table>
                  @endif
                </div>
                
                <div class="tab-pane" role="tabpanel" id="atc-tab-2">
                  @if (count($book1) == 0)
                  {{__('lp/lp_index.nobook_1')}}
                  @else
                  <table class="table">
                    <thead class="thead">
                      <tr>
                        <th scope="col">{{__('lp/lp_index.position')}}</th>
                        <th scope="col">{{__('lp/lp_index.name')}}</th>
                        <th scope="col">{{__('lp/lp_index.hours')}}</th>
                        <th scope="col">{{__('lp/lp_index.rating')}}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($book1 as $b)
                      <th scope="row">{{$b['position']}}</th>
                      <td>{{$b['user']['fname']}} {{$b['user']['lname']}} ({{$b['vatsim_id']}})</td>
                      <td>{{$b['time']}}</td>
                      <td>{{$b['user']['atc_rating_short']}}</td>
                      @endforeach
                    </tbody>
                  </table>
                  @endif
                </div>
  
                <div class="tab-pane" role="tabpanel" id="atc-tab-3">
                  @if (count($book2) == 0)
                  {{__('lp/lp_index.nobook_2')}}
                  @else
                  <table class="table">
                    <thead class="thead">
                      <tr>
                        <th scope="col">{{__('lp/lp_index.position')}}</th>
                        <th scope="col">{{__('lp/lp_index.name')}}</th>
                        <th scope="col">{{__('lp/lp_index.hours')}}</th>
                        <th scope="col">{{__('lp/lp_index.rating')}}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($book2 as $b)
                      <th scope="row">{{$b['position']}}</th>
                      <td>{{$b['user']['fname']}} {{$b['user']['lname']}} ({{$b['vatsim_id']}})</td>
                      <td>{{$b['time']}}</td>
                      <td>{{$b['user']['atc_rating_short']}}</td>
                      @endforeach
                    </tbody>
                  </table>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container-fluid py-4">
    <div class="container">
      <div class="row">
        <div class="col-sm">
          <h3 class="font-weight-medium">Live Map</h3>
        </div>
        <div class="col-md-6">
          <h3 class="white-text mt-2">{{__('lp/lp_index.online_atc_title')}}</h3>
          <ul class="list-unstyled ml-0 mt-3 p-0 onlineControllers">
            <li class="mb-2">
              <table class="table mt-4">
                <thead class="thead">
                  <tr>
                    <th scope="col">{{__('lp/lp_index.position')}}</th>
                    <th scope="col">{{__('lp/lp_index.name')}}</th>
                    <th scope="col">{{__('lp/lp_index.livesince')}}</th>
                    <th scope="col">{{__('lp/lp_index.rating')}}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($atconline as $a)
                    <tr>
                      <th class="position" scope="row">{{$a['callsign']}}</th>
                      <td>{{$a['name']}}</td>
                      <td>{{$a['livesince']}}z</td>
                      <td>{{$a['rating']}}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              @if (count($atconline) == 0)
                <p style="vertical-align: middle; text-align: center;">{{__('lp/lp_index.noatc')}}</p>
              @endif
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

@endsection