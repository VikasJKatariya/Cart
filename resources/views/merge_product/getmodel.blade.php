<form  action="{{ route('merge_product.store') }}"  autocorrect="off" autocomplete="off" method="post" class="form-horizontal form-bordered submitform">
   {{ csrf_field() }}
   <div class="row">
      <div class="col-lg-6 col-lg-4">
         <div class="form-group">
           <input type="hidden" name="product_id"  value="@if(!empty($product)){{ encrypt($product->id) }}@endif">
            <label><i class="fa fa-book" aria-hidden="true"></i>  Product <span style="color: red;">*</span></label>
             <select class="form-control" id="product_sku" required name="product_sku">
                 <option selected disabled="">Select Product</option>
                 @if(!empty($products))
                     @foreach($products as $produ)
                         <option
                             value="{{ $produ->sku }}" @if(!empty($product) && $product->sku == $produ->sku) {{ 'selected' }} @endif>{{ $produ->title }} </option>
                     @endforeach
                 @endif
            </select>
         </div>
      </div>
      <div class="col-lg-6 col-lg-4">
         <div class="form-group">
            <label><i class="fa fa-book" aria-hidden="true"></i> Product name <span style="color: red;">*</span></label>
            <input type="text" class="form-control  pricetab"  name="title" placeholder="Title" value="@if(!empty($product)){{ $product->title }}@endif"  required>
         </div>
      </div>
      <div class="col-lg-6 col-lg-4">
         <div class="form-group">
            <label><i class="fa fa-book" aria-hidden="true"></i> Quantity <span style="color: red;">*</span></label>
            <input type="number" class="form-control  pricetab"  name="quantity" placeholder="Quantity" value="@if(!empty($product)){{ $product->quantity }}@endif"  required>
         </div>
      </div>         
      <div class="col-lg-6 col-lg-4">
         <div class="form-group">
            <label><i class="fa fa-book" aria-hidden="true"></i> Selling Price <span style="color: red;">*</span></label>
            <input type="text" class="form-control  pricetab"  name="selling_price" placeholder="Selling Price" value="@if(!empty($product)){{ $product->selling_price }}@endif"  required>
         </div>
      </div>
      <div class="col-lg-6 col-lg-4">
         <div class="form-group">
            <label><i class="fa fa-book" aria-hidden="true"></i> Buying Price  <span style="color: red;">*</span></label>
            <input type="text" class="form-control  pricetab"  name="buying_price" placeholder="Buying Price " value="@if(!empty($product)){{ $product->buying_price }}@endif"  required>
         </div>
      </div>
      <div class="col-md-12">
         <div class="form-group">
            <button type="submit" class="btn btn-adminsqure pull-right submitbutton"> @if(!empty($product)) Update @else Add @endif  <span class="spinner"></span></button>
         </div>
      </div>
   </div>
</form>
