(function ($) {
  $.entwine('ss', function ($) {

    $('.calendar-field').entwine({

      statusMessage: function(text, type) {
        text = jQuery('<div/>').text(text).html(); // Escape HTML entities in text
        jQuery.noticeAdd({text: text, type: type, stayTime: 5000, inEffect: {left: '0', opacity: 'show'}});
      },

      onmatch: function () {

        //Vars
        var _this = this;

        //Locale
        var lang = $('html').attr('lang');
        if(lang){
          lang = lang.split('-')[0];
        }else{
          lang = 'en';
        }

        //Calendar
        this.fullCalendar({
          events: this.data('dataurl'),
          locale: lang,
          header: {
            left: 'title',
            center: '',
            right: 'today prev,next month'
          },

          dayClick: function (date, jsEvent, view) {
            window.location = _this.data('addnewurl') + '?' + _this.data('startdate-field') + '=' + date.format();
          },

          eventClick: function(calEvent, jsEvent, view) {
            window.location = calEvent.editlink;
          },

          eventDrop: function(event, delta, revertFunc) {
            $.post(_this.data('saveurl'), {
              'newstart': event.start.format(),
              'id': event.id
            }, function(result){
              if(!result.success){
                _this.statusMessage(result.message, 'bad');
                revertFunc();
              }else{
                _this.statusMessage(result.message, 'good');
              }
            });
          }
        });

        //Re render fix
        $('.ss-tabset').on('tabsactivate', function(){
          if(_this.is(':visible')){
            _this.fullCalendar('render');
          }
        });

      }

    });

  });
})(jQuery);
