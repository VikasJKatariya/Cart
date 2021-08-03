<style type="text/css">
  .header_logo{
    max-width: 150px;
  }
</style>
  <header class="main-header">
    <!-- Logo -->
    <a href="{{ route('dashboard') }}" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>Project</b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img class="header_logo" src="{{ URL::asset('public/images/users_logos/1561727642.png') }}" height="75px" width="auto"></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      
      
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          @php $image = url('public/images/users/default.png'); @endphp
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="{{ $image }}" class="user-image" alt="User Image">
              <span class="hidden-xs">{{ auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu">
             
             <li class="user-header">
              
                <img src="{{ $image }}" class="img-circle" alt="User Image">

                <p>
                 {{ auth()->user()->name }} {{ auth()->user()->lastname }}
                </p>
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-right" style="margin-right: 90px;">
                	<a class="btn btn-default btn-flat" href="{{ route('logout') }}"
                     onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">
                      {{ __('Logout') }}
                  </a>
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                      {{ csrf_field() }}
                  </form>
                  
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
         
        </ul>
      </div>
    </nav>
  </header>