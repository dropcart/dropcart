(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node/CommonJS
        factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {
    'use strict';

    $.fn.dcPriceFormula = function (options) {

        var defaults = {
            template : $('#dcPriceFormulaTemplate').html(),
            container: $('#dcFormula'),
            addButton: $('#dcAddButton'),
            removeButton: $('#dcRemoveButton'),
            previewContainer: $('#dcPreviewContainer'),
            operators: [],
            values: [],
            priceLabel: 'PRIJS'
        };

        options = $.extend({}, defaults, options);

        var PriceFormula = {};

        PriceFormula.addModifier = function() {
            options.operators.push('+');
            options.values.push(0);
            PriceFormula.render();
        };

        PriceFormula.removeModifier = function() {
            options.operators.pop();
            options.values.pop();
            PriceFormula.render();
            PriceFormula.preview();
        };

        PriceFormula.preview = function() {
            var formula = '';

            for(var i=0; i < options.operators.length; i++) {
                if(options.values[i] !== 0)
                    formula += '( ';
            }

            formula += options.priceLabel;

            for(var j=0; j < options.operators.length; j++) {
                if(options.values[j] != 0) {

                    if(isNumeric(options.values[j])) {
                        formula += ' ' + options.operators[j] + ' ' + options.values[j] + ' )';
                    } else {
                        formula = 'Formula contains non-numeric values';
                        break;
                    }

                }
            }

            options.previewContainer.text(formula);
        };

        PriceFormula.updateValueFromIndex = function(index) {
            options.values[index] = options.container.find('.value').eq(index).val().replace(/,/g, '.');
            PriceFormula.preview();
        };

        PriceFormula.updateOperatorFromIndex = function(index) {
            options.operators[index] = options.container.find('.operator').eq(index).val().replace(/,/g, '.');
            PriceFormula.preview();
        };


        PriceFormula.render = function() {
            options.container.empty();

            if(options.operators.length !== options.values.length) {
                throw new Error('Operators and values do not have the same length!');
            }

            for(var i=0; i < options.operators.length; i++) {
                var template = $(options.template);
                template.appendTo(options.container);
                template.find('.operator').on('change', PriceFormula.updateOperatorFromIndex.bind(this, i));
                template.find('.operator').val(options.operators[i] || 0);
                template.find('.value').on('change', PriceFormula.updateValueFromIndex.bind(this, i)).val(options.values[i]);
            }
        };

        var isNumeric = function(num) {
            return !isNaN(num)
        };

        var handleRemoveModifier = function(event) {
            event.preventDefault();
            PriceFormula.removeModifier();
        };

        var handleAddModifier = function(event) {
            event.preventDefault();
            PriceFormula.addModifier();
        };

        var bindEvents = function() {
            options.addButton.on('click', handleAddModifier);
            options.removeButton.on('click', handleRemoveModifier);
        }

        return this.each(function() {
            bindEvents();

            PriceFormula.render();
            PriceFormula.preview();
        });

    };
}));