$(function () {
    let formModeration    = '.moderation-form',
        responseContainer = '.response',
        btn_export        = '.btn-export'

    $(`${formModeration} .btn`).one('click', function (e) {
        let string = $(this).parents('form').serialize();

        e.preventDefault()

        $.ajax({
            url       : `/${shop_root_dir}/ajax/moderation.php`,
            type      : 'POST',
            data      : string,
            cache     : false,
            beforeSend: () => {
                ntfStatus('clear')
                area_loading(true)
                loader(true, $(this).hasClass('btn-moder-send') ? 'Отправка' : $(this).hasClass('btn-moder-cancel') ? 'Загрузка' : false)
                $(this).addClass('loading')
            },
            success   : (data) => {
                if (data.STATUS === 'ok') {
                    ntfStatus('ok', data.STATUS_TEXT)
                }
            },
            error     : (e) => {
                server_error_response(e)
            },
            complete  : () => {
                $(responseContainer).html('')
                $(btn_export).removeClass('disabled')
                loader(false)
                area_loading(false)
            }
        })

    })
})