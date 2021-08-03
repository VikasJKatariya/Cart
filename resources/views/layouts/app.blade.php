<!DOCTYPE html>
<html>
 @include('includes.head')
<body>
<div class="wrapper">
	@include('includes.header')
	@include('includes.sidebar')
	<div class="content-wrapper">
   		@yield('content')
  	</div>
  	@include('includes.footer')
</div>
@include('includes.scripts')
</body>
</html>
