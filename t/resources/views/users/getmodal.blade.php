

<style type="text/css">
    .formsubmit label.error {
        font-weight: 100 !important;
    }
</style>

<form class="form-some-up form-block formsubmit" role="form" action="{{ route('users.store') }}" method="post">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <input type="hidden" name="userid" value="@if(!empty($user)){{ encrypt($user->id) }}@endif">

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                 <label>Name</label>
                <input type="text" class="form-control " name="name"
                       placeholder="Name"
                       value="@if(!empty($user)){{ $user->name }}@endif" required="" maxlength="30">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
               <label>Last Name</label>
                <input type="text" class="form-control" name="last_name"
                       placeholder="Last Name"
                       value="@if(!empty($user)){{ $user->last_name }}@endif" required="" maxlength="30">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control " name="email"
                       placeholder="Email"
                       value="@if(!empty($user)){{ $user->email }}@endif" required="">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" class="form-control" name="phone" placeholder="Phone"
                       value="@if(!empty($user)){{ $user->phone }}@endif" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
               <label>Password</label>
                <input type="password" class="form-control password" id="password" minlength="6" name="password"
                       placeholder="password"
                       value="" @if(empty($user)) required @endif>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
               <label>Re-type password</label>
                <input type="password" class="form-control password_confirm" id="password_confirm" minlength="6" name="password_confirm"
                       placeholder="Confirm password" @if(empty($user)) required @endif>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group pull-right">
                <button type="submit" class="btn btn-primary submitbutton">@if(!empty($user)) Update @else
                        Add @endif <span class="spinner"></span></button>
            </div>
        </div>
    </div>


</form>
