jQuery( document ).ready(function() {
    var heightToAdd = jQuery(".pbuilder_row_stick_top").height();
    heightToAdd = heightToAdd-28;
    jQuery('.stick-top-div').css('height', heightToAdd+'px');
});
(function ($) {
    /*
     Validation Singleton
     */
    var Validation = function () {

        var rules = {
            email: {
                check: function (value, default_value) {
					             if (value !== default_value)
                        return testPattern(value, "[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])");
                    return true;
                },
                msg: "Enter a valid e-mail address."
            },
            url: {
                check: function (value, default_value) {

                    if (value !== default_value)
                        return testPattern(value, "^https?://(.+\.)+.{2,4}(/.*)?$");
                    return true;
                },
                msg: "Enter a valid URL."
            },
            checkbox: {
                check: function (value, default_value, checked) {
                    if (checked == 'checked')
                    return true;
                },
                msg: "You need to check the box."
            },
            required: {
                check: function (value, default_value) {
                    if (value !== default_value)
                        return true;
                    else
                        return false;
                },
                msg: "This field is required."
            }
        }
        var testPattern = function (value, pattern) {

            var regExp = new RegExp(pattern, "");
            return regExp.test(value);
        }
        return {
            addRule: function (name, rule) {

                rules[name] = rule;
            },
            getRule: function (name) {

                return rules[name];
            }
        }
    }

    /*
     Form factory
     */
    var Form = function (form) {

        var fields = [];

        form.find("[validation]").each(function () {
            var field = $(this);

            if (field.attr('validation') !== undefined) {
                fields.push(new Field(field));
            }
        });
        this.fields = fields;
    }
    Form.prototype = {
        validate: function () {

            for (field in this.fields) {
                if(field != "remove"){
                this.fields[field].validate();
            }
            }
        },
        isValid: function () {

            for (field in this.fields) {

                if(field != "remove"){
                if (!this.fields[field].valid) {
                    this.fields[field].field.focus();
                    return false;
                }
				}
            }
            return true;
        }
    }

    /*
     Field factory
     */
    var Field = function (field) {

        this.field = field;
        this.valid = false;
        this.attach("change");
    }
    Field.prototype = {
        attach: function (event) {

            var obj = this;
            if (event == "change") {
                obj.field.bind("change", function () {
                    return obj.validate();
                });
            }
            if (event == "keyup") {
                obj.field.bind("keyup", function (e) {
                    return obj.validate();
                });
            }
        },
        validate: function () {

            var obj = this,
                    field = obj.field,
                    errorClass = "errorlist",
                    errorlist = $(document.createElement("ul")).addClass(errorClass),
                    types = field.attr("validation").split(" "),
                    container = field.parent(),
                    errors = [];

            field.next(".errorlist").remove();

            for (var type in types) {
                if (type != "remove") {
                    console.log('Validate '+field.attr('name'));
                    var rule = $.Validation.getRule(types[type]);
                    if (!rule.check(field.val(), field.attr('default-value'), field.attr('checked'))) {
                        container.addClass("error");

                        errors.push(field.attr('error-message'));
                        container.css("margin", '');
                        field.css("border-color", 'red !important;');

                    }
                }
            }
            if (errors.length) {
                obj.field.unbind("keyup")
                obj.attach("keyup");
                field.after(errorlist.empty());

                for (error in errors) {
                    if(error != "remove"){
                    errorlist.append("<li>" + errors[error].toString() + "</li>");
                    }
                }
                obj.valid = false;
            }
            else {
                errorlist.remove();
                container.removeClass("error");
                obj.valid = true;
            }
        }
    }

    /*
     Validation extends jQuery prototype
     */
    $.extend($.fn, {
        validation: function () {

            var validator = new Form($(this));
            $.data($(this)[0], 'validator', validator);

            $(this).bind("submit", function (e) {
                validator.validate();
                if (!validator.isValid()) {
                    e.preventDefault();
                }
            });
        },
        validate: function (form) {

            var validator = new Form(form);
            $.data(form, 'validator', validator);
            validator.validate();
            return validator.isValid();

        }
    });
    $.Validation = new Validation();
})(jQuery);
