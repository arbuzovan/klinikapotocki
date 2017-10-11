$('document').ready(function(){
	// phone mask
	$("#order_phone").mask("+7 (999) 999-99-99");

	$(".hasdatepicker").datepicker( {
		minDate: 'today'
	} );
// GO TOP
    window.onscroll = function() {
        goTop();
    };

    function goTop(){
      var scrolled = window.pageYOffset || document.documentElement.scrollTop;
      // var innerHeight = document.documentElement.clientHeight;

      if(scrolled >= 50){
        $('#go_top').css('right','0');
      }else if(scrolled < 50){
        $('#go_top').css('right','-100px');
      };
    };


    $('#go_top').click(function(){
        $('body,html').animate({
            scrollTop: 0
        }, 700);
    })
    // END GO TOP


	// Captcha
	$(function(){
		$('.reload').click(function(){
			var d = new Date();
			$('.captcha-img').attr('src', '/captcha.php?reset&' + d.getTime());
			return false;
		});
	});


	// Scroll
    $('.scroll').click(function(){
    	var idscroll;
	    	if($('a').is('#order-window')){
		        idscroll = '#order-window';
		        $.scrollTo(idscroll, 1000);
		        $('.window-wrap .form, .btn-close').fadeIn(300);
	    	} else if($('div').is('#order')){
	    		idscroll = '#order';
	    		$.scrollTo(idscroll, 1000);
	    	}
        return false;
    });

    // SELECT
	$(".chosen").chosen({
		disable_search_threshold: 10,
		width: "100%"
	});


	function windowToggle(){
		if($('.window-wrap .form').css('display') == 'block'){
			$('.window-wrap .form, .btn-close').fadeOut(300);
		}else{
			$('.window-wrap .form, .btn-close').fadeIn(300);
		};
	}

	$('.window-wrap a.btn, .btn-close').click(function(){
		windowToggle();
	});

	window.addEventListener("keydown", function(e){
		if (e.keyCode == 27) {
			$('.window-wrap .form, .btn-close').fadeOut(300);
		}
	}, true);


	// PRICE
	$(".accordion .caption").click(function(){
		if($(this).hasClass('active')){
			$(".caption").each(function(){
				$(this).removeClass('active').next('.accordion_row').fadeOut(500).closest('td').removeClass('blue-border');
			});
		}else{
			$(".caption").each(function(){
				$(this).removeClass('active').next('.accordion_row').fadeOut(500).closest('td').removeClass('blue-border');
			});
			$(this).addClass('active').next('.accordion_row').fadeIn(500).closest('td').addClass('blue-border');
		}
	});


    // AJAX FORM
    var currentForm;

    $('.ajax-send').click(function(){
        currentForm = "#" + $(this).closest('form').attr('id');
        //console.log(currentForm);
        $('.alert').each(function(){
            $(this).fadeOut(500)
        });
    });

    var options = {
        beforeSubmit:  validate,
        success:       showResponse
    };

    $("#medcenter").ajaxForm(options);

    function validate(formData, jqForm, options) {
        var form = jqForm[0];
        var dateArr1 = [];
        var dateArr2 = [];

		/*Current date*/
		dateArr1 = $('.current-date input').val().split('.');
		var currentDate = dateArr1[0] + '/' + dateArr1[1] + '/' + dateArr1[2];

        /* Order date*/
        dateArr2 = $('.hasdatepicker').val().split('.');
		var orderDate = dateArr2[1] + '/' + dateArr2[0] + '/' + dateArr2[2];

		if(!Date.parse(orderDate)){
			// console.log('date format err');
			$(currentForm).find(".date-format-msg").fadeIn();
			return false;
		}else if(Date.parse(currentDate) > Date.parse(orderDate)) {
			// console.log('date err');
			$(currentForm).find(".date-msg").fadeIn();
			return false;
		}else{
			// console.log('date ok');
			return true;
		}
    }

    function showResponse() {
        $.ajax({
            url: '/udata/custom/check_captcha.json',
            method: 'POST',
            dataType: 'json',
            success: function(data) {
                if(data.result == 'error'){
                    $(currentForm).find(".captcha-msg").fadeIn();
                }

                if(data.result == 'success'){
                     $(currentForm).find(".success-msg").fadeIn();

                    setTimeout(function(){
                        $("input:reset").click();
                        $(currentForm).find(".success-msg").fadeOut();
                    }, 4000);

                }
            }
        });
    }

    $(".ba-slider").beforeAfter();

});

$('.fancybox').fancybox();