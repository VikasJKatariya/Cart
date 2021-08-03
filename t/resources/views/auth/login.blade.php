
<!DOCTYPE html>
<html lang="zxx">
    <head>
        <title>Login | Project</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />

        <!-- Google / Search Engine Tags -->

        <meta itemprop="image" content="{{ URL::asset('public/login/banner1-old.png') }}">

        <!-- Facebook Meta Tags -->
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Project">
        <meta property="og:description" content="Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged">
        <meta property="og:image" content="{{ URL::asset('public/login/banner1-old.png') }}">

        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">

        <meta name="twitter:description" content="Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged">
        <meta name="twitter:image" content="{{ URL::asset('public/login/banner1-old.png') }}">

        <link rel="shortcut icon" href="{{ URL::asset('public/login/favicon.png') }}" />


        <link rel="stylesheet" href="{{ URL::asset('public/login/style.css') }}" type="text/css" media="all" />

        <style>
            .errorstyle, .form-bar{
                color: #e64848eb;
            }
        </style>
    </head>

   <body>
      <meta name="robots" content="noindex">
      <body>
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
         <!-- New toolbar-->
         <style>
            * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }
            .toggle-right-sidebar span {
            background: #0D1326;
            width: 50px;
            height: 50px;
            line-height: 50px;
            text-align: center;
            color: #e6ebff;
            border-radius: 50px;
            font-size: 26px;
            cursor: pointer;
            opacity: .5;
            }
            .pull-right {
            float: right;
            position: fixed;
            right: 0px;
            top: 70px;
            width: 90px;
            z-index: 99999;
            text-align: center;
            }
            /* ============================================================
            RIGHT SIDEBAR SECTION
            ============================================================ */
            #right-sidebar {
            width: 90px;
            position: fixed;
            height: 100%;
            z-index: 1000;
            right: 0px;
            top: 0;
            margin-top: 60px;
            -webkit-transition: all .5s ease-in-out;
            -moz-transition: all .5s ease-in-out;
            -o-transition: all .5s ease-in-out;
            transition: all .5s ease-in-out;
            overflow-y: auto;
            }
            /* ============================================================
            RIGHT SIDEBAR TOGGLE SECTION
            ============================================================ */
            .hide-right-bar-notifications {
            margin-right: -300px !important;
            -webkit-transition: all .3s ease-in-out;
            -moz-transition: all .3s ease-in-out;
            -o-transition: all .3s ease-in-out;
            transition: all .3s ease-in-out;
            }
            .icon {
                position: absolute;
                top: 48px;
                right: 15px;
                z-index: 1000;
            }
         </style>

         </div>
         <!-- /login-section -->
         <section class="w3l-login-6">
            <div class="login-hny">
               <div class="form-content">

                  <div class="form-right">
                     <div class="overlay">
                        <div class="grid-info-form">
                           <h5>Say hello</h5>
                           <h3>LOGIN ACCOUNT </h3>
                           <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Est natus facere aperiam!
                              Tenetur maiores dolore a quod pariatur ut voluptates quae saepe ea quasi laudantium,
                              iste molestias inventore fuga assumenda.
                           </p>
                           <a href="#" class="read-more-1 btn">Get Started</a>
                        </div>
                     </div>
                  </div>
                  <div class="form-left" style="padding-bottom: 30px">
                     <h3>Login</h3>
                     <form method="POST" class="signin-form" action="{{ route('login') }}">
                        @csrf

                        <div class="form-input">
                           <label>Email</label>
                           <input  type="email" name="email" placeholder="Email" value="{{ old('email') }}" required >
                           @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong style="color: #b52f2f; font-size: 14px; ">{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="form-input" style="position: relative">
                           <label>Password</label>
                            <input id="password" type="password" name="password" placeholder="Password" required>
                            <i class="fa fa-eye icon toggle-password"></i>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong style="color: #b52f2f; font-size: 14px; ">{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button class="btn">Login</button>
                   
                     </form>

                  </div>
               </div>
            </div>
         </section>
         <!-- //login-section -->
          <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous"></script>
          <script>
              $(function(){
                  $("body").on('click', '.toggle-password', function() {
                      $(this).toggleClass("fa-eye fa-eye-slash");
                      var input = $('#password');
                      if (input.attr("type") == "password") {
                          input.attr("type", "text");
                      } else {
                          input.attr("type", "password");
                      }

                  });
              });

          </script>
   </body>
</html>
