;
//localized var aioseopext_link_counter_vars

var AIOSEOPEXT_LINK_COUNTER = {};

(function($) {

	AIOSEOPEXT_LINK_COUNTER = {

		process_paused : 0,

		init : function() {

			$("#aioseopext_link_counter_start_process").click( function( e ) {
				e.preventDefault();
				$("#lc_process_box_1").addClass('hidden');
				$("#lc_process_box_2").removeClass('hidden');
				AIOSEOPEXT_LINK_COUNTER.startProcessing();
			});

			$("#aioseopext_link_counter_stop_process").click( function( e ) {
				e.preventDefault();
				$("#lc_process_box_2").addClass('hidden');
				$("#lc_process_box_1").removeClass('hidden');
				AIOSEOPEXT_LINK_COUNTER.stopProcessing();
			});
			
		},

		setProgressBar : function( val ){
			val = parseFloat( val );
			if( val < 0 || val > 100 ){
				return;
			}
			
			$("#aioseopext_lc_progressbar").css("width", ""+val+"%");

		},

		startProcessing : function() {
			this.process_paused = 0;
			var qData = {
				'action' : aioseopext_link_counter_vars.ajax_action_name,
				'_wpnonce' : aioseopext_link_counter_vars.nonce
			};
			$.ajax({
			   url: ajaxurl,
			   type: "GET",
			   global: false,
			   cache: false,
			   async: true,
			   data:qData,
				success: function(response){
					
					if(response.status == 'success')
					{
						if( response.completed == 1 )
						{
							//show completed message.
							 $("#lc_process_box_2").html( response.progress_msg );

						}
						else
						{
							 AIOSEOPEXT_LINK_COUNTER.setProgressBar( response.progress_percentage );
							 $("#aioseopext_lc_progress_status").html( response.progress_msg );
							 if( AIOSEOPEXT_LINK_COUNTER.process_paused == 0 )
							 {
							 	AIOSEOPEXT_LINK_COUNTER.startProcessing();
							 }
							 
						}
										
					}
					else
					{
						alert(response.message);
					}
				},
				error: function(xhr,errorThrown){}
				   
			  });//end $.ajax
		},

		stopProcessing : function() {
			this.process_paused = 1;
		},

	};

	$(document).ready(function() {
		AIOSEOPEXT_LINK_COUNTER.init();
	});

})(jQuery);