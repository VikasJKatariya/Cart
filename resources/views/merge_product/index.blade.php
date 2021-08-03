@extends('layouts.app')
@section('content')
@section('pageTitle', 'Merge Product')
<div class="col-md-12" style="text-align: right;margin-top: 10px;margin-bottom: 10px;">
    <button type="button"  data-toggle="modal" data-target=".modal_edit_list" class="btn btn-adminsqure openform"  ><i class="fa fa-plus"></i> Add</button>
</div>
<div class="col-md-12">
<table class="table table-bordered table-hover datasample" >
 <thead>
    <tr>
       <th>#</th>
       <th>SKU</th>
       <th>Title</th>
       <th>Quantity</th>
       <th>Selling price</th>
       <th>Buying price</th>
       <th>Date</th>
       <th>Action</th>
    </tr>
 </thead>
 <tbody>
 </tbody>
</table>
</div>
<div class="modal fade modal_edit_list" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title addorder" id="exampleModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      </div>
    </div>
  </div>
</div>
<link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/pnotify/2.0.0/pnotify.all.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/pnotify/2.0.0/pnotify.all.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
    var table = $('.datasample').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            'url': "{{ route('merge_product.getallmergeproduct') }}",
            'type': 'POST',
            'data': function ( d ) {
                d._token = "{{ csrf_token() }}";

            },
            complete: function() {
                $('[data-toggle="tooltip"]').tooltip();
           },
        },
        columns: [
        { data: 'id'},
        { data: 'sku'},
        { data: 'title'},
        { data: 'quantity'},
        { data: 'selling_price'},
        { data: 'buying_price'},
        { data: 'created_at'},
        { data: 'action'},
        ]
    });

    $('body').on('click', '.openform', function() {
            var id = $(this).data('id');
            if(id == null && id == undefined){
                $('.addorder').text('Add Child Product');
            }else{
                $('.addorder').text('Update Child Product');
            }

            $.ajax({
                 url: "{{ route('merge_product.getmergeproductmodel')}}",
                 type: 'POST',
                 headers: {
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 data: {
                     id: id
                 },
                 beforeSend: function() {
                     $('.modal-body').html('<h5>Loading..</h5>')
                 },
                 success: function(data) {
                     $('.modal-body').html(data);
                     $('.classfocus').focus();
                     $(".submitform").validate({
                    rules: {
                      title:
                        {
                          maxlength: 30,
                          required: true,
                        },
                        selling_price:
                        {
                          required: true,
                          number: true
                        },
                        buying_price:
                        {
                          required: true,
                          number: true
                        },
                        quantity:
                        {
                          required: true,
                          number: true
                        }
                    },

                });
                },
             });
        });

        $('body').on('submit','.submitform',function(e){
        e.preventDefault();
        $.ajax({
            url : $(this).attr('action'),
            data: new FormData(this),
            type: 'POST',
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
              $('.spinner').html('<i class="fa fa-spinner fa-spin"></i>');
              $('.submitbutton').prop( "disabled", true );

            },
            success:function(data){
            $('.submitbutton').prop( "disabled", false );
             if(data.status == 400){
                $('.spinner').html('');
                toastr.error(data.msg, 'Oh No!');
             }
              if(data.status == 200){
                 $('.spinner').html('');

                 $('.datasample').DataTable().ajax.reload();
                 $('.modal_edit_list').modal('hide');
                 toastr.success(data.msg, 'Success!');
              }

            },
        });
        });

          $('body').on('click','.delete_product',function(){
             var id = $(this).data('id');

             (new PNotify({
                 title: "Confirmation Needed",
                 text: "Are you sure you wants to delete?",
                 icon: 'glyphicon glyphicon-question-sign',
                 hide: false,
                 confirm: {
                     confirm: true
                 },
                 buttons: {
                     closer: false,
                     sticker: false
                 },
                 history: {
                     history: false
                 },
                 addclass: 'stack-modal',
                 stack: {
                     'dir1': 'down',
                     'dir2': 'right',
                     'modal': true
                 }
             })).get().on('pnotify.confirm', function() {
                 $.ajax({
                     url : '{{ url("product/") }}/' + id,
                     type: 'DELETE',
                     headers: {
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                     beforeSend: function(){
                     },
                     success:function(data){
                         if(data.status == 400) {
                             toastr.error(data.msg, 'Oh No!');

                         }
                         if(data.status == 200) {
                             $(".datasample").DataTable().ajax.reload();
                             toastr.success('Product deleted successfully.', 'Success!');
                         }

                     },
                     error: function(){
                         toastr.error('Something went wrong!', 'Oh No!');

                     }
                 });
             });
         });
});
</script>
@endsection