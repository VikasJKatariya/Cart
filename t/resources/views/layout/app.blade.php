<!DOCTYPE html>
<html>
 @include('includes.head')
<body class="hold-transition skin-blue sidebar-mini">
  <style type="text/css">
    
#loader-wrapper {
    background-color: rgba(0, 0, 0, 0.6);
    height: 100%;
    left: 0;
    position: fixed;
    top: 0;
    transition: all 0.4s ease-out 0s;
    width: 100%;
    z-index: 9999;
}
#loader {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    animation: 2s linear 0s normal none infinite running spin;
    border-color: #3c8dbc transparent transparent;
    border-image: none;
    border-radius: 50%;
    border-style: solid;
    border-width: 3px;
    display: block;
    height: 80px;
    left: 50%;
    margin: -40px 0 0 -40px;
    position: relative;
    top: 50%;
    width: 80px;
    z-index: 1001;
}
#loader::before {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    animation: 3s linear 0s normal none infinite running spin;
    border-color: #95cae8 transparent transparent;
    border-image: none;
    border-radius: 50%;
    border-style: solid;
    border-width: 3px;
    bottom: 5px;
    content: "";
    left: 5px;
    position: absolute;
    right: 5px;
    top: 5px;
}
#loader::after {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    animation: 1.5s linear 0s normal none infinite running spin;
    border-color: #c6c6c6 transparent transparent;
    border-image: none;
    border-radius: 50%;
    border-style: solid;
    border-width: 3px;
    bottom: 15px;
    content: "";
    left: 15px;
    position: absolute;
    right: 15px;
    top: 15px;
}
@keyframes spin {
0% {
    transform: rotate(0deg);
}
100% {
    transform: rotate(360deg);
}
}
#loader-wrapper .loader-section {
    background: #fff none repeat scroll 0 0;
    height: 100%;
    position: fixed;
    top: 0;
    width: 51%;
    z-index: 1000;
}
#loader-wrapper .loader-section.section-left {
    display: none;
    left: 0;
}
#loader-wrapper .loader-section.section-right {
    display: none;
    right: 0;
}
.loaded #loader-wrapper .loader-section.section-left {
    background: transparent none repeat scroll 0 0;
    transition: all 0.9s ease-out 0s;
}
.loaded #loader-wrapper .loader-section.section-right {
    background: transparent none repeat scroll 0 0;
    transition: all 0.9s ease-out 0s;
}
.loaded #loader {
    opacity: 0;
}
.loaded #loader-wrapper {
    background: transparent none repeat scroll 0 0;
    visibility: hidden;
}
  </style>
	<div id="loader-wrapper">
    <div id="loader"></div>
    <div class="loader-section section-left"></div>
    <div class="loader-section section-right"></div>
</div>
<div class="wrapper">
	@include('includes.header')
	@include('includes.sidebar')
 <!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
   		@yield('content')
  	</div>
  
  	@include('includes.footer')
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
@include('includes.scripts')
</body>
</html>
