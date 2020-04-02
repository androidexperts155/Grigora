$(document).ready(function () {
    function render_uploaded_image(input, element_selector) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            $('.uploaded-file-name').html(file.name);

            var reader = new FileReader();

            reader.onload = function (e) {
                $(element_selector).attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
            $(element_selector).show();
        }
    }

    $("#image").change(function () {
        render_uploaded_image(this, '.render-image');
    });

    $("#logo").change(function () {
        render_uploaded_image(this, '.render-logo');
        $(".existing-logo-wrapper").css('display', 'none');
    });

    $('.delete-item').bootstrap_confirm_delete({
        heading: 'Delete',
        message: 'Are you sure you want to delete this item?',
        data_type: null,
    });

    $(".existing-image-wrapper .existing-image").on({
        mouseenter: function () {
            $(this).find(".delete-existing-image").css("display", "block");
        },
        mouseleave: function () {
            $(this).find(".delete-existing-image").css("display", "none");
        }
    });

    $("body").on("click", ".existing-image .delete-btn", function () {
        $(this).parent(".existing-image").remove();
    });

    $(function () {
        $('.button-checkbox').each(function () {

            // Settings
            var $widget = $(this),
                    $button = $widget.find('button'),
                    $checkbox = $widget.find('input:checkbox'),
                    color = $button.data('color'),
                    settings = {
                        on: {
                            icon: 'glyphicon glyphicon-check'
                        },
                        off: {
                            icon: 'fa fa-square-o'
                        }
                    };

            // Event Handlers
            $button.on('click', function () {
                $checkbox.prop('checked', !$checkbox.is(':checked'));
                $checkbox.triggerHandler('change');
                updateDisplay();
            });
            $checkbox.on('change', function () {
                updateDisplay();
            });

            // Actions
            function updateDisplay() {
                var isChecked = $checkbox.is(':checked');

                // Set the button's state
                $button.data('state', (isChecked) ? "on" : "off");

                // Set the button's icon
                $button.find('.state-icon')
                        .removeClass()
                        .addClass('state-icon ' + settings[$button.data('state')].icon);

                // Update the button's color
                if (isChecked) {
                    $button
                            .removeClass('btn-default')
                            .addClass('btn-' + color + ' active');
                } else {
                    $button
                            .removeClass('btn-' + color + ' active')
                            .addClass('btn-default');
                }
            }

            // Initialization
            function init() {

                updateDisplay();

                // Inject the icon if applicable
                if ($button.find('.state-icon').length == 0) {
                    $button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i> ');
                }
            }
            init();
        });
    });
    
    $("body").on("click", ".establishment-close-check", function () {
        var check_active_class = $(this).hasClass("active");
        
        if(check_active_class == true) {
            $(this).parent(".button-checkbox").parent(".establishment-checkbox").siblings(".hidden-data").find("input").val("closed");
            $(this).parent(".button-checkbox").parent(".establishment-checkbox").siblings(".hidden-data").css("display", "none");
        } else {
            $(this).parent(".button-checkbox").parent(".establishment-checkbox").siblings(".hidden-data").css("display", "block");
        }
    });
    
    $("body").on("click", ".change-status", function () {
        var status = $(this).children(".status-value").val();
        var update_id = $(this).children(".status-value").attr("data-update_id");
        var url = $(this).children(".status-value").attr("data-update_url");
        var status_for = $(this).children(".status-value").attr("data-status_for");
        //alert(is_approved);
        
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'JSON',
            data: {
                status: status, update_id: update_id, _token: token
            },
            success: function (data, textStatus, jqXHR) {
                var msg = '';
                if(status == 1) {
                    msg = status_for + "has been activated successfully";
                } else {
                    msg = status_for + "has been de-activated successfully";
                }
                $.notify({
                    // options
                    message: msg
                },{
                    // settings
                    type: 'info'
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var msg = '';
                if(status == 1) {
                    msg = "Oops! error occured while activating";
                } else {
                    msg = "Oops! error occured while de-activating";
                }
                $.notify({
                    // options
                    message: msg
                },{
                    // settings
                    type: 'danger'
                });
            }
        });
    });
    
    $(".listing-page-description-field").shorten({
        showChars: 250,
        moreText: 'read more',
        lessText: 'read less',
        ellipsesText: '...'
    });
});