@extends('layouts.app')
@section('content')
@section('pageTitle', 'product')
<div class="col-md-12">
<div class="row">
  <div class="col-lg-3">
     <div class="form-group">
        <input type="hidden" name="orderid"  value="@if(!empty($order)){{ encrypt($order->id) }}@endif">
        <label><i class="fa fa-book" aria-hidden="true"></i> Parent child filter product <span style="color: red;">*</span></label>
         <select class="form-control" id="product_search" required name="product_search">
            <option selected disabled="">Select Product</option>
            <option value="all_product"> All product </option>
            <option value="parent_product">Parent Product </option>
        </select>
     </div>
  </div>
  <div class="col-lg-3">
      <button type="text" id="btnFiterSubmitSearch" class="btn btn-info" style="    margin-top: 30px;">Submit</button>
  </div>
</div>
</div>
<div class="col-md-12">
<table class="table table-bordered table-hover datasample" >
 <thead>
    <tr>
       <th>#</th>
       <th>SKU</th>
       <th>Title</th>
       <th>Order ID</th>
       <th>Total</th>
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
        <h5 class="modal-title" id="exampleModalLabel">Add product</h5>
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
            'url': "{{ route('top_profit') }}",
            'type': 'POST',
            'data': function ( d ) {
                d._token = "{{ csrf_token() }}";
                d.product_search = $('#product_search').val()

            },
            complete: function() {
                $('[data-toggle="tooltip"]').tooltip();
           },
        },
        columns: [
        { data: 'id'},
        { data: 'sku'},
        { data: 'title'},
        { data: 'order_id'},
        { data: 'sum'},
        ]
    });

    $('#btnFiterSubmitSearch').click(function(){
     $('.datasample').DataTable().draw(true);
});
});
</script>
@endsection