/* All Scripts

    1. Date Picker
    2. Testimonial Slider
    3. How it slider
    

    */

    $( document ).ready(function() {

      /* 1. Date Picker */
      var today = new Date(); 

      $("#start_date").datepicker({ 
        uiLibrary: 'bootstrap',
        dateFormat:'yy-mm-dd',           
        autoclose: true,
        minDate: today
      })

        $("#end_date").datepicker({ 
          uiLibrary: 'bootstrap',
          dateFormat:'yy-mm-dd',           
          autoclose: true,
          minDate: today
        })
      

      $('#datepicker').datepicker({
        uiLibrary: 'bootstrap',
        minDate: new Date()
      });

      $('#datepicker1').datepicker({
        uiLibrary: 'bootstrap',
        minDate: new Date()
      });

      $('#datepicker2').datepicker({
        uiLibrary: 'bootstrap'
      });
      $('#datepicker3').datepicker({
        uiLibrary: 'bootstrap'
      });

      /* 2. Testimonial Slider */

      if (jQuery('.testimonailSlider').length > 0) {
        jQuery('.testimonailSlider').slick({
          dots: false,
          infinite: true,
          speed: 2000,
          slidesToShow: 1,
          slidesToScroll: 1,
          appendArrows: ".testimonialArrow",
          prevArrow: '<div class="testiArrowLeft pull-left trans"></div>',
          nextArrow: '<div class="testiArrowRight pull-right trans"></div>',
          arrows: true,
          fade: true

        });
      }

     // dashboaedjs start

     $(".closeIcon").click(function(){
       $('.left_sidebar').removeClass('opndash');
     });
     $(".filBy").click(function(){

      $('.left_sidebar').addClass('opndash');
    });


     // dashboaedjs end


     $(".howItSlider")
     .length && $(".howItSlider")
     .on("afterChange init", function (e, t, a) {
      t.$slides.removeClass("prevdiv")
      .removeClass("nextdiv");
      for (var s = 0; s < t.$slides.length; s++) {
        var o = $(t.$slides[s]);
        if (o.hasClass("slick-current")) {
          o.prev()
          .addClass("prevdiv"), o.next()
          .addClass("nextdiv");
          break
        }
      }
      var i = $(".text1[data-animation]")
      .attr("data-animation"),
      n = (s = $(".text2[data-animation]")
        .attr("data-animation"), $(".text3[data-animation]")
        .attr("data-animation")),
      l = $(".text4[data-animation]")
      .attr("data-animation");
      $(".slick-active .text1")
      .addClass(i), $(".slick-active .text2")
      .addClass(s), $(".slick-active .text3")
      .addClass(n), $(".slick-active .text4")
      .addClass(l), $(".prevdiv .text1")
      .removeClass(i), $(".prevdiv .text2")
      .removeClass(s), $(".prevdiv .text3")
      .removeClass(n), $(".prevdiv .text4")
      .removeClass(n), $(".nextdiv .text1")
      .removeClass(i), $(".nextdiv .text2")
      .removeClass(s), $(".nextdiv .text3")
      .removeClass(n), $(".nextdiv .text4")
      .removeClass(l)
    })
     .on("beforeChange", function (e, t) {
      t.$slides.removeClass("prevdiv")
      .removeClass("nextdiv")
    })
     .slick({
      dots: true,
      infinite: true,
      speed: 2000,
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      fade: true
    }) 

     searchOverlay();







   });


    /*  A. Searchbar Overlay  */

    function searchOverlay(){

      var triggerBttn = document.getElementById( 'trigger-overlay' ),
      overlay = document.querySelector( 'div.overlay' ),
      closeBttn = overlay.querySelector( '.overlay-close' );
      transEndEventNames = {
        'WebkitTransition': 'webkitTransitionEnd',
        'MozTransition': 'transitionend',
        'OTransition': 'oTransitionEnd',
        'msTransition': 'MSTransitionEnd',
        'transition': 'transitionend'
      },
      transEndEventName = transEndEventNames[ Modernizr.prefixed( 'transition' ) ],
      support = { transitions : Modernizr.csstransitions };
      s = Snap( overlay.querySelector( 'svg' ) ), 
      path = s.select( 'path' ),
      pathConfig = {
        from : path.attr( 'd' ),
        to : overlay.getAttribute( 'data-path-to' )
      };

      function toggleOverlay() {
        if( classie.has( overlay, 'open' ) ) {
          classie.remove( overlay, 'open' );
          classie.add( overlay, 'close' );

          var onEndTransitionFn = function( ev ) {
            classie.remove( overlay, 'close' );
          };

          path.animate( { 'path' : pathConfig.from }, 400, mina.linear, onEndTransitionFn );
        }
        else if( !classie.has( overlay, 'close' ) ) {
          classie.add( overlay, 'open' );
          path.animate( { 'path' : pathConfig.to }, 400, mina.linear );
        }
      }

      triggerBttn.addEventListener( 'click', toggleOverlay );
      closeBttn.addEventListener( 'click', toggleOverlay );
    }


    // slick slider
    $('.slider-for').slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      fade: false,
      asNavFor: '.slider-nav'
    });
    $('.slider-nav').slick({
      slidesToShow: 4,
      slidesToScroll: 1,
      asNavFor: '.slider-for',
      appendArrows: ".customerArrows",
      prevArrow: '<div class="CatArrowLeft pull-left trans"><i class="fa fa-angle-left" aria-hidden="true"></i></div>',
      nextArrow: '<div class="CatArrowRight pull-right trans"><i class="fa fa-angle-right" aria-hidden="true"></i></div>',
      arrows: true,
      dots: false,
      centerMode: false,
      focusOnSelect: false,

      responsive: [

      ]





    });
    
     // form js
    $(document).ready(function () {
    //Initialize tooltips
    $('.nav-tabs > li a[title]').tooltip();
    
    //Wizard
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {

      var $target = $(e.target);
      
      if ($target.parent().hasClass('disabled')) {
        return false;
      }
    });

    $(".next-step").click(function (e) {

      var $active = $('.wizard .nav-tabs li.active');
      $active.next().removeClass('disabled');
      nextTab($active);

    });
    $(".prev-step").click(function (e) {

      var $active = $('.wizard .nav-tabs li.active');
      prevTab($active);

    });
  });

    function nextTab(elem) {
      $(elem).next().find('a[data-toggle="tab"]').click();
    }
    function prevTab(elem) {
      $(elem).prev().find('a[data-toggle="tab"]').click();
    }


    // owl start
    var owl = $("#testimonial");
    owl.owlCarousel({
    items: 1, //10 items above 1000px browser width
    nav: true,
    navigation: true,
    pagination: true,
    touchDrag: true,
    paginationSpeed: 500,
    dots: false,
    margin: 100,
    loop: true,
    mouseDrag: true,
    autoplay: false,
    navText: ["<i class='fa fa-long-arrow-left'></i>",
    "<i class='fa fa-long-arrow-right'></i>"],
    responsive: {
      1200: {
        items: 1
      },
      1024: {
        items: 1
      },
      768: {
        items: 1
      },
      480: {
        items: 1
      },
      300: {
        items: 1
      }
    }
  });