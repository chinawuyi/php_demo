/**
 * Created by apple on 14-6-25.
 */
$.createController('Pub');
$.extend(Pub, {
    thislight: null,
    __constructor: function () {
        //   this.thislight = new lightBox();
    },
    'checkForm': function (id) {
        if (!id) {
            return $('form').valid();
        }
        else {
            return $('#' + id).valid();
        }

    },
    'C_defaultWinOpen': function () {
        $('.jsopen').each(function () {
            $(this).click(function () {
                var type = $(this).attr('opentype') ? $(this).attr('opentype') : $(this).attr('type');
                if (type == 'iframe') {
                    window.top.window.Desk.openIframe(this);
                }
            });
        });
    },
    //默认验证类
    'C_defaultCheck': function () {
        if (!$.fn.validate) {
            return;
        }
        $('form').validate({
            onfocusout: function (element) {
                $(element).valid();
            }
        });
        /*   return;
         $('form').blur(function () {
         alert(4);
         });
         $('.jsnotempty').each(function () {
         $(this).blur(function () {
         var _this = this;
         window.setTimeout(function () {
         if ($(_this).val() == '') {
         $(_this).popover({
         'content': function () {
         return $(_this).attr('data-pre') + ' : 不可为空';
         }
         });
         $(_this).addClass('pub_error');
         $(_this).popover('show');

         }
         else {
         $(_this).popover('destroy');
         $(_this).removeClass('pub_error');
         }
         }, 400);


         });
         }); */

    },
    'C_defaultLightOpen': function () {

        /*    var _this = this;
         $('.lightopen').each(function(){
         var openfresh = $(this).attr('openfresh'),
         defaultsize = $(this).attr('defaultsize'),
         openwidth = $(this).attr('openwidth'),
         openheight = $(this).attr('openheight');
         _this.thislight.winLight(this,{'width':'1024','height':'600',mask:true});
         }); */
    }
});
$.doController('Pub', true);