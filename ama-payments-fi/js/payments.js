$j = jQuery.noConflict();

var total = 0;

$j(function() {

    window.modal_loader_widget = new ModalLoaderWidget();

    $j('.changeTime').on('click', function(e) {
      //change prices and stuff
      var type = $j('input', $j(this)).attr('data-type');
      packageID = $j(this).closest('.checkout-section').attr('id');
      changePrices(type, packageID);
      calculatePriceExtraUser($j('#formType','#form'+packageID).val(), packages, packageID, $j("#extraUsers" + packageID).val());

      $j('#packages_view form').each(function(){
          var old_action = $j(this).attr('data-cleanurl');
          $j(this).attr('action', old_action + type);
      });
    });

    $j(document).on('keyup change', '.number-select input', function(){
         var packageID = $j(this).attr('data-row');
            $j("#extraUsers" + packageID).val($j(this).val());
            calculatePriceExtraUser($j('#formType','#form'+packageID).val(), packages, packageID, $j(this).val());
    });

    $j('.btn-minus').click(function(e){
      e.preventDefault();
      var input = $j(this).next();
      var number = parseInt(input.val());
      var min = input.attr('min');
      if (number > min && number != null){
        input.val( number - 1 );
        input.change();
      }
    });

    $j('.btn-plus').click(function(e){
      e.preventDefault();
      var input = $j(this).prev();
      var number = parseInt(input.val());
      //var max = input.attr('max');
      //if (number < max && number != null){
      if (number != null){
        input.val( number +1 );
        input.change();
      }
    });

    $j(document).on('click', '.checkout-section .btn-submit', function(event){
        event.preventDefault();
            if ( typeof translatePleaseWait == 'undefined' ) translatePleaseWait = '';

            //$j('#packages_view').slideUp();
            //$j('#order_view').slideDown();
            //$j('#order_view').html('<img src="'+pluginUrl+'/images/loading.gif" alt=""/> ' + translatePleaseWait);
            // $j('#formTotal' + $j(this).attr('data-row')).val($j('#mainPrice' + $j(this).attr('data-row')).text());

            $formType = $j('#formType','#form'+$j(this).attr('data-row')).val();

            if( $formType == 1 )    {
                //var thisisprice = parseFloat(($j('#mainPrice' + $j(this).attr('data-row')).find('.realprice').text())*12);
                $j('#formTotal' + $j(this).attr('data-row')).val(($j('#mainPrice' + $j(this).attr('data-row')).find('.realprice').text())*12);
            } else    {
                //var thisisprice = parseFloat($j('#mainPrice' + $j(this).attr('data-row')).find('.realprice').text());
                $j('#formTotal' + $j(this).attr('data-row')).val($j('#mainPrice' + $j(this).attr('data-row')).find('.realprice').text());
            }

            if ( $j('#formType').val() == 2 ) {
                $j('#packagePriceText' + $j(this).attr('data-row')).val(packages[$j(this).attr('data-row')].monthPriceText);
            }
            else {
                $j('#packagePriceText' + $j(this).attr('data-row')).val(packages[$j(this).attr('data-row')].yearPriceText);
            }

            postOrder($j('#form' + $j(this).attr('data-row')));
            return false;
    });

    $j(document).on('click', '.goBackToPackages', function(){
        $j('#order_view').slideUp();
        $j('#packages_view').slideDown();
    });

    $j(document).on('click', '.goBackToOrder', function(){
        $j('#summary_view').slideUp();
        $j('#order_view').slideDown();
    });

    $j(document).on('click', '.showInfo', function(){
        $j.colorbox({
            html: $j("#terms112").html()
        });
    });

    $j(document).on('click', '.go-back', function(){
        window.history.back();
        return false;
    });

});

function changePrices(type, packageID)
{
        if ( type == 'year') changeToAnnually(packageID);
        if ( type == 'month') changeToMonthly(packageID);

}


