$(function () {
    let table           = $('#shop-profile-table'),
        btn_edit        = 'button.btn-shop-edit',
        table_container = '.shop-profile-table-wrapper',
        table_container_w = $('.shop-profile-table-wrapper'),
        body            = $('body')


    on_edit(table, body,btn_edit, table_container, table_container_w)

    on_save(table, body)

    on_cancel(table, body)

})

function on_edit(table, body, btn_edit, table_container, table_container_w) {
    body.on('click', btn_edit, function () {
        let _btn = $(this)

        table.find('.shop-table-values').each(function () {

            if ($(this).attr('data-edit') === 'Y') {
                let attrs = {
                    type : $(this).attr('data-type'),
                    name : $(this).attr('data-name'),
                    value: $(this).attr('data-value')
                }

                if (attrs.type === 'S') {
                    $(this).html(`<input type="text" class="form-control" name="${attrs.name}" value="${attrs.value}" >`)
                }

                if (attrs.type === 'SP') {
                    $(this).html(`<input type="text" class="form-control phone-mask" name="${attrs.name}" value="${attrs.value}" >`)
                }

                if (attrs.type === 'SE') {
                    $(this).html(`<input type="text" class="form-control email-mask" name="${attrs.name}" value="${attrs.value}" >`)
                }

                if (attrs.type === 'A') {
                    let area_value = attrs.value.replace(/[&]nbsp[;]/gi, " ");
                    area_value     = area_value.replace(/[<]br[^>]*[>]/gi, "");

                    $(this).html(`<textarea class="form-control" rows="4" name="${attrs.name}">${area_value}</textarea>`)
                }

                if (attrs.type === 'FI') {
                    let _this = $(this)

                    $(this).append(`<div class="input-group mt-2">
                                        <input type="file" id="file" name="${attrs.name}" class="form-control mw-100">
                                    </div>`)

                    let input = $(this).find('input[type="file"]')

                    view_photo_after_upload(input, _this)

                }

                if (attrs.type === 'LS') {
                    $.ajax({
                        url       : `/${shop_root_dir}/ajax/iblock/delivery.php`,
                        cache: false,
                        beforeSend: () => {
                            ntfStatus('clear')
                            _btn.addClass('loading').addClass('disabled')
                            $(table_container).addClass('loading')
                        },
                        success   : (html) => {
                            let select = $(html)

                            select.attr('name', attrs.name)
                            $(this).html(select)

                            _btn
                                .removeClass(['btn-primary', 'btn-shop-edit'])
                                .addClass(['btn-success', 'btn-shop-save'])
                                .text('Сохранить изменения')
                                .attr('type', 'submit')
                                .parents('.table').addClass('btn-sticky')

                            $('.btn-shop-edit-cancel').addClass('visible')

                            table_container_w.wrap('<form id="shopFormEdit" enctype="multipart/form-data" />')

                            if ($('.phone-mask').length >= 1) {
                                $(".phone-mask").inputmask({ mask: "+7 (999) 999-99-99" });
                            }

                            table.find("input:text:visible:first").focus()

                            ntfStatus('info', 'Режим редактирования включен')

                        },
                        error     : (e) => {
                            server_error_response(e)
                        },
                        complete  : () => {
                            scroll_top_animate()
                            $('.btn-shop-save').removeClass('loading').removeClass('disabled')
                            $(table_container).removeClass('loading')
                        }
                    })
                }

            }

        })

    })
}

function on_save(table, body) {
    let btn_save = '.btn-shop-save'

    body.on('click', btn_save, function (e) {

        e.preventDefault()

        let form     = $('#shopFormEdit'),
            formData = new FormData(shopFormEdit);

        formData.append('file', form.find('input[type="file"]').prop('files'));

        $.ajax({
            url        : `/${shop_root_dir}/ajax/shop_update.php`,
            type       : 'POST',
            data       : formData,
            processData: false,
            contentType: false,
            cache      : false,
            beforeSend : () => {
                ntfStatus('clear')
                $(this).addClass('loading').addClass('disabled')
                area_loading(true)
            },
            success    : (res) => {
                let interval = null

                if (res.STATUS === 'ok') {
                    $('[data-tab="btn-profile"]').triggerHandler('click')

                    interval = setInterval(() => {
                        if (!$('.preloader').is(':visible')) {
                            area_loading(false)
                            $(this).removeClass('loading').removeClass('disabled')
                            ntfStatus('ok', res.STATUS_TEXT)
                            clearInterval(interval)
                        }
                    }, 100)
                }

                if (res.STATUS === 'fail') {
                    area_loading(false)
                    $(this).removeClass('loading').removeClass('disabled')
                    ntfStatus('fail', res.STATUS_TEXT)
                }
            },
            error      : (e) => {
                server_error_response(e)
                $(this).removeClass('loading').removeClass('disabled')
                area_loading(false)
            },
            complete   : () => {
                scroll_top_animate()
            }
        })
    })
}

function on_cancel(table, body) {
    body.on('click', '.btn-shop-edit-cancel', function () {
        $('[data-tab="btn-profile"]').trigger('click')
        $('.btn-shop-edit-cancel').addClass('disabled').addClass('loading')
        area_loading(true)
        $(btn_save).addClass('disabled')
    })
}