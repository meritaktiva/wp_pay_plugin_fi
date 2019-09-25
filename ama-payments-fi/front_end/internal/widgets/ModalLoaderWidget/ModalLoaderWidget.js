function ModalLoaderWidget(options) {

    if (options == undefined) {
        options = {}
    }

    var _is_loading = false
    var _message_div

    function _get_theme_class() {

        var available_theme_array = [
            'greenery-3'
        ]
        var theme_class = 'modal-loader-widget-'

        if (available_theme_array.indexOf(window.theme) == -1) {
            theme_class += available_theme_array[0]
        } else {
            theme_class += window.theme
        }

        return theme_class

    }

    var _theme_class = _get_theme_class()

    function _show(loading_msg) {

        if (_is_loading == false) {

            var dark_layer_div = $j('<div id="modalloaderscreen">')
            dark_layer_div.addClass('modal-loader-widget-scope')
            dark_layer_div.addClass('dark-layer-div')
            dark_layer_div.height(document.body.scrollHeight)

            var loader_div = $j('<div>')
            loader_div.addClass('loader-div')

            _message_div = $j('<div>')
            _message_div.addClass('msg-div')

            var spinner_div = $j('<div>')
            spinner_div.addClass('spinner-div')

            dark_layer_div.append(
                loader_div.append(
                    _message_div,
                    spinner_div
                )
            )

            if (loading_msg) {
                _setmessage(loading_msg)
            }

            $j('body').append(dark_layer_div)

            _is_loading = true
        }

    }

    function _hide() {

        _is_loading = false
        $j('#modalloaderscreen').remove()

    }

    function _setmessage(loading_msg) {
        _message_div.html(loading_msg)
        //_message_div.text(loading_msg)
    }


    return {
        'show': _show,
        'hide': _hide,
        'setmessage': _setmessage
    }

} // eof ModalLoaderWidget
