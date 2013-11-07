var REGEXP_NUMERIC = /^\d+$/,
    REGEXP_CENA = /^[\d]+(.[\d]{1,2})?$/,
    URL = 'http://' + DOMAIN + '/index.php?' + VALUES,
    AJAX_MAIN = 'http://' + DOMAIN + '/ajax/main.php?' + VALUES,
    setCookie = function(name, value) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() + 1);
        document.cookie = name + '=' + value + '; path=/; expires=' + exdate.toGMTString();
    },
    getCookie = function(name) {
        var arr1 = document.cookie.split(name);
        if(arr1.length > 1) {
            var arr2 = arr1[1].split(/;/);
            var arr3 = arr2[0].split(/=/);
            return arr3[0] ? arr3[0] : arr3[1];
        } else
            return null;
    },
    delCookie = function(name) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate()-1);
        document.cookie = name + '=; path=/; expires=' + exdate.toGMTString();
    },
    _end = function(count, arr) {
        if(arr.length == 2)
            arr.push(arr[1]);
        var send = arr[2];
        if(Math.floor(count / 10 % 10) != 1)
            switch(count % 10) {
                case 1: send = arr[0]; break;
                case 2: send = arr[1]; break;
                case 3: send = arr[1]; break;
                case 4: send = arr[1]; break;
            }
        return send;
    },
    hashLoc,
    hashSet = function(hash) {
        if(!hash && !hash.p)
            return;
        hashLoc = hash.p;
        var s = true;
        switch(hash.p) {
            case 'client':
                if(hash.d == 'info')
                    hashLoc += '_' + hash.id;
                break;
            case 'zayav':
                if(hash.d == 'info')
                    hashLoc += '_' + hash.id;
                else if(hash.d == 'add')
                    hashLoc += '_add' + (REGEXP_NUMERIC.test(hash.id) ? '_' + hash.id : '');
                else if(!hash.d)
                    s = false;
                break;
            default:
                if(hash.d) {
                    hashLoc += '_' + hash.d;
                    if(hash.d1)
                        hashLoc += '_' + hash.d1;
                }
        }
        if(s)
            VK.callMethod('setLocation', hashLoc);
    },
    clientAdd = function(callback) {
        var html = '<table style="border-spacing:10px">' +
                '<tr><td class="label">Имя:<TD><input type="text" id="fio" style="width:220px;">' +
                '<tr><td class="label">Телефон:<TD><input type="text" id="telefon" style=width:220px;>' +
                '<tr><td class="label">Адрес:<TD><input type="text" id="adres" style=width:220px;>' +
                '</TABLE>',
            dialog = _dialog({
                width:340,
                head:'Добавление нoвого клиента',
                content:html,
                submit:submit
            });
        $('#fio').focus();
        $('#fio,#telefon,#adres').keyEnter(submit);
        function submit() {
            var send = {
                op:'client_add',
                fio:$('#fio').val(),
                telefon:$('#telefon').val(),
                adres:$('#adres').val()
            };
            if(!send.fio) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">Не указано имя клиента.</SPAN>',
                    top:-47,
                    left:81,
                    indent:40,
                    show:1,
                    remove:1,
                    correct:0
                });
                $('#fio').focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        _msg('Новый клиент внесён.');
                        if(typeof callback == 'function')
                            callback(res);
                        else
                            document.location.href = URL + '&p=client&d=info&id=' + res.uid;
                    } else dialog.abort();
                }, 'json');
            }
        }
    },
    clientFilter = function() {
        var v = {
            fast:cFind.inp(),
            dolg:$('#dolg').val(),
            active:$('#active').val()
        };
        $('.filter')[v.fast ? 'hide' : 'show']();
        return v;
    },
    clientSpisokLoad = function() {
        var send = clientFilter(),
            result = $('.result');
        send.op = 'client_spisok_load';
        if(result.hasClass('busy'))
            return;
        result.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            result.removeClass('busy');
            if(res.success) {
                result.html(res.all);
                $('.left').html(res.spisok);
            }
        }, 'json');
    };

$(document)
    .ajaxError(function(event, request, settings) {
        if(!request.responseText)
            return;
        alert('Ошибка:\n\n' + request.responseText);
    })
    .on('click', '#cache_clear', function() {
        $.post(AJAX_MAIN, {'op':'cache_clear'}, function(res) {
            if(res.success) {
                _msg('Кэш очищен.');
                document.location.reload();
            }
        }, 'json');
    })
    .on('click', '.debug_toggle', function() {
        var d = getCookie('debug');
        setCookie('debug', d == 0 ? 1 : 0);
        _msg('Debug включен.');
        document.location.reload();
    });

$(document).ready(function() {
    frameHidden.onresize = _fbhs;

    VK.callMethod('scrollWindow', 0);
    VK.callMethod('scrollSubscribe');
    VK.addCallback('onScroll', function(top) { VK_SCROLL = top; });

    _fbhs();

    if($('#client').length > 0) {
        window.cFind = $('#find')._search({
            width:602,
            focus:1,
            enter:1,
            txt:'Начните вводить данные клиента',
            func:clientSpisokLoad
        });
        $('#buttonCreate').vkHint({
            msg:'<B>Внесение нового клиента в базу.</B><br /><br />' +
                'После внесения Вы попадаете на страницу с информацией о клиенте для дальнейших действий.<br /><br />' +
                'Клиентов также можно добавлять при <A href="' + URL + '&p=zayav&d=add&back=client">создании новой заявки</A>.',
            ugol:'right',
            width:215,
            top:-38,
            left:-250,
            indent:40,
            delayShow:1000,
            correct:0
        }).click(clientAdd);
        $('#dolg_check').vkHint({
            msg:'<b>Список должников.</b><br /><br />' +
                'Выводятся клиенты, у которых баланс менее 0. Также в результате отображается общая сумма долга.',
            ugol:'right',
            width:150,
            top:-6,
            left:-185,
            indent:20,
            delayShow:1000,
            correct:0
        });
    }
});