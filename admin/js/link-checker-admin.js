(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    $(function() {
        init();
        function init() {
            pullLastData();
        }

        function pullLastData() {
            $.ajax('/wp-content/plugins/team51-link-checker/link-checker-last-result.json').done(( data ) => {
                console.log(data);

                new Vue({
                    el: '.link-checker__vue_app',
                    data: data
                });
            })
        }

        // Events
        $('#linkCheckerStartBtn').on('click', function() {
            $('#linkCheckerStartBtn').attr('disabled', 1);
            $.ajax('/wp-json/linkchecker/v1/check').done(() => {
                $('#linkCheckerStartBtn').removeAttr('disabled');
                pullLastData();
            });
        });
    });

})( jQuery );
