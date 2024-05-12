FroalaEditor.DefineIcon('formatOLStartPlus', {
    NAME: 'plus',
    SVG_KEY: 'add'
});
FroalaEditor.DefineIcon('formatOLStartMinus', {
    NAME: 'minus',
    SVG_KEY: 'minimize'
});
FroalaEditor.RegisterCommand('formatOLStartPlus', {
    title: 'Aumentar',
    focus: true,
    undo: true,
    refreshAfterCallback: true,
    refresh: function (e) {
        let el = this.selection.element().parentNode;
        if (el.tagName === "LI") {
            el = this.selection.element().parentNode.parentNode;
        }
        if (el.tagName === "OL") {
            let num = el.getAttribute('start') ?? 1;
            document.getElementById("formatOLNumber").innerHTML = num;
            document.getElementById('formatOLStartMinus-1').classList.remove('fr-disabled');
            document.getElementById('formatOLStartPlus-1').classList.remove('fr-disabled');
        } else {
            document.getElementById("formatOLNumber").innerHTML = 1;
            document.getElementById('formatOLStartMinus-1').classList.add('fr-disabled');
            document.getElementById('formatOLStartPlus-1').classList.add('fr-disabled');
        }
    },
    callback: function () {
        let el = this.selection.element().parentNode;
        if (el.tagName === "LI") {
            el = this.selection.element().parentNode.parentNode;
        }
        if (el.tagName === "OL") {
            let num = +el.getAttribute('start') ?? 1;
            if (num < 1) {
                num = 1;
            }
            num++
            el.setAttribute('start', num)
        }
    }
});
FroalaEditor.RegisterCommand('formatOLStartMinus', {
    title: 'Aumentar',
    focus: true,
    undo: true,
    refreshAfterCallback: true,
    refresh: function (e) {
        let el = this.selection.element().parentNode;
        if (el.tagName === "LI") {
            el = this.selection.element().parentNode.parentNode;
        }
        if (el.tagName === "OL") {
            let num = el.getAttribute('start') ?? 1;
            document.getElementById("formatOLNumber").innerHTML = num;
            document.getElementById('formatOLStartMinus-1').classList.remove('fr-disabled');
            document.getElementById('formatOLStartPlus-1').classList.remove('fr-disabled');
        } else {
            document.getElementById("formatOLNumber").innerHTML = 1;
            document.getElementById('formatOLStartMinus-1').classList.add('fr-disabled');
            document.getElementById('formatOLStartPlus-1').classList.add('fr-disabled');
        }
    },
    callback: function () {
        let el = this.selection.element().parentNode;
        if (el.tagName === "LI") {
            el = el.parentNode;
        }
        if (el.tagName === "OL") {
            let num = +el.getAttribute('start') ?? 1;
            if (num > 1) {
                num--;
                el.setAttribute('start', num)
            }
        }
    }
});

FroalaEditor.POPUP_TEMPLATES["customFormatOLStartIn.popup"] = '[_CUSTOM_LAYER_]';
FroalaEditor.PLUGINS.customFormatOLStartIn = function (editor) {
    function initPopup() {
        var layer = '';

        layer += '<div style="padding: 5px 10px;">';
        layer += 'Iniciar lista em...';
        layer += '</div>';
        layer += '<div style="display: flex;align-items: center;justify-content: space-between;">';
        layer += editor.button.buildList(['formatOLStartMinus', '|']);
        layer += '<div id="formatOLNumber" style="flex: 1;min-width: 50px;text-align: center;">1</div>';
        layer += editor.button.buildList(['|', 'formatOLStartPlus']);
        layer += '</div>';

        return editor.popups.create('customFormatOLStartIn.popup', {
            custom_layer: layer
        });
    }

    function showPopup() {
        var $popup = editor.popups.get('customFormatOLStartIn.popup');

        if (!$popup) $popup = initPopup();

        editor.popups.setContainer('customFormatOLStartIn.popup', editor.$tb);

        var $btn = editor.$tb.find('.fr-command[data-cmd="formatOL"]');
        var left = $btn.offset().left + $btn.outerWidth() / 2;
        var top = $btn.offset().top + (editor.opts.toolbarBottom ? 10 : $btn.outerHeight() - 10);
        editor.popups.show('customFormatOLStartIn.popup', left, top, $btn.outerHeight());
    }

    function hidePopup() {
        editor.popups.hide('customFormatOLStartIn.popup');
    }

    return {
        showPopup: showPopup,
        hidePopup: hidePopup
    }
}


FroalaEditor.RegisterCommand('formatOL', {
    title: "Lista ordenada",
    hasOptions: function () {
        return this.opts.listAdvancedTypes;
    },
    options: {
        default: "Numérico",
        "lower-alpha": "Alfabético",
        "start": "Iniciar em..."
    },
    refresh: function ($btn) {
        this.lists.refresh($btn, "OL");
    },
    callback: function (cmd, val) {
        if (val == "start") {
            let el = this.selection.element().parentNode;
            if (el.tagName === "LI") {
                el = el.parentNode;
            }
            if (el.tagName !== "OL") {
                this.lists.format("OL", val);
            }
            this.customFormatOLStartIn.showPopup();
        } else {
            this.lists.format("OL", val);
        }
    },
    plugin: "lists",
});

