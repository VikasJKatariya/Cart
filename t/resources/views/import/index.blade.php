@extends('layout.app')
@section('content')
@section('pageTitle', 'import')
@include('includes.topbar')
<style type="text/css">
  .btn-primary{
    background: #6eb32b!important;
  }
  .drop-zone {
        width: 100%;
        height: 200px;
        padding: 25px;
        display: flex;
        align-items: center;
        justify-items: center;
        text-align: center;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        font-weight: 500;
        font-size: 20px;
        cursor: pointer;
        color: #7e7e7e;
        border: 3px dashed lightgrey;
        border-radius: 10px;
      }
      .drop-zone--over {
        border-style: solid;
      }
      .drop-zone__input {
        display: none;
      }
      .drop-zone__thumb {
        width: 100%;
        height: 100%;
        border-radius: 10px;
        overflow: hidden;
        background-color: #ccc;
        background-size: cover;
        position: relative;
      }
      .drop-zone__thumb::after {
        content: attr(data-label); /*  displays text of data-lable*/
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 5px 0;
        color: white;
        background: rgba(0, 0, 0, 0.75);
        text-align: center;
        font-size: 14px;
      }
</style>

<div class="container">
  <!--begin::Notice-->
  <div class="card card-custom gutter-b">
    <div class="card-header flex-wrap py-3">
      <div class="card-title" style="margin-bottom: 15px;
    margin-right: 90px;
    margin-left: 30px;">
        <h3 class="card-label" >Import your data
        <span class="d-block text-muted pt-2 font-size-sm">Drag and drop your data below or click to select a file. You have to upload it in the correct format.<br> Any errors will be shown once uploaded.</span></h3>
      </div>
      <div class="card-toolbar">
        <!--begin::Dropdown-->
      </div>
    </div>
    <div class="card-body">
      <div class="form-group row" style="margin-bottom: 15px;
    margin-right: 90px;
    margin-left: 30px;">
         <form action="{{ url('import/importsubmit') }}" enctype="multipart/form-data" method="post" style="display: block; width: 100%;" class="importexcel">
          <button style="float: right;margin-bottom: 15px; border: none;" class="btn btn-primary font-weight-bolder "> Import <span class="spinnernn"></span></button>
         {{ csrf_field() }}
        <div class="col-lg-12 col-md-12 col-sm-12" style="padding: 0px; float: left;">
           <div class="drop-zone">
                <span class="drop-zone__prompt" style="width: 100%; ">Drop file or click to upload<br><span style="font-size: 15px;">Only excel is supported. Any errors will be shown once uploaded.</span></span>
                
                <!-- <div class="drop-zone__thumb" data-label="myfile.txt"></div> -->
                <input type="file" name="file" class="drop-zone__input" />
                <!-- add multiple attribute to input to support uploading more than one file-->
              </div>
        </div>
        <div class="return"></div>
        
        </form>
      </div>
    </div>
  </div>
<!--end::Container-->
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
    <script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
<script type="text/javascript">
  $(function(){

    //*******************************************************
    //importexcel submit
    //Author : Vikas Katariya
    //Date : 13-07-2021
    //*******************************************************  

    $('body').on('submit', '.importexcel', function (e) {
          e.preventDefault();
          $.ajax({
                     
              url: $(this).attr('action'),
              data: new FormData(this),
              type: 'POST',
              contentType: false,
              cache: false,
              processData: false,
              beforeSend: function () {
                  $('.spinnernn').html('<i class="fa fa-spinner fa-spin"></i>')
                  $("body").addClass("loading");
              },
              success: function (data) {

                  $("body").removeClass("loading"); 
                  $('.spinnernn').html('')
                  
                  if(data.status == 201){
                    toastr.error(data.msg, 'Oh No!');
                      $('.return').html(data.return);
                      
                  }
                  if (data.status == 400) {

                       toastr.error(data.msg, 'Oh No!');
                  }
                  if (data.status == 200) {
                       toastr.success('Your File has been imported successfully.', 'Success!');
                       window.setTimeout(function(){
                          window.location.href = "{{ url('users') }}";
                      }, 3000);
                  }
              },
              error: function (jqXHR, textStatus, errorThrown) {
                $('.spinnernn').html('')
                  if (jqXHR.status == 500) {
                     // Swal.fire({
                     //      position: "top-right",
                     //      icon: "error",
                     //      title: "Internal error:"+ jqXHR.responseText,
                     //      showConfirmButton: false,
                     //      timer: 5000
                     //  });
                     toastr.error(jqXHR.responseText, 'Oh No!');
                      
                  } else {
                      alert('Unexpected error.');
                  }
              }
          });
        });

    //*******************************************************
    //DropZone Js 
    //Author : Vikas Katariya
    //Date : 13-07-2021
    //*******************************************************  

    document.querySelectorAll(".drop-zone__input").forEach((inputElement) => {
        const dropZoneElement = inputElement.closest(".drop-zone");

        dropZoneElement.addEventListener("click", (event) => {
          inputElement.click(); /*clicking on input element whenever the dropzone is clicked so file browser is opened*/
        });

        inputElement.addEventListener("change", (event) => {
          if (inputElement.files.length) {
            updateThumbnail(dropZoneElement, inputElement.files[0]);
          }
        });

        dropZoneElement.addEventListener("dragover", (event) => {
          event.preventDefault(); /*this along with prevDef in drop event prevent browser from opening file in a new tab*/
          dropZoneElement.classList.add("drop-zone--over");
        });
        ["dragleave", "dragend"].forEach((type) => {
          dropZoneElement.addEventListener(type, (event) => {
            dropZoneElement.classList.remove("drop-zone--over");
          });
        });
        dropZoneElement.addEventListener("drop", (event) => {
          event.preventDefault();
          console.log(
            event.dataTransfer.files
          ); /*if you console.log only event and check the same data location, you won't see the file due to a chrome bug!*/
          if (event.dataTransfer.files.length) {
            inputElement.files =
              event.dataTransfer.files; /*asigns dragged file to inputElement*/

            updateThumbnail(
              dropZoneElement,
              event.dataTransfer.files[0]
            ); /*thumbnail will only show first file if multiple files are selected*/
          }
          dropZoneElement.classList.remove("drop-zone--over");
        });
      });
      function updateThumbnail(dropZoneElement, file) {
        let thumbnailElement = dropZoneElement.querySelector(
          ".drop-zone__thumb"
        );
        /*remove text prompt*/
        if (dropZoneElement.querySelector(".drop-zone__prompt")) {
          dropZoneElement.querySelector(".drop-zone__prompt").remove();
        }

        /*first time there won't be a thumbnailElement so it has to be created*/
        if (!thumbnailElement) {
          thumbnailElement = document.createElement("div");
          thumbnailElement.classList.add("drop-zone__thumb");
          dropZoneElement.appendChild(thumbnailElement);
        }
        thumbnailElement.dataset.label =
          file.name; /*takes file name and sets it as dataset label so css can display it*/

        /*show thumbnail for images*/
        if (file.type.startsWith("image/")) {
          const reader = new FileReader(); /*lets us read files to data URL*/
          reader.readAsDataURL(file); /*base 64 format*/
          reader.onload = () => {
            thumbnailElement.style.backgroundImage = `url('${reader.result}')`; /*asynchronous call. This function runs once reader is done reading file. reader.result is the base 64 format*/
            thumbnailElement.style.backgroundPosition = "center";
          };
        } else {
          thumbnailElement.style.backgroundImage = null; /*plain background for non image type files*/
        }
      }
  })
</script>
    
@endsection