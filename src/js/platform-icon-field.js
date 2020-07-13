jQuery(document).ready(function ($) {
    let files_modal,
        $field = $('#platform-icon-wrap'),
        $input = $field.find('#platform-icon'),
        $review_container = $field.find('.image'),
        $review_img = $review_container.find('img'),
        $add_btn = $field.find('#add-img'),
        $remove_btn = $field.find('#remove-img');

    $(function() {
        if ($input.val()) {
            $add_btn.text(mf_text.btn_edit);
            $review_container.show();
            $review_img.attr('src', $input.val());
            $remove_btn.show();
        }
    });

    $('#submit').click(function() {
        setTimeout(function() {
            $remove_btn.click();
        }, 500);
    });

    $add_btn.click(function(event) {
        event.preventDefault();

        if (files_modal) {
            files_modal.open();
            return;
        }

        let AttachmentLibrary = wp.media.view.Attachment.Library;

        wp.media.view.Attachment.Library = AttachmentLibrary.extend({
            render: function() {
                if (this.model.attributes.height > 50 && this.model.attributes.width > 50) {
                    this.$el.addClass('acf-disabled');
                }

                return AttachmentLibrary.prototype.render.apply(this, arguments);
            },
            toggleSelection: function () {
                let selection = this.options.selection,
                    model = this.model,
                    $sidebar = this.controller.$el.find('.media-frame-content .media-sidebar');

                $sidebar.children('.acf-selection-error').remove();
                
                if (this.$el.hasClass('acf-disabled')) {
                    let filename = model.attributes.filename;

                    $sidebar.prepend([
                        '<div class="acf-selection-error">',
                        '<span class="selection-error-label">' + mf_text.error_disabled_title + '</span>',
                        '<span class="selection-error-filename">' + filename + '</span>',
                        '<span class="selection-error-message">' + mf_text.error_disabled_text + '</span>',
                        '</div>'
                    ].join(''));

                    selection.reset();
                    selection.single(model);

                    return;
                }
				
                AttachmentLibrary.prototype.toggleSelection.apply(this, arguments);
            }
        });

        files_modal = wp.media({
            title: mf_text.title_popup,
            library: {
                type: 'image'
            },
            button: {
                text: mf_text.btn_select_popup
            },
            multiple: false
        });
        
        files_modal.on('open', function() {
            if ($input.val()) {
                files_modal.state().get('selection').add(wp.media.attachment($input.val()));
            }
        }).on('select', function () {
            let attachment = files_modal.state().get('selection').first().toJSON();

            $input.val(attachment.url).change();
            $add_btn.text(mf_text.btn_edit);
            $review_container.show();
            $review_img.attr('src', attachment.url);
        }).open();

        $remove_btn.show();
    });

    $remove_btn.click(function(event) {
        event.preventDefault();

        $input.val('').change();
        $add_btn.text(mf_text.btn_choose);
        $review_container.hide();
        $review_img.attr('src', '#');
        $(this).hide();
    });
});