FroalaEditor.RegisterCommand('convertLink', {
    title: 'Converter em Link',
    icon: 'linkStyle',
    undo: true,
    focus: true,
    showOnMobile: true,
    refreshAfterCallback: true,
    callback: function () {
        let text = this.selection.text();
        if (text.trim().length > 0) {

            let html = (text.startsWith(' ') ? ' ' : '') +
                '<a href="' + text.trim() + '">' + text.trim() + '</a>' +
                (text.endsWith(' ') ? ' ' : '');

            this.html.insert(html);

        } else {

            //

        }
    },
})