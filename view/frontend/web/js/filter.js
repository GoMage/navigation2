define([
    "jquery"
], function ($) {
    "use strict";

    function Filter(element) {

        element = $(element);

        if (element.is("select")) {
            this.value = element.val();
        }else {
            this.value = element.attr('data-value');
        }
        this.param = element.attr('data-param');
        this.ajax = element.attr('data-ajax');
        this.active = element.attr('data-active');
    }

    Filter.prototype = {
        constructor: Filter,

        isActive: function () {
            return this.active;
        },

        isAjax: function () {
            return this.ajax;
        },

        getParam: function () {
            return this.param;
        },

        getValue: function () {
            return this.value;
        }

    };

    return Filter;
});
