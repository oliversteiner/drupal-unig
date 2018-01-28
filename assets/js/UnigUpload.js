/**
 * Created by ost on 14.05.17.
 */


(function ($, Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.unigUpload = {
        attach: function (context, settings) {
             console.log('Drupal.behaviors.unig.upload');

            // onload
            constructor(context, settings);

            // Event Handlers
            // Change
            $('#edit-project').change(function (context, settings) {
                selectChange(context, settings);
            });

        }
    };

    /**
     *
     * @param context
     * @param settings
     */
    function constructor(context, settings) {
        selectChange(context, settings);
    }

    /**
     *
     * @param context
     * @param settings
     */
    function selectChange(context, settings) {

        // Get the selected Option
        var e = document.getElementById("edit-project");
        var select = e.options[e.selectedIndex].value;

        if (select === 'neu') {
            $('#unig_form_upload_new_project').show();
        }
        else {
            $('#unig_form_upload_new_project').hide();
        }
    }


})(jQuery, Drupal, drupalSettings);

