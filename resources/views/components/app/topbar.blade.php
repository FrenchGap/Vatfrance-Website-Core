<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="#" class="nav-link">
        <span>
          {{__('app_menus.local_time')}}:
        </span>
        <span id="local_time"></span>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="#" class="nav-link">
        <span>
          {{__('app_menus.utc_time')}}: 
        </span>
        <span id="utc_time"></span>
      </a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <!-- Messages Dropdown Menu -->
    <li class="nav-item dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link">
        {{ strtoupper(app()->getLocale()) }}
      </a>
      <div class="dropdown-menu">
        @foreach (config('app.available_locales') as $locale)
          <a href="{{ route(\Illuminate\Support\Facades\Route::currentRouteName(), $locale) }}" class="dropdown-item"
            @if (app()->getLocale() == $locale) style="font-weight: bold; text-decoration: underline" @endif>
            {{ strtoupper($locale) }}
          </a>
        @endforeach
      </div>
    </li>
    <li class="nav-item">
      <a href="{{ route('landingpage.home', app()->getLocale()) }}" class="nav-link">
        {{__('app_menus.return_lp')}}
      </a>
    </li>
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <i class="far fa-user"></i> {{ Auth::user()->fname}} {{ Auth::user()->lname }}
      </a>
      <div class="dropdown-menu">
        <a href="{{ route('app.index', app()->getLocale()) }}" class="dropdown-item">
          {{__('app_menus.my_profile')}}
        </a>
        <a href="{{ route('auth.logout', app()->getLocale()) }}" class="dropdown-item">
          {{__('app_menus.logout')}}
        </a>
      </div>
    </li>
  </ul>
</nav>
<!-- /.navbar -->

<script>
  // Local and UTC time scripts
  function startTime() {
    var today = new Date();
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    var uh = today.getUTCHours();
    var um = today.getUTCMinutes();
    var us = today.getUTCSeconds();
    m = checkTime(m);
    s = checkTime(s);
    um = checkTime(um);
    us = checkTime(us);
    document.getElementById('local_time').innerHTML = h + ":" + m + ":" + s;
    document.getElementById('utc_time').innerHTML = uh + ":" + um + ":" + us;
    var t = setTimeout(startTime, 500);
  };
  function checkTime(i) {
    if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
    return i;
  };
</script>