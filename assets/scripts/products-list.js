$(function () {
    window.products_edit_list = new Vue({
        el     : '#form-products-edit-app',
        data   : {
            active_all         : false,
            edit               : false,
            products_cnt       : 0,
            products_active_ids: [],
            products           : []
        },
        methods: {
            getProductsCnt() {
                let products_list = document.querySelectorAll('[data-prod-id]')

                return this.products_cnt = products_list.length
            },
            check_inputs(id) {
                if (id) {
                    let elem = document.querySelector(`input[data-prod-id="${id}"]`)

                    elem.checked
                        ? this.products_active_ids.push(id)
                        : this.products_active_ids.splice(this.products_active_ids.indexOf(id), 1)

                    this.active_all = this.products_active_ids.length === this.products_cnt

                } else {
                    let elems = document.querySelectorAll('[data-prod-id]')

                    this.products_active_ids = []
                    this.active_all          = !this.active_all
                    elems.forEach(input => {
                        input.checked = this.active_all
                        if (this.active_all) this.products_active_ids.push(parseInt(input.value))
                    })

                }
            },
            on_edit(e) {
                e.preventDefault()
                this.edit = true
            },
            on_update(e) {
                let elems = document.querySelectorAll('[data-product]')

                e.preventDefault()

                this.edit = true
                this.products = []

                elems.forEach((product) => {
                    if (product.hasChildNodes()) {
                        let is_checked = product.querySelector('[data-prod-id]').checked

                        if (is_checked) {
                            let id       = 'data-prod-id',
                                name     = 'data-name',
                                activity = 'data-active',
                                fields   = {
                                    cnt  : 'data-cnt',
                                    price: 'data-price'
                                },
                                res_obj  = {}

                            res_obj['id']     = product.querySelector(`[${id}]`).value
                            res_obj['name']     = product.querySelector(`[${name}]`).textContent
                            res_obj['active'] = product.querySelector(`[${activity}]`).checked

                            for (let key in fields) {
                                res_obj[key] = product.querySelector(`[${fields[key]}]`).querySelector('input[type="text"]').value
                            }

                            this.products.push(res_obj)

                        }

                    }
                })

                $.ajax({
                    url       : `/${shop_root_dir}/ajax/products/update.php`,
                    type      : 'POST',
                    data      : { update_data: this.products },
                    beforeSend: () => {
                        ntfStatus('clear')
                        area_loading(true)
                    },
                    success   : (res) => {
                        let interval = null

                        if (res.STATUS === 'ok') {
                            $('[data-tab="btn-catalog"]').trigger('click')

                            interval = setInterval(() => {
                                if (!$('.preloader').is(':visible')) {
                                    area_loading(false)
                                    ntfStatus(res.STATUS, res.STATUS_TEXT)
                                    clearInterval(interval)
                                }
                            }, 100)
                        }

                        if (res.STATUS === 'fail') {
                            ntfStatus(res.STATUS, res.STATUS_TEXT)
                            area_loading(false)
                        }

                    },
                    error     : (e) => {
                        area_loading(false)
                        server_error_response(e)
                    }
                })
            },
            on_cancel(e) {
                e.preventDefault()
                $('[data-tab="btn-catalog"]').trigger('click')
            },
            on_delete(e) {
                e.preventDefault()
                if (confirm('Подтверждаете удаление?')) {
                    $.ajax({
                        url       : `/${shop_root_dir}/ajax/products/delete.php`,
                        beforeSend: () => {
                            area_loading(true)
                        },
                        success   : (res) => {
                            let interval = null

                            if (res.STATUS === 'ok') {
                                $('[data-tab="btn-catalog"]').trigger('click')

                                interval = setInterval(() => {
                                    if (!$('.preloader').is(':visible')) {
                                        area_loading(false)
                                        ntfStatus(res.STATUS, res.STATUS_TEXT)
                                        clearInterval(interval)
                                    }
                                }, 100)
                            }
                        },
                        error     : (e) => {
                            area_loading(false)
                            server_error_response(e)
                        },
                        complete  : () => {
                        }
                    })
                }
            },
            get_editable_products(id) {
                return this.products_active_ids.includes(+id)
            }
        },
        mounted() {
            this.getProductsCnt()
        }
    })
})