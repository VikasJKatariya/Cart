@extends('layouts.app')
@section('content')
@section('pageTitle', 'Order')

<div class="col-md-12 tablebody">

</div>

<link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/pnotify/2.0.0/pnotify.all.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/pnotify/2.0.0/pnotify.all.min.js"></script>

<script type="text/javascript">
	   loadtable(1)
       function loadtable(page)
       {
	        $.ajax({
	            url : "{{route('table.gettable')}}"+"?page="+ page,
	            data: {
                	_token:"{{ csrf_token() }}"
            	},
	            type: 'POST',
	            beforeSend: function(){
	              $('.spinner').html('<i class="fa fa-spinner fa-spin"></i>');
	              $('.submitbutton').prop( "disabled", true );

	            },
	            success:function(data){

	            	$('.tablebody').html(data);
	            },
	        });
    	}

	    $(window).on('hashchange', function() {
	        if (window.location.hash) {
	            var page = window.location.hash.replace('#', '');
	            if (page == Number.NaN || page <= 0) {
	                return false;
	            }else{
	                loadtable(page);
	            }
	        }
	    });
    	$(document).ready(function()
		    {
		        $(document).on('click', '.pagination a',function(event)
		        {
	            event.preventDefault();
	  
	            $('li').removeClass('active');
	            $(this).parent('li').addClass('active');
	  
	            var myurl = $(this).attr('href');
	            var page=$(this).attr('href').split('page=')[1];
	  
	            loadtable(page);
	        });
    	});
      

</script>
@endsection