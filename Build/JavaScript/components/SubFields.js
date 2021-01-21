module.exports = function() {
    $(document).ready(function() {
        let subFields = function(options, element) {
            let app = {
                element: null,
                options: {},
                /**
                 * Initialize the plugin
                 */
                init: function() {

                }
            };
            app.options = $.extend(app.options, options);
            app.element = element;
            app.init();

            return app;
        };

        $.fn.subFields = function(options) {
            return $(this).each(function() {
                let $this = $(this);
                if(!$this.data('subFields')) {
                    $this.data('subFields', new subFields(options, $this));
                }
            })
        };

        $('.subFields').subFields();
    })
}