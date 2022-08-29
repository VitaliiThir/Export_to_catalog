$(function () {
    let submit_export     = '.btn-submit-export',
        responseContainer = $('.response')

    $('body').one('click', submit_export, function (e) {
        let formID = $('#user-export')

        e.preventDefault()

        loader(true, 'Экспорт')
        $(this).addClass('loading')

        if ($(this).attr('data-status') === 'send') {
            $(this).parents(formID).find('input[name="export-status"]').attr('value', 'send')
        } else if ($(this).attr('data-status') === 'cancel') {
            $(this).parents(formID).find('input[name="export-status"]').attr('value', 'cancel')
        } else {
            return false
        }

        setTimeout(() => {
            $.ajax({
                url       : `/${shop_root_dir}/ajax/export.php`,
                type      : 'POST',
                cache     : false,
                data      : formID.serialize(),
                beforeSend: () => {
                    area_loading(true)
                },
                success   : (data) => {
                    if (data.STATUS === 'ok') {
                        $(submit_export).addClass('disabled')
                        ntfStatus('ok', data.STATUS_TEXT)
                        responseContainer.html(data.TABLE)
                    } else {
                        ntfStatus('fail', 'Ошибка экспорта!')
                    }
                },
                error     : (e) => {
                    server_error_response(e)
                },
                complete  : () => {
                    $(this).removeClass('loading')
                    loader(false)
                    area_loading(false)
                }
            })
        }, 100)
    })
})