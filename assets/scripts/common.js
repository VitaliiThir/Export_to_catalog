$(function () {
    let responseContainer = $('.response'),
        tabs              = $('.tabs .btn'),
        urls              = {
            'btn-profile'   : `/${shop_root_dir}/ajax/tabs/profile.php`,
            'btn-save'      : `/${shop_root_dir}/ajax/tabs/save.php`,
            'btn-export'    : `/${shop_root_dir}/ajax/tabs/export.php`,
            'btn-moderation': `/${shop_root_dir}/ajax/tabs/moderate_products.php`,
            'btn-catalog'   : `/${shop_root_dir}/ajax/tabs/products.php`
        },
        tabsAjaxHandler   = function (url, tabs, cnx, responseContainer) {
            return $.ajax({
                url       : url,
                cache     : false,
                beforeSend: () => {
                    tabs.removeClass('disabled').addClass('p-events-none')
                    cnx.addClass('loading')
                    area_loading(true)
                    ntfStatus('clear')
                    loader(true)
                    $('.preloader .text-status').text('Загрузка')
                    history.pushState(null, null, cnx.attr('href'));
                },
                success   : (data) => {

                    if (data.STATUS) {
                        responseContainer.html('')
                        ntfStatus(data.STATUS, data.STATUS_TEXT)
                    } else {
                        responseContainer.html(data)

                        if (responseContainer.children('#form-products-edit-app').length >= 1) {
                            responseContainer.children('#form-products-edit-app')
                                .fadeOut(0)
                                .ready(() => responseContainer.children('#form-products-edit-app').fadeIn(200))
                        }
                    }

                },
                error     : (e) => {
                    server_error_response(e)
                },
                complete  : () => {
                    cnx.removeClass('loading').addClass('disabled')
                    loader(false)
                    area_loading(false)
                    tabs.removeClass('p-events-none')
                }
            })
        }

    tabs.on('click', function () {
        let current_query = $(this).attr('data-tab'),
            url           = urls[current_query];

        tabsAjaxHandler(url, tabs, $(this), responseContainer)

        return false;

    })

})