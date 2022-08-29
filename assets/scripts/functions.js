function loader(status, $text) {
    let loader   = $('.preloader'),
        submit   = $('.send-file-btn'),
        disabled = 'disabled',
        text     = loader.find('.text-status')

    if (status) {
        if ($text) {
            text.text($text)
        }

        loader.fadeIn(50)
        submit.addClass(disabled)
    } else {
        loader.fadeOut(50)
        submit.removeClass(disabled)
        setTimeout(() => {
            text.text('Загрузка')
        }, 50)
    }
}

function area_loading(status) {
    let area = $('.area-loading')

    status ? area.addClass('loading') : area.removeClass('loading')
}

function ntfStatus(status, text) {
    let ntfContainer = $('.notification')

    if (status === 'ok') {
        ntfContainer.html(`<div class="alert alert-success">${text}</div>`)
    }

    if (status === 'fail') {
        ntfContainer.html(`<div class="alert alert-danger">${text}</div>`)
    }

    if (status === 'warn') {
        ntfContainer.html(`<div class="alert alert-warning">${text}</div>`)
    }

    if (status === 'info') {
        ntfContainer.html(`<div class="alert alert-info">${text}</div>`)
    }

    if (status === 'clear') {
        if (ntfContainer.html() !== '') {
            ntfContainer.html('')
        }
    }


}

function scroll_top_animate() {
    let body                  = $('body, html'),
        status_bar_offset_top = $('.status-bar').offset().top - ($(window).height() / 5)

    body.stop().animate({ scrollTop: status_bar_offset_top }, 500, 'swing');
}

function server_error_response(e) {
    ntfStatus('fail', `<b>${e.status}</b>\n${e.statusText}`)
}

function view_photo_after_upload(input, img_container) {
    input.on("change", function () {
        if (this.files[0]) {
            let fr = new FileReader();

            fr.addEventListener("load", function () {
                img_container.find('img').attr('src', fr.result)
            }, false);

            fr.readAsDataURL(this.files[0]);
        }
    });
}