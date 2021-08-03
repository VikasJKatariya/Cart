<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item {{ activeMenu('product') }}">
        <a class="nav-link" href="{{ url('product') }}"><b>Product</b></a>
      </li>
      <li class="nav-item {{ activeMenu('order') }}">
        <a class="nav-link" href="{{ url('order') }}"><b>Order</b></a>
      </li>
      <li class="nav-item {{ activeMenu('calculate_profit_of_order_item_data') }}">
        <a class="nav-link" href="{{ url('calculate_profit_of_order_item_data') }}"><b>API</b></a>
      </li>
      <li class="nav-item {{ activeMenu('merge_product') }}">
        <a class="nav-link" href="{{ url('merge_product') }}"><b>Merge 2 Product</b></a>
      </li>
      <li class="nav-item  {{ activeMenu('profit_index') }}">
        <a class="nav-link" href="{{ url('profit_index') }}"><b>Top 5 profitable item</b></a>
      </li>
      <li class="nav-item  {{ activeMenu('selling_index') }}">
        <a class="nav-link" href="{{ url('selling_index') }}"><b>Top 5 selling item</b></a>
      </li>
      <li class="nav-item  {{ activeMenu('table') }}">
        <a class="nav-link" href="{{ url('table') }}"><b>Table</b></a>
      </li>
    </ul>
  </div>
</nav>