function changeToAnnually(packageID)
{
   var thePackage = $j('#'+packageID);
    if ( packages[packageID].yearPrice != 0 ) {
        if(packages[packageID].couponValue != 0){
            var diff = (packages[packageID].couponValue/100)*packages[packageID].yearPrice;
            var soodustus = packages[packageID].yearPrice-diff;
            soodustus = soodustus % 1 === 0 ? soodustus.toString() : parseFloat(soodustus).toFixed(2);
            $j('.main_price', thePackage).html('<span class="realprice oldprice">'+packages[packageID].yearPrice+'</span> <span class="orig-euro">€</span> '+soodustus);
        } else {
            $j('.main_price', thePackage).html('<span class="realprice">'+packages[packageID].yearPrice+'</span>');
        }

        $j('.main_price_text', thePackage).text(packages[packageID].yearText);
        if ( packages[packageID].yearExtraUserPrice == 0 ) {
            $j('.extraUsers', thePackage).hide();
        }
        else {
            $j('.extraUsers', thePackage).show();
        }
    }
    $j('.year-price-text', thePackage).show();
    $j('.month-price-text', thePackage).hide();
    $j('#formType', '#form'+packageID).val(1);
}

function changeToMonthly(packageID)
{
   var thePackage = $j('#'+packageID);
    if ( packages[packageID].monthPrice != 0 ) {
        if( packages[packageID].couponValue != 0){
            var diff = (packages[packageID].couponValue/100)*packages[packageID].monthPrice;
            var soodustus = packages[packageID].monthPrice-diff;
            soodustus = soodustus % 1 === 0 ? soodustus.toString() : parseFloat(soodustus).toFixed(2);
            $j('.main_price', thePackage).html('<span class="realprice oldprice">'+packages[packageID].monthPrice+'</span> <span class="orig-euro">€</span> '+soodustus);
        } else {
            $j('.main_price', thePackage).html('<span class="realprice">'+packages[packageID].monthPrice+'</span>');
        }
       $j('.main_price_text', thePackage).text(packages[packageID].monthText);
           if ( packages[packageID].monthExtraUserPrice == 0 ) {
               $j('.extraUsers', thePackage).hide();
           }
           else {
               $j('.extraUsers', thePackage).show();
           }
    }
    $j('.year-price-text', thePackage).hide();
    $j('.month-price-text', thePackage).show();
    $j('#formType', '#form'+packageID).val(2);
}

function calculatePriceExtraUser(type, packages, row, userCount)
{
   //console.log(type +';'+ packages +';'+ row  +';'+ userCount);
        if ( typeof userCount == 'undefined' )  userCount = 1;
        if ( type == 1) changeAnnuallyTotal(packages[row], userCount, row);
        if ( type == 2) changeToMonthlyTotal(packages[row], userCount, row);
}

function changeAnnuallyTotal(data, userCount, row)
{
    if(data.couponValue != 0){
        var diff = (data.couponValue/100)*data.yearPrice;
        var soodustus = data.yearPrice-diff;
        total = soodustus;
        totalprev = data.yearPrice;
    } else {
        total = data.yearPrice;
        totalprev = data.yearPrice;
    }

    if ( userCount > 1 ) {
        userCount  = userCount - 1;
        total += (data.yearExtraUserPrice * userCount);
        totalprev += (data.yearExtraUserPrice * userCount); // w/o discount
    }

    total = total % 1 == 0 ? total.toString() : parseFloat(total).toFixed(2);

    if(data.couponValue != 0){
        var prev = totalprev % 1 == 0 ? totalprev.toString() : parseFloat(totalprev).toFixed(2);
        $j('#mainPrice' + row).html('<span class="realprice oldprice">'+prev+'</span> <span class="orig-euro">€</span> '+total);
    } else {
        $j('#mainPrice' + row).html('<span class="realprice">'+total+'</span>');
    }

    $j('#formTotal' + row).val(total);
}

function changeToMonthlyTotal(data, userCount, row)
{
    if(data.couponValue != 0){
        var diff = (data.couponValue/100)*data.monthPrice;
        var soodustus = data.monthPrice-diff;
        total = soodustus;
        totalprev = data.monthPrice;
    } else {
        total = data.monthPrice;
        totalprev = data.monthPrice;
    }

    if ( userCount > 1 ) {
        userCount  = userCount - 1;
        total += (data.monthExtraUserPrice * userCount);
        totalprev += (data.monthExtraUserPrice * userCount); // w/o discount
    }

    total = total % 1 == 0 ? total.toString() : parseFloat(total).toFixed(2);

    if(data.couponValue != 0){
        var prev = totalprev % 1 == 0 ? totalprev.toString() : parseFloat(totalprev).toFixed(2);
        $j('#mainPrice' + row).html('<span class="realprice oldprice">'+prev+'</span> <span class="orig-euro">€</span> '+total);
    } else {
        $j('#mainPrice' + row).html('<span class="realprice">'+total+'</span>');
    }


    $j('#formTotal' + row).val(total);

}

