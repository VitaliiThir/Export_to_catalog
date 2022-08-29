$(function () {
    let formID            = '.load-file',
        submit            = $('.send-file-btn'),
        loader_status     = $('.preloader .text-status'),
        input_file        = $('#user_file'),
        responseContainer = $('.response')

    input_file.on('change', function () {
        if (input_file.val() !== '') {
            ntfStatus('clear')
        }
    })

    submit.on('click', function (e) {
        let fd = new FormData($(formID)[0])

        e.preventDefault();

        $.ajax({
            url        : `/${shop_root_dir}/ajax/save.php`,
            type       : "POST",
            data       : fd,
            cache      : false,
            processData: false,
            contentType: false,
            beforeSend : () => {
                ntfStatus('clear')
                loader(true)
            },
            success    : (data) => {

                if (data.STATUS !== 'ok') {
                    loader(false)
                }

                // Если уже есть файл на модерации или Некорректное расширение файла
                if (data.STATUS === 'fail') {
                    input_file.val('')
                    ntfStatus('fail', data.STATUS_TEXT)
                }

                // Удачный ответ сервера
                if (data.STATUS === 'ok') {
                    let file = {
                        name: data.FILE.NAME,
                        date: data.FILE.DATE,
                        path: data.FILE.PATH
                    }

                    loader_status.text(data.STATUS_TEXT)
                    input_file.val('')


                    setTimeout(() => {

                        $.ajax({
                            url       : `/${shop_root_dir}/ajax/checkout.php`,
                            type      : "POST",
                            data      : $.param({ file_name: file.name, file_path: file.path, file_date: file.date }),
                            beforeSend: () => {
                                loader_status.text('Проверка файла на корректность. Это может занять несколько минут')
                            },
                            success   : (res) => {

                                if (res.STATUS === 'ok') {
                                    responseContainer.html('')
                                    ntfStatus('ok', res.STATUS_TEXT)
                                } else {
                                    ntfStatus('fail', res.STATUS_TEXT)
                                }

                                loader(false)

                            },
                            error      : (e) => {
                                server_error_response(e)
                                loader(false)
                            }
                        })

                    }, 1000)
                }
            },
            error      : (e) => {
                server_error_response(e)
                loader(false)
            }
        })

    })
})