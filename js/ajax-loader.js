var ifsg_ajax_scripts_processing;

jQuery(document).ready(function($) {

      $(window).scroll(function() {

        if (ifsg_ajax_scripts_processing)
          return false;

          if( ($(window).scrollTop() >= ($(document).height() - $(window).height())*0.85) && ifsg_ajax_scripts.total_posts > $('article').size() ) {
                ifsg_ajax_scripts_processing = true;

                var last_id = $('article').attr("id");
                var last_id_small = $('article').attr("id");
                // loadMoreData(last_id);
                if ( $(window).width() > 768) {
                  // processing = true;
                  // javascript for large screens here
                  loadMoreData(last_id);
                  // loadMoreDataSmall(last_id_small);
                }
                else {
                  // processing = true;
                  // javascript for small screens here
                  loadMoreDataSmall(last_id_small);
                }
              }
      });

    function loadMoreData(last_id){ // Larger Screens
      $.ajax(
            {
                url: ifsg_ajax_scripts.ajax_url,
                type : 'post',
                data : {
                  action : 'post_template_load',
                  security: $( '#inf_scroll_gp_ajax_nonce' ).val(),
                  post_id : last_id,
                  offset : $('article').size(),
                },
                beforeSend: function(response)
                {
                    $('.site-main').append('<div id="matts_inf_scroll_loading"></div>');
                },
                success: function( response ) {
                		$('#matts_inf_scroll_loading').remove();
                    if(response) { // check for non-empty response
                      $('#main').append(response);
                      ifsg_ajax_scripts_processing = false;
                    }
                    else {
                      ifsg_ajax_scripts_processing = true;
                    }
              	},
              });
          return false;
    }

    function loadMoreDataSmall(last_id_small){ // Smaller Screens
      $.ajax(
            {
                url: ifsg_ajax_scripts.ajax_url,
                type : 'post',
                data : {
                  action : 'post_template_load_small',
                  security: $( '#inf_scroll_gp_ajax_nonce' ).val(),
                  post_id : last_id_small,
                  offset : $('article').size(),
                },
                beforeSend: function(response)
                {
                    $('.inside-right-sidebar').append('<div id="matts_inf_scroll_loading"></div>');
                },
                success: function( response ) {
                    $('#matts_inf_scroll_loading').remove();
                    if(response) { // check for non-empty response
                      $('#content').children('div:last').append(response);
                      ifsg_ajax_scripts_processing = false;
                    }
                    else {
                      ifsg_ajax_scripts_processing = true;
                    }
                }
            });
        return false;
    }
});
