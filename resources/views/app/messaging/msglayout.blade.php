@extends('layouts.app')

@section('page-title')
  @yield('title')
@endsection

@section('page-header')
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>@yield('header')</h1>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
@endsection

@section('page-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
          <a href="#" class="btn btn-primary btn-block mb-3">Compose</a>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Folders</h3>
            </div>
            <div class="card-body p-0">
              <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                  <a href="{{ route('app.inmsg.inbox', app()->getLocale()) }}" class="nav-link @if (Route::is('app.inmsg.inbox')) active @endif">
                    <i class="fas fa-inbox"></i> Inbox
                    @if (!session()->get('inbox_count') == 0)
                    <span class="badge bg-warning float-right">{{ session()->get('inbox_count') }}</span>
                    @endif
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('app.inmsg.mentoring', app()->getLocale()) }}" class="nav-link @if (Route::is('app.inmsg.mentoring')) active @endif">
                    <i class="fa fa-graduation-cap"></i> Mentoring
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('app.inmsg.sent', app()->getLocale()) }}" class="nav-link @if (Route::is('app.inmsg.sent')) active @endif">
                    <i class="far fa-envelope"></i> Sent
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('app.inmsg.archive', app()->getLocale()) }}" class="nav-link @if (Route::is('app.inmsg.archive')) active @endif">
                    <i class="fa fa-archive"></i> Archived
                  </a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('app.inmsg.trash', app()->getLocale()) }}" class="nav-link @if (Route::is('app.inmsg.trash')) active @endif">
                    <i class="far fa-trash-alt"></i> Trash
                  </a>
                </li>
              </ul>
            </div>
            <!-- /.card-body -->
          </div>
        </div>
        <div class="col-md-9">
          @yield('body')
        </div>
    </div>
</div>
@endsection