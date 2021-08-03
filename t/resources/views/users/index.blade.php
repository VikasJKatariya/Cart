@extends('layout.app')
@section('content')
@section('pageTitle', 'Users')

    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-md-12">
        <a href="#" data-toggle="modal" data-typeid="" data-target=".add_modal"
                       class="btn btn-info btn-sm openaddmodal" data-id="" style="float: right; ">
                        <i class="fa fa-plus"></i> Add New
        </a>
        </div>
        <div class="col-12">
            <div class="card card-info card-outline displaybl">
                <div class="card-body" style="padding: 10px 15px;">
                    <div class="col-lg-12">
                        <div class="form-group row " style="margin-bottom: 0px;">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><b>Search: </b>
                                    </label>
                                     <input type="text" class="form-control search" name="phone" placeholder="Search with full name email and phone" value="" required>
                                </div>
                            </div>
                            <div class="col-md-2" style="padding-left: 0px;">
                                <button class="btn btn-success btn-sm searchdata"
                                        style="margin-top: 27px;padding: 6px 16px;">Search <span
                                        class="spinner"></span>
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-danger btn-sm"
                                   style="margin-top: 27px;margin-left: 5px;padding: 6px 16px;cursor: pointer; ">
                                    <i class="fa fa-refresh" aria-hidden="true"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <div class="col-md-12">
            <div class="card  card-outline">
               
                <div class="card-body">
                    <!-- /.card-header -->
                    <table id="employee" class="table table-bordered table-hover" style="background: #fff;">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Full name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                    <!-- /.card-body -->
                    <!-- /.card -->
                </div>
            </div>
        </div>
            <!-- /.col -->
        </div>

    </div>
    <!-- /.row -->
</div>
<!--/. container-fluid -->
<div class="modal fade add_modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="padding: 5px 15px;">
                <h4 class="modal-title">Large Modal</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body addholidaybody">
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
</section>
<!-- /.modal -->
@push('script')
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
<script>
        $(function () {

            /**************************************************** validation *********************************************/ 

            jQuery.validator.methods.matches = function( value, element, params ) {
                var re = new RegExp(params);
                // window.console.log(re);
                // window.console.log(value);
                // window.console.log(re.test( value ));
                return this.optional( element ) || re.test( value );
            }

            /**************************************************** datatable *********************************************/

            $("#employee").DataTable({
                "responsive": true,
                "autoWidth": false,
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: {
                    'url': "{{ route('users.getall') }}",
                    'type': 'POST',
                    'data': function (d) {
                        d._token = "{{ csrf_token() }}";
                        d.search = $(".search").val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', "orderable": false},
                    {data: 'name'},
                    {data: 'email'},
                    {data: 'phone'},
                    {data: 'status'},
                    {data: 'action', orderable: false},
                ]
            });
            /*filter*/
            $('.searchdata').click(function () {
                event.preventDefault();
                $("#employee").DataTable().ajax.reload()
            })
        });

        /**************************************************** add update new user *********************************************/

        $('body').on('click', '.openaddmodal', function () {
            var id = $(this).data('id');
            if (id == '') {
                $('.modal-title').text('Add User');
            } else {
                $('.modal-title').text('Edit User');
            }
            $.ajax({
                url: "{{ route('users.getmodal')}}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {id: id},
                success: function (data) {
                    $('.addholidaybody').html(data);
                        $(".formsubmit").validate({
                        rules : {
                            name : {
                                maxlength : 30
                            },
                            last_name : {
                                maxlength : 30
                            },
                            password : {
                                minlength : 6
                            },
                            password_confirm : {
                                minlength : 6,
                                equalTo : "#password"
                            },
                            phone: {
                                required  : true,
                                matches   : "^(\\d|\\s)+$",
                                minlength : 10,
                                maxlength : 20
                            }
                        },
                        messages: {
                            phone: {
                                required: "this field is required",
                                matches : "please enter valid value."
                            },
                            recipient_name: {
                                required: "Enter recipient name",
                                minlength: "Name should be at least {0} characters long" // <-- removed underscore
                            }
                        },
                    });

                },
            });
        });

         /**************************************************** formsubmit *********************************************/

        $('body').on('submit', '.formsubmit', function (e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                data: new FormData(this),
                type: 'POST',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    $('.spinner').html('<i class="fa fa-spinner fa-spin"></i>')
                },
                success: function (data) {
                   
                    if (data.status == 400) {
                        $('.spinner').html('');
                        toastr.error(data.msg)
                    }
                    if (data.status == 200) {
                        $('.spinner').html('');
                        $('.add_modal').modal('hide');
                        $('#employee').DataTable().ajax.reload();
                        toastr.success(data.msg,'Success!')
                    }
                },
            });
        });

         /**************************************************** Delete record *********************************************/

        $('body').on('click', '.delete_record', function () {
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
            })).get().on('pnotify.confirm', function () {
                $.ajax({
                    url: '{{ url("users/") }}/' + id,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                    },
                    success: function (data) {
                        if (data.status == 400) {
                            toastr.error(data.msg, 'Oh No!');
                        }
                        if (data.status == 200) {
                            toastr.success(data.msg, 'Success!');
                            $("#employee").DataTable().ajax.reload();
                        }
                    },
                    error: function () {
                        toastr.error('Something went wrong!', 'Oh No!');
                    }
                });
            });
        });


        /**************************************************** changestatus *********************************************/

        $('body').on('click', '.changestatus', function () {
            var id = $(this).data('id');
            var status = $(this).data('status');
            (new PNotify({
                title: "Confirmation Needed",
                text: "Are you sure you wants to "+ status +" this record?",
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
            })).get().on('pnotify.confirm', function () {
                $.ajax({
                    url: '{{ route("users.changestatus") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: {id: id, status: status},
                    success: function (data) {
                        $("#employee").DataTable().ajax.reload();
                        toastr.success('Status changed successfully.', 'Success!');
                    },
                    error: function () {
                        toastr.error('Something went wrong!', 'Oh No!');

                    }
                });
            })

        });

        /**************************************************** end *********************************************/
    </script>
@endpush
@endsection
