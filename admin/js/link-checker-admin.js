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
            $.ajax(`/wp-content/plugins/team51-link-checker/link-checker-last-result.json?v=${Date.now()}`).done(( data ) => {
                console.log(data);

                if ( ! vm ) {
                    var vm = new Vue({
                        el: '.link-checker__vue_app',
                        data: data
                    });
                }

                vm.date = data.date;
                vm.results = data.results;
            })
        }

        // Events. TODO: Move this to VueJS event
        $(document).on('click', '.link-checker__btn-start', function() {
            $('.link-checker__btn-start').attr('disabled', 1);
            $.ajax('/wp-json/linkchecker/v1/check').done(() => {
                $('.link-checker__btn-start').removeAttr('disabled');
                // Give a few seconds while the new JSON file is created
                setTimeout( () => {
                    pullLastData();
                }, 1000)
            });
        });
    });

})( jQuery );