function postOrder(form)
{
    var params = form.serialize();
    params += '&ajaxRequest=true';
    $j.ajax({
        type: 'POST',
        data: params,
        url: pluginUrl + "/ajax.php",
        dataType: 'json'
    }).done(function(resp) {
        if ( resp.status == 0 ) {
            return;
        }

        $j('#order_view').html(resp.html);

        $j('.order-form-loader').hide();
        if(document.getElementById("coupongValue").value && document.getElementById("coupongDefault").value == false) {
        } else {
            $j('#order_view').velocity(
                "scroll", {
                duration: 1000
            });
        }
        validateOrderForm(translator);
    });
}

//validate and post order form
function validateOrderForm(translator)
{

    if ( typeof translator == 'undefined' ) {
        var translator = {};
    }

    $j.validator.messages.required = translator.thisFieldIsRequired;
    $j('.order-form').validate({
      errorClass: "error-text",
      onkeyup: false,
      errorPlacement: function(error, element) {
        $j(element).parent().after(error);
      },
      highlight: function(element, errorClass) {
        $j(element.parentNode).addClass('has-error');
      },
      unhighlight: function(element, errorClass) {
        $j(element.parentNode).removeClass('has-error');
      },
        rules: {
            'order[email]': {
                required: true,
                email: true,
            },
            'order[email]': {
                required: true,
                email: true
            }
        },
        messages: {
            'order[email]': translator.wrongEmailAddress,
            'order[loginEmail]': translator.wrongEmailAddress,
        },
        /*errorPlacement: function(){
                //return true;
        },*/
        submitHandler : function (form) {
            /*
            $j('#order_view').slideUp();
            $j('#summary_view').slideDown();
            $j('#summary_view').html('<img src="'+pluginUrl+'/images/loading.gif" alt=""/> ' + translatePleaseWait);

            $j.ajax({
                type: 'POST',
                data: $j('.order-form').serialize(),
                url: pluginUrl + "/ajax.php",
                dataType: 'json'
            }).done(function(resp) {
                if ( resp.status == 0 ) {
                    return;
                }
                $j('#summary_view').html(resp.html);
            });


            return false;
            */

            $j('.order-form').sumbit();
        }
    });
}

function calcALV(price)
{
    var alv = 1.24;
    var newPrice = price*alv;
    return newPrice.toFixed(2);
}

function setFormPayment(payment)
{
    window.modal_loader_widget.show('Ladataan...');

    var old_action = $j('#paymentForm').attr('data-cleanurl');
    $j('#paymentForm').attr('action', old_action + payment);
}

function validateCoupong() {
    var code = document.getElementById("coupongCode").value;
    var packageId = document.getElementById("activePackageId").value;
    var formType = document.getElementById("activeFormType").value;
    var params = 'action=validateCoupong&code='+code+'&packageId='+packageId+'&formType='+formType;
    params += '&ajaxRequest=true';

    var type = jQuery('input[name="changeTimeRadio"]:checked').data('type');

    jQuery.ajax({
        url: pluginUrl + "/ajax.php",
        type: "post",
        dataType: 'json',
        data: params,
        success: function(data) {
            if(data.error) {
                document.getElementById("error").innerHTML = data.error;
            } else {
                document.getElementById("error").innerHTML = '';
                jQuery(".btn.btn-submit.active").attr("data-target", "");
                jQuery(".btn.btn-submit.active").click();
                jQuery(".btn.btn-submit.active").attr("data-target", "#checkoutForm");
                changePrices(type, packageId);
            }
        }
    });
}

function showValidateButton() {
    jQuery("#validateCoupong").removeClass("hidden");
}

function saveToSession(elem) {
    var id = elem.id;
    var value = elem.value;
    var name = elem.getAttribute('name');
    var name = name.split('[').pop().split(']').shift();
    var params = 'action=saveToSession&name='+name+'&value='+value;
    params += '&ajaxRequest=true';

    if(name == 'email') {
        if(document.getElementById("loginEmail").value) {}else{
            document.getElementById("loginEmail").value = value;
            saveToSession(document.getElementById("loginEmail"));
        }
    }

    $j.ajax({
        type: 'POST',
        data: params,
        url: pluginUrl + "/ajax.php",
        dataType: 'json',
        success: function(data) {
            console.log(data);
        }
    });
}
