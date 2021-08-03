<form  action="{{ route('order.store') }}"  autocorrect="off" autocomplete="off" method="post" class="form-horizontal form-bordered submitform">
   {{ csrf_field() }}
   <div class="row">
      <div class="col-lg-6 col-lg-4">
         <div class="form-group">
            <input type="hidden" name="orderid"  value="@if(!empty($order)){{ encrypt($order->id) }}@endif">
            <label><i class="fa fa-book" aria-hidden="true"></i>  Product <span style="color: red;">*</span></label>
             <select class="form-control" id="product_id" required name="product_id">
                 <option selected disabled="">Select Product</option>
                 @if(!empty($products))
                     @foreach($products as $product)
                         <option
                             value="{{ $product->sku }}" @if(!empty($order) && $order->sku_id == $product->sku) {{ 'selected' }} @endif>{{ $product->title }} </option>
                     @endforeach
                 @endif
            </select>
         </div>
      </div>
      <div class="col-lg-6 col-lg-4">
         <div class="form-group">
            <label><i class="fa fa-book" aria-hidden="true"></i> Item quantity <span style="color: red;">*</span></label>
            <input type="number" class="form-control  pricetab"  name="item_quantity" placeholder="Quantity" value="@if(!empty($order)){{ $order->item_quantity }}@endif"  required>
         </div>
      </div>
      <div class="col-md-12">
         <div class="form-group">
            <button type="submit" class="btn btn-adminsqure pull-right submitbutton"> @if(!empty($order)) Update @else Add @endif   <span class="spinner"></span></button>
         </div>
      </div>
   </div>
</form>
