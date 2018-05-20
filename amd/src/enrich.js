// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Enrich form manipulation
 *
 * @module     search_elastic/elastic
 * @package    search_elastic
 * @class      Enrich
 * @copyright  2018 Matt Porritt <mattp@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.4
 */
define(['jquery', 'core/fragment', 'core/templates'], function($, Fragment, templates) {

    /**
     * Module level variables.
     */
    var Enrich = {};
    var contextid;

    function updateSelects() {
        var ischecked = $("[name=fileindexing]").is(':checked');
        window.console.log(ischecked);
        if (ischecked === true) {
            $('[name=imageindexselect]').prop('disabled', false);
            $('[name=fileindexselect]').prop('disabled', false);
        } else {
            $('[name=imageindexselect]').prop('disabled', true).val("0");
            $('[name=fileindexselect]').prop('disabled', true).val("0");
            updateForm();
        }
    }

    function updateForm() {
        var formData = $('.form_container form').serialize();
        var params = {jsonformdata: JSON.stringify(formData)};
        Fragment.loadFragment('search_elastic', 'new_enrich_form', contextid, params).done(
                function(foo){
                    $('.form_container').html(foo);
                    $('[name=imageindexselect]').change(updateForm);
                    $('[name=fileindexselect]').change(updateForm);
                    $('[name=fileindexing]').change(updateSelects);
                    //updateSelects();
                    }
                );
    }

    Enrich.init = function(context) {
     // Save the context ID in a closure variable.
        contextid = context;
        $('[name=imageindexselect]').change(updateForm);
        $('[name=fileindexselect]').change(updateForm);
        $('[name=fileindexing]').change(updateSelects);
        updateSelects();
    };

    return Enrich;
});